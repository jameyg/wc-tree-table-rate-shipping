<?php
namespace TrsVendors\Dgm\Shengine\Operations;

use Dgm\Arrays\Arrays;
use Dgm\Shengine\Interfaces\ICalculator;
use Dgm\Shengine\Interfaces\IPackage;
use Dgm\Shengine\Processing\RateRegister;
use Dgm\Shengine\Processing\Registers;
use RuntimeException;


class AddOperation extends \TrsVendors\Dgm\Shengine\Operations\AbstractOperation
{
    public function __construct(\TrsVendors\Dgm\Shengine\Interfaces\ICalculator $calculator)
    {
        $this->calculator = $calculator;
    }

    public function process(\TrsVendors\Dgm\Shengine\Processing\Registers $registers, \TrsVendors\Dgm\Shengine\Interfaces\IPackage $package)
    {
        $newRates = isset($this->calculator) ? $this->calculator->calculateRatesFor($package) : array();
        if (!$newRates) {
            return;
        }

        if (count($registers->rates) > 1 && count($newRates) > 1) {
            throw new RuntimeException("Adding up two rate sets is not supported due to ambiguity");
        }

        if (!$registers->rates) {

            $registers->rates = \TrsVendors\Dgm\Arrays\Arrays::map($newRates, function($rate) {
                return new \TrsVendors\Dgm\Shengine\Processing\RateRegister($rate);
            });

            return;
        }

        $newRegistersRates = array();
        foreach ($registers->rates as $rate1) {
            foreach ($newRates as $rate2) {
                $newRegistersRates[] = new \TrsVendors\Dgm\Shengine\Processing\RateRegister(array($rate1, $rate2));
            }
        }

        $registers->rates = $newRegistersRates;
    }

    public function getType()
    {
        return $this->calculator->multipleRatesExpected() ? self::OTHER : self::MODIFIER;
    }

    public function canOperateOnMultipleRates()
    {
        return !$this->calculator->multipleRatesExpected();
    }

    private $calculator;
}