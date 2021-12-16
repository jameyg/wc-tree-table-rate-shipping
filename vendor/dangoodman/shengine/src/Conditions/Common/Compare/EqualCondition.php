<?php
namespace TrsVendors\Dgm\Shengine\Conditions\Common\Compare;


class EqualCondition extends \TrsVendors\Dgm\Shengine\Conditions\Common\Compare\CompareCondition
{
    public function isSatisfiedBy($value)
    {
        return $this->comparator->equal($value, $this->compareWith);
    }
}