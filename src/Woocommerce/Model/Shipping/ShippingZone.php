<?php
namespace Trs\Woocommerce\Model\Shipping;

use TrsVendors\Dgm\SimpleProperties\SimpleProperties;


/**
 * @property-read string $title
 */
class ShippingZone extends SimpleProperties
{
    /** @var ShippingZone */
    static $GLOBAL;


    public function __construct($title)
    {
        $this->title = $title;
    }

    protected $title;
}

ShippingZone::$GLOBAL = new ShippingZone('Global');