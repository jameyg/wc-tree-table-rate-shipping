<?php
namespace TrsVendors\Dgm\Shengine\Conditions\Common\Enum;


class SupersetCondition extends \TrsVendors\Dgm\Shengine\Conditions\Common\Enum\SubsetCondition
{
    protected function isSubset($superset, $subset)
    {
        return parent::isSubset($subset, $superset);
    }
}