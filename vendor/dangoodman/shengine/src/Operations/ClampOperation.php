<?php
namespace TrsVendors\Dgm\Shengine\Operations;

use Dgm\Range\Range;
use Dgm\Shengine\Interfaces\IPackage;
use Dgm\Shengine\Processing\Registers;


class ClampOperation extends \TrsVendors\Dgm\Shengine\Operations\AbstractOperation
{
    public function __construct(\TrsVendors\Dgm\Range\Range $range)
    {
        $this->range = $range;
    }

    public function process(\TrsVendors\Dgm\Shengine\Processing\Registers $registers, \TrsVendors\Dgm\Shengine\Interfaces\IPackage $package)
    {
        foreach ($registers->rates as $rate) {
            $rate->cost = $this->range->clamp($rate->cost);
        }
    }

    public function getType()
    {
        return self::MODIFIER;
    }

    private $range;
}