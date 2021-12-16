<?php
namespace TrsVendors\Dgm\Shengine\Attributes;

use Dgm\Shengine\Interfaces\IItem;
use Dgm\Shengine\Interfaces\IPackage;


abstract class MapAttribute extends \TrsVendors\Dgm\Shengine\Attributes\AbstractAttribute
{
    public function getValue(\TrsVendors\Dgm\Shengine\Interfaces\IPackage $package)
    {
        $result = array();

        foreach ($package->getItems() as $key => $item) {
            $result[$key] = $this->getItemValue($item);
        }

        return $result;
    }

    protected abstract function getItemValue(\TrsVendors\Dgm\Shengine\Interfaces\IItem $item);
}