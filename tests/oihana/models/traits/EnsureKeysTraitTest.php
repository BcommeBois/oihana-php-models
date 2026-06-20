<?php

namespace tests\oihana\models\traits ;

use oihana\models\enums\ModelParam;
use oihana\models\traits\EnsureKeysTrait;
use PHPUnit\Framework\TestCase;
use stdClass;

class EnsureKeysTraitTest extends TestCase
{
    private object $traitConsumer;

    protected function setUp(): void
    {
        // Création d'une classe anonyme pour consommer le trait
        // et exposer la méthode protégée en public pour le test.
        $this->traitConsumer = new class
        {
            use EnsureKeysTrait;

            public function callEnsureDocumentKeys(mixed &$data, array $init): void
            {
                $this->ensureDocumentKeys($data, $init);
            }

            public function callInitializeEnsure(array $init = []): static
            {
                return $this->initializeEnsure($init);
            }
        };
    }

    public function testInitializeEnsureSetsAndDefaultsTheProperty(): void
    {
        $definition = [ 'status' => 'active' ] ;
        $result = $this->traitConsumer->callInitializeEnsure([ ModelParam::ENSURE => $definition ]) ;

        $this->assertSame( $this->traitConsumer , $result ) ;
        $this->assertSame( $definition , $this->traitConsumer->ensure ) ;

        $this->traitConsumer->callInitializeEnsure() ;
        $this->assertNull( $this->traitConsumer->ensure ) ;
    }

    // --- 1. Tests des Guard Clauses (Sorties anticipées) ---

    public function testReturnsEarlyIfDataIsEmpty(): void
    {
        $data = [];
        $init = [ModelParam::ENSURE => ['status']];

        $this->traitConsumer->callEnsureDocumentKeys($data, $init);

        $this->assertEmpty($data, 'Data should remain empty.');
    }

    public function testReturnsEarlyIfInitMissingEnsureKey(): void
    {
        $data = ['id' => 1];
        $init = []; // Pas de clé ENSURE

        $this->traitConsumer->callEnsureDocumentKeys($data, $init);

        $this->assertEquals(['id' => 1], $data, 'Data should not be modified if ENSURE config is missing.');
    }

    // --- 2. Tests Document Unique (Format Simple) ---

    public function testEnsureSingleArrayDocumentWithSimpleConfig(): void
    {
        $data = ['id' => 1];
        $init = [
            ModelParam::ENSURE => ['status', 'role'] // Format simple (array de clés)
        ];

        $this->traitConsumer->callEnsureDocumentKeys($data, $init);

        $this->assertArrayHasKey('status', $data);
        $this->assertNull($data['status'], 'Default should be null via simple config');
        $this->assertArrayHasKey('role', $data);
        $this->assertSame(1, $data['id'], 'Existing data should be preserved');
    }

    public function testEnsureSingleObjectDocumentWithSimpleConfig(): void
    {
        $data = (object) ['id' => 1];
        $init = [
            ModelParam::ENSURE => 'active' // Clé unique string
        ];

        $this->traitConsumer->callEnsureDocumentKeys($data, $init);

        $this->assertObjectHasProperty('active', $data);
        $this->assertNull($data->active);
    }

    // --- 3. Tests Document Unique (Format Complet) ---

    public function testEnsureSingleDocumentWithFullConfigAndDefault(): void
    {
        $data = ['name' => 'Alice'];
        $init = [
            ModelParam::ENSURE => [
                ModelParam::KEYS    => ['age'],
                ModelParam::DEFAULT => 18,
                ModelParam::ENFORCE => false
            ]
        ];

        $this->traitConsumer->callEnsureDocumentKeys($data, $init);

        $this->assertSame(18, $data['age']);
    }

    public function testEnsureAssociativeDefaultsSpecificKeys(): void
    {
        // Test de la feature "Specific Defaults" via ensureKeyValue
        $data = ['name' => 'Bob'];
        $init = [
            ModelParam::ENSURE => [
                'role' => 'guest', // Specifique
                'active'           // Global default (null)
            ]
        ];

        $this->traitConsumer->callEnsureDocumentKeys($data, $init);

        $this->assertSame('guest', $data['role']);
        $this->assertNull($data['active']);
    }

    // --- 4. Tests Collection (Liste Indexée) ---

    public function testEnsureCollectionOfArrays(): void
    {
        // Liste indexée (0, 1)
        $data = [
            ['id' => 1],
            ['id' => 2, 'status' => 'banned'] // Celui-ci a déjà la clé
        ];

        $init = [
            ModelParam::ENSURE => [
                ModelParam::KEYS    => ['status'],
                ModelParam::DEFAULT => 'active'
            ]
        ];

        $this->traitConsumer->callEnsureDocumentKeys($data, $init);

        // Item 1 : Doit avoir la valeur par défaut
        $this->assertSame('active', $data[0]['status']);
        // Item 2 : Ne doit pas être écrasé
        $this->assertSame('banned', $data[1]['status']);
    }

    public function testEnsureCollectionOfObjects(): void
    {
        $obj1 = new stdClass(); $obj1->id = 1;
        $obj2 = new stdClass(); $obj2->id = 2;

        $data = [$obj1, $obj2];

        $init = [
            ModelParam::ENSURE => 'meta.verified' // Nested key
        ];

        $this->traitConsumer->callEnsureDocumentKeys($data, $init);

        $this->assertObjectHasProperty('meta', $data[0]);
        $this->assertObjectHasProperty('verified', $data[0]->meta);
        $this->assertNull($data[0]->meta->verified);

        $this->assertObjectHasProperty('meta', $data[1]);
    }

    // --- 5. Tests Cas Limites & Distinction Indexé vs Associatif ---

    public function testDistinguishesAssociativeArrayFromCollection(): void
    {
        // C'est un document unique (associatif), pas une collection
        // Si la logique isIndexed échoue, il pourrait essayer d'itérer sur 'id', 'name'...
        $data = [
            'id' => 100,
            'name' => 'Test'
        ];

        $init = [
            ModelParam::ENSURE => ['status']
        ];

        $this->traitConsumer->callEnsureDocumentKeys($data, $init);

        // Vérifie qu'on a bien traité $data comme un document unique
        $this->assertArrayHasKey('status', $data);
        // Vérifie qu'on n'a pas traité les valeurs scalaires comme des documents
        $this->assertSame(100, $data['id']);
    }

    public function testEnforceModePassedToHelper(): void
    {
        // Teste que le flag 'enforce' est bien passé (nécessite un objet avec propriété typée)
        if (version_compare(PHP_VERSION, '7.4.0', '<')) {
            $this->markTestSkipped('Typed properties require PHP 7.4+');
        }

        // Classe anonyme avec propriété typée non initialisée
        $doc = new class {
            public string $status;
        };

        $data = $doc;

        $init =
        [
            ModelParam::ENSURE =>
            [
                ModelParam::KEYS    => ['status'],
                ModelParam::DEFAULT => 'initialized',
                ModelParam::ENFORCE => true // C'est ça qu'on teste
            ]
        ];

        $this->traitConsumer->callEnsureDocumentKeys($data, $init);

        // Si enforce n'était pas passé à ensureKeyValue, l'accès suivant ferait planter
        // ou la valeur resterait non initialisée selon la version de PHP.
        // Ici on s'attend à ce que la valeur par défaut soit appliquée.
        $this->assertSame('initialized', $data->status);
    }
}