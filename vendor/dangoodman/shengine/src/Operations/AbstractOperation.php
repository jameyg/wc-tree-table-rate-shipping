<?php
namespace TrsVendors\Dgm\Shengine\Operations;

use Dgm\ClassNameAware\ClassNameAware;
use Dgm\Shengine\Interfaces\IOperation;


abstract class AbstractOperation extends \TrsVendors\Dgm\ClassNameAware\ClassNameAware implements \TrsVendors\Dgm\Shengine\Interfaces\IOperation
{
    public function getType()
    {
        return self::OTHER;
    }

    public function canOperateOnMultipleRates()
    {
        return true;
    }
}