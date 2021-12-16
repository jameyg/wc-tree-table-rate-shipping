<?php
namespace TrsVendors\Dgm\Shengine\Conditions\Common\Compare;


class LessOrEqualCondition extends \TrsVendors\Dgm\Shengine\Conditions\Common\Compare\CompareCondition
{
    public function isSatisfiedBy($value)
    {
        return $this->comparator->less($value, $this->compareWith, true);
    }
}