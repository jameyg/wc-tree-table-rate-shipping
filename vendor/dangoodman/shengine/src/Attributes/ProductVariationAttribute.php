<?php
namespace TrsVendors\Dgm\Shengine\Attributes;

use Dgm\Shengine\Interfaces\IItem;


class ProductVariationAttribute extends \TrsVendors\Dgm\Shengine\Attributes\MapAttribute
{
    protected function getItemValue(\TrsVendors\Dgm\Shengine\Interfaces\IItem $item)
    {
        $id = $item->getProductVariationId();
        $id = isset($id) ? $id : $item->getProductId();
        return $id;
    }
}