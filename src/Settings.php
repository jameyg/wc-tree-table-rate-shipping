<?php
namespace Trs;

/**
 * @property-read bool $preferCustomPackagePrice;
 * @property-read bool $includeNonShippableItems;
 */
class Settings
{
    public static function instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    function __get($property)
    {
        return $this->{$property};
    }

    protected function __construct()
    {
        $_settings = get_option('trs_settings');
        if (!is_array($_settings)) {
            return;
        }

        if (isset($_settings['preferCustomPackagePrice'])) {
            $this->preferCustomPackagePrice = (bool)$_settings['preferCustomPackagePrice'];
        }

        if (isset($_settings['includeNonShippableItems'])) {
            $this->includeNonShippableItems = (bool)$_settings['includeNonShippableItems'];
        }
        $this->includeNonShippableItems = apply_filters('trs_include_non_shippable_items', $this->includeNonShippableItems);
    }

    /** @var self */
    private static $instance;

    private $preferCustomPackagePrice = true;
    private $includeNonShippableItems = false;
}
