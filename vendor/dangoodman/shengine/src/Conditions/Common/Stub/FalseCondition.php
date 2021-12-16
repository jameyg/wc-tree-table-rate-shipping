<?php
namespace TrsVendors\Dgm\Shengine\Conditions\Common\Stub;

use Dgm\ClassNameAware\ClassNameAware;
use Dgm\Shengine\Interfaces\ICondition;


class FalseCondition extends \TrsVendors\Dgm\ClassNameAware\ClassNameAware implements \TrsVendors\Dgm\Shengine\Interfaces\ICondition
{
    public function isSatisfiedBy($value)
    {
        return false;
    }
}