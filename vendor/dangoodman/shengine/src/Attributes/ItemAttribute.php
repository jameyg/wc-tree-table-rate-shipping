<?php
namespace TrsVendors\Dgm\Shengine\Attributes;

use Dgm\Shengine\Interfaces\IItem;


class ItemAttribute extends \TrsVendors\Dgm\Shengine\Attributes\MapAttribute
{
    protected function getItemValue(\TrsVendors\Dgm\Shengine\Interfaces\IItem $item)
    {
        return spl_object_hash($item);
    }
}