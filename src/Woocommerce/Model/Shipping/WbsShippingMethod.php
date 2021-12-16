<?php
namespace Trs\Woocommerce\Model\Shipping;


class WbsShippingMethod extends ShippingMethod
{
    public function formatInstanceId(ShippingMethodPersistentId $id = null)
    {
        if (!isset($id)) {
            $id = new ShippingMethodPersistentId(false, $this->id->id);
        }

        return parent::formatInstanceId($id);
    }
}