<?php
namespace TrsVendors\Dgm\Shengine\Attributes;

use Dgm\Shengine\Interfaces\IItem;


class VolumeAttribute extends \TrsVendors\Dgm\Shengine\Attributes\SumAttribute
{
    protected function getItemValue(\TrsVendors\Dgm\Shengine\Interfaces\IItem $item)
    {
        $dimensions = $item->getDimensions();
        $volume = $dimensions->length * $dimensions->width * $dimensions->height;
        return $volume;
    }
}