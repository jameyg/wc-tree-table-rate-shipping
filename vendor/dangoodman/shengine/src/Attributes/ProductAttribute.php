<?php
namespace TrsVendors\Dgm\Shengine\Attributes;

use Dgm\Shengine\Interfaces\IItem;


class ProductAttribute extends \TrsVendors\Dgm\Shengine\Attributes\MapAttribute
{
    protected function getItemValue(\TrsVendors\Dgm\Shengine\Interfaces\IItem $item)
    {
        return $item->getProductId();
    }
}