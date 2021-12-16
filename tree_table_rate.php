<?php
use Trs\Woocommerce\ShippingMethod;

/**
 * An alias class intended to give its name to WooCommerce 'section' parameter.
 */
class tree_table_rate extends ShippingMethod
{
    public static function className() {
        return __CLASS__;
    }
}