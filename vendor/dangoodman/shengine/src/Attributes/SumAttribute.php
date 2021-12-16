<?php
namespace TrsVendors\Dgm\Shengine\Attributes;

use Dgm\Shengine\Interfaces\IPackage;


abstract class SumAttribute extends \TrsVendors\Dgm\Shengine\Attributes\MapAttribute
{
    public function getValue(\TrsVendors\Dgm\Shengine\Interfaces\IPackage $package)
    {
        return array_sum(parent::getValue($package));
    }
}