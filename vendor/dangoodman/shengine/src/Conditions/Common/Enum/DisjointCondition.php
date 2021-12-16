<?php
namespace TrsVendors\Dgm\Shengine\Conditions\Common\Enum;


class DisjointCondition extends \TrsVendors\Dgm\Shengine\Conditions\Common\Enum\AbstractEnumCondition
{
    public function isSatisfiedBy($value)
    {
        return $this->intersect($value, $this->other) == 0;
    }
}