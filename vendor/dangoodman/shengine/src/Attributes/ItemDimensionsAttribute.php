<?php
namespace TrsVendors\Dgm\Shengine\Attributes;

use Dgm\Shengine\Interfaces\IItem;


class ItemDimensionsAttribute extends \TrsVendors\Dgm\Shengine\Attributes\MapAttribute
{
    protected function getItemValue(\TrsVendors\Dgm\Shengine\Interfaces\IItem $item)
    {
        $dimensions = $item->getDimensions();
        $box = array($dimensions->length, $dimensions->width, $dimensions->height);
        return $box;
    }
}