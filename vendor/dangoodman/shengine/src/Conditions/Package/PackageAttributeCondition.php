<?php
namespace TrsVendors\Dgm\Shengine\Conditions\Package;

use Dgm\Shengine\Interfaces\IAttribute;
use Dgm\Shengine\Interfaces\ICondition;
use Dgm\Shengine\Interfaces\IPackage;


class PackageAttributeCondition extends \TrsVendors\Dgm\Shengine\Conditions\Package\AbstractPackageCondition
{
    public function __construct(\TrsVendors\Dgm\Shengine\Interfaces\ICondition $condition, \TrsVendors\Dgm\Shengine\Interfaces\IAttribute $attribute)
    {
        $this->condition = $condition;
        $this->attribute = $attribute;
    }

    public function isSatisfiedByPackage(\TrsVendors\Dgm\Shengine\Interfaces\IPackage $package)
    {
        return $this->condition->isSatisfiedBy($this->attribute->getValue($package));
    }

    private $condition;
    private $attribute;
}