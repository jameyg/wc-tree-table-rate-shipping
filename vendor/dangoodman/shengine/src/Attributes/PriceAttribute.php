<?php
namespace TrsVendors\Dgm\Shengine\Attributes;

use Dgm\Shengine\Interfaces\IPackage;
use Dgm\Shengine\Model\Price;


class PriceAttribute extends \TrsVendors\Dgm\Shengine\Attributes\AbstractAttribute
{
    public function __construct($flags = \TrsVendors\Dgm\Shengine\Model\Price::BASE)
    {
        $this->flags = $flags;
    }

    public function getValue(\TrsVendors\Dgm\Shengine\Interfaces\IPackage $package)
    {
        return $package->getPrice($this->flags);
    }

    private $flags;
}