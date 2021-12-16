<?php
namespace TrsVendors\Dgm\Shengine\Conditions\Common\Enum;

use Dgm\ClassNameAware\ClassNameAware;
use Dgm\Shengine\Interfaces\ICondition;


class EmptyEnumCondition extends \TrsVendors\Dgm\ClassNameAware\ClassNameAware implements \TrsVendors\Dgm\Shengine\Interfaces\ICondition
{
    public function isSatisfiedBy($value)
    {
        return empty($value);
    }
}