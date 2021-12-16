<?php
namespace TrsVendors\Dgm\Shengine\Conditions\Common\Logic;

use Dgm\ClassNameAware\ClassNameAware;
use Dgm\Shengine\Interfaces\ICondition;


class NotCondition extends \TrsVendors\Dgm\ClassNameAware\ClassNameAware implements \TrsVendors\Dgm\Shengine\Interfaces\ICondition
{
    public function __construct(\TrsVendors\Dgm\Shengine\Interfaces\ICondition $condition)
    {
        $this->condition = $condition;
    }

    public function isSatisfiedBy($value)
    {
        return !$this->condition->isSatisfiedBy($value);
    }

    private $condition;
}