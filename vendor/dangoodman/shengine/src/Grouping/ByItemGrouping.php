<?php
namespace TrsVendors\Dgm\Shengine\Grouping;

use Dgm\Shengine\Interfaces\IGrouping;
use Dgm\Shengine\Interfaces\IItem;


class ByItemGrouping implements \TrsVendors\Dgm\Shengine\Interfaces\IGrouping
{
    public static function instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getPackageIds(\TrsVendors\Dgm\Shengine\Interfaces\IItem $item)
    {
        return [spl_object_hash($item)];
    }

    public function multiplePackagesExpected()
    {
        return true;
    }

    /** @var self */
    private static $instance;

    private function __construct()
    {
    }
}
