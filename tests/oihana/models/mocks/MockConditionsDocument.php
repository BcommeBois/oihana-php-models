<?php

namespace tests\oihana\models\mocks;

use oihana\models\traits\ConditionsTrait;

class MockConditionsDocument
{
    public function __construct(  array $init = [] )
    {
        $this->initializeConditions( $init );
    }

    use ConditionsTrait;
}