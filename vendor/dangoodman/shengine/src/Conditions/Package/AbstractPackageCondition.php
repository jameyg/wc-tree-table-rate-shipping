<?php
namespace TrsVendors\Dgm\Shengine\Conditions\Package;

use Dgm\Shengine\Conditions\Common\AbstractCondition;
use Dgm\Shengine\Interfaces\IPackage;


abstract class AbstractPackageCondition extends \TrsVendors\Dgm\Shengine\Conditions\Common\AbstractCondition
{
    public function isSatisfiedBy($package)
    {
        return $this->isSatisfiedByPackage($package);
    }

    abstract protected function isSatisfiedByPackage(\TrsVendors\Dgm\Shengine\Interfaces\IPackage $package);
}