<?php
namespace TrsVendors\Dgm\Shengine\Grouping;

use Dgm\Shengine\Interfaces\IGrouping;
use Dgm\Shengine\Interfaces\IItem;
use Dgm\Shengine\Interfaces\IPackage;


class NoopGrouping implements \TrsVendors\Dgm\Shengine\Interfaces\IGrouping
{
    public function getPackageIds(\TrsVendors\Dgm\Shengine\Interfaces\IItem $item)
    {
        return ['noop'];
    }

    public function multiplePackagesExpected()
    {
        return false;
    }
}
