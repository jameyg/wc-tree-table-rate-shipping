<?php
namespace Trs\Woocommerce;

use Exception;
use Trs\Woocommerce\Model\Shipping\ShippingMethodPersistentId;
use WC_Shipping_Method;
use WC_Shipping_Zones;


class ShippingMethodLoader
{
    /**
     * @return WC_Shipping_Method
     * @throws ShippingMethodNotLoaded
     */
    public function load(ShippingMethodPersistentId $id)
    {
        $method = null;

        if ($id->global) {

            $methods = WC()->shipping()->load_shipping_methods();
            $method = @$methods[$id->id];

        } else {

            if (!class_exists('WC_Shipping_Zones')) {
                throw new ShippingMethodNotLoaded(
                    "Couldn't load a shipping method instance '{$id}' since ".
                    "current Woocommerce version doesn't seem to support Shipping Zones"
                );
            }

            $method = WC_Shipping_Zones::get_shipping_method($id->id);
            if ($method === false) {
                $method = null;
            }
        }

        if (!isset($method)) {
            throw new ShippingMethodNotLoaded("Shipping method '{$id}' not found.");
        }

        return $method;
    }
}

class ShippingMethodNotLoaded extends Exception {}