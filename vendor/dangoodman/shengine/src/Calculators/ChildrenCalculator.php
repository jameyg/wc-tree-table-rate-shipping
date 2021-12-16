<?php
namespace TrsVendors\Dgm\Shengine\Calculators;

use Dgm\Shengine\Interfaces\ICalculator;
use Dgm\Shengine\Interfaces\IPackage;
use Dgm\Shengine\Interfaces\IProcessor;


class ChildrenCalculator implements \TrsVendors\Dgm\Shengine\Interfaces\ICalculator
{
    public function __construct(\TrsVendors\Dgm\Shengine\Interfaces\IProcessor $processor, $children)
    {
        $this->processor = $processor;
        $this->children = $children;
    }

    public function calculateRatesFor(\TrsVendors\Dgm\Shengine\Interfaces\IPackage $package)
    {
        return $this->processor->process($this->children, $package);
    }

    public function multipleRatesExpected()
    {
        return !empty($this->children);
    }

    private $processor;
    private $children;
}