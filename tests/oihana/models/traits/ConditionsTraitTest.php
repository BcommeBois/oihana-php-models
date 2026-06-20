<?php

namespace tests\oihana\models\traits ;

use oihana\models\enums\ModelParam;
use tests\oihana\models\mocks\MockConditionsDocument;
use PHPUnit\Framework\TestCase;

class ConditionsTraitTest extends TestCase
{
    public function testInitializeConditionsWithEmptyArray(): void
    {
        $model = new MockConditionsDocument();

        $result = $model->initializeConditions();

        $this->assertSame($model, $result);
        $this->assertSame([], $model->conditions);
    }

    public function testInitializeConditionsWithConditionsParam(): void
    {
        $conditions = [
            'status' => 'published',
            'age >'  => 18,
        ];

        $model = new MockConditionsDocument();

        $model->initializeConditions
        ([
            ModelParam::CONDITIONS => $conditions,
        ]);

        $this->assertSame($conditions, $model->conditions);
    }

    public function testInitializeConditionsOverridesExistingConditions(): void
    {
        $model = new MockConditionsDocument();

        $model->conditions = [
            'status' => 'draft',
        ];

        $model->initializeConditions
        ([
            ModelParam::CONDITIONS =>
            [
                'status' => 'published',
            ],
        ]);

        $this->assertSame(
            ['status' => 'published'],
            $model->conditions
        );
    }

    public function testInitializeConditionsResetsConditionsIfKeyIsMissing(): void
    {
        $model = new MockConditionsDocument();

        $model->conditions = [
            'foo' => 'bar',
        ];

        $model->initializeConditions([]);

        $this->assertSame([], $model->conditions);
    }
}