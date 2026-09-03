<?php

namespace tests\oihana\models\mocks;

/**
 * A document whose properties are **typed**, to tell apart three states a plain
 * `stdClass` cannot express.
 *
 * - {@see self::$tags} is declared with no default : it stays *uninitialized*
 *   until something fills it. A source that never supplied it leaves it there,
 *   and the document carries no value for it.
 * - {@see self::$labels} is declared `= null` : it **is** initialized, it merely
 *   holds nothing. Alterations are routinely declared on such properties.
 * - {@see self::$name} is an ordinary valued property.
 *
 * The first two look alike to `property_exists()`, and they are not the same
 * thing — which is what {@see \oihana\models\traits\AlterDocumentTrait::alter()}
 * has to tell apart before it alters anything.
 */
class MockTypedDocument
{
    /**
     * Declared, never initialized — no default value.
     * @var array<array-key,mixed>
     */
    public array $tags ;

    /**
     * Declared AND initialized, holding null.
     * @var array<array-key,mixed>|null
     */
    public ?array $labels = null ;

    /**
     * An ordinary valued property.
     */
    public ?string $name = null ;

    public function __construct( ?string $name = null )
    {
        $this->name = $name ;
    }
}
