<?php
namespace Trs\Mapping\Lazy\Wrappers;

use TrsVendors\Dgm\Shengine\Interfaces\ICalculator;
use TrsVendors\Dgm\Shengine\Interfaces\IPackage;


class LazyCalculator extends AbstractLazyWrapper implements ICalculator
{
    public function calculateRatesFor(IPackage $package)
    {
        return $this->load()->calculateRatesFor($package);
    }

    public function multipleRatesExpected()
    {
        return $this->load()->multipleRatesExpected();
    }

    /**
     * @return ICalculator
     */
    protected function load()
    {
        return parent::load();
    }
}