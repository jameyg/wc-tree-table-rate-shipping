<?php
namespace TrsVendors\Dgm\Shengine\Operations;

use InvalidArgumentException;
use Dgm\Shengine\Interfaces\IPackage;
use Dgm\Shengine\Processing\Registers;


class MultiplyOperation extends \TrsVendors\Dgm\Shengine\Operations\AbstractOperation
{
    public function __construct($multiplier)
    {
        if (!is_numeric($multiplier)) {
            throw new InvalidArgumentException();
        }

        $this->multiplier = $multiplier;
    }

    public function process(\TrsVendors\Dgm\Shengine\Processing\Registers $registers, \TrsVendors\Dgm\Shengine\Interfaces\IPackage $package)
    {
        foreach ($registers->rates as $rate) {
            $rate->cost *= $this->multiplier;
        }
    }

    public function getType()
    {
        return self::MODIFIER;
    }

    private $multiplier;
}