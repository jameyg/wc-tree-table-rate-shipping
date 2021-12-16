<?php
namespace TrsVendors\Dgm\Shengine\Attributes;

use Dgm\Shengine\Interfaces\IAttribute;
use Dgm\Shengine\Interfaces\IPackage;


class CustomerRolesAttribute implements \TrsVendors\Dgm\Shengine\Interfaces\IAttribute
{
    public function getValue(\TrsVendors\Dgm\Shengine\Interfaces\IPackage $package)
    {
        $roles = array();

        if ($customer = $package->getCustomer())
        if ($customerId = $customer->getId())
        if ($wpuser = get_userdata($customerId)) {
            $roles = $wpuser->roles;
        }

        return $roles;
    }
}