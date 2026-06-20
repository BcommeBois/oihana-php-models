<?php

namespace tests\oihana\traits\mocks;

use oihana\models\pdo\PDOTrait;

class MockPDOClass
{
    use PDOTrait;

    /**
     * To allow altering results (no operation here).
     */
    public function alter( mixed $document ) :mixed
    {
        return $document;
    }
}