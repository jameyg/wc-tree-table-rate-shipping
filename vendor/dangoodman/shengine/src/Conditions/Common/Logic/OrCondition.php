<?php
namespace TrsVendors\Dgm\Shengine\Conditions\Common\Logic;

use Dgm\Shengine\Conditions\Common\GroupCondition;


class OrCondition extends \TrsVendors\Dgm\Shengine\Conditions\Common\GroupCondition
{
    public function isSatisfiedBy($value)
    {
        foreach ($this->conditions as $condition) {
            if ($condition->isSatisfiedBy($value)) {
                return true;
            }
        }

        return false;
    }
}