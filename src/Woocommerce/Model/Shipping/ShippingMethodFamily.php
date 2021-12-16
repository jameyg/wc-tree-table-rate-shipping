<?php
namespace Trs\Woocommerce\Model\Shipping;

use InvalidArgumentException;
use TrsVendors\Dgm\SimpleProperties\SimpleProperties;
use WC_Shipping_Method;


/**
 * @property-read string $id
 * @property-read string $title
 * @property-read bool $supportsInstances
 * @property-read bool $supportsGlobalInstance
 * @property-read ShippingMethod[] $methods
 * @property-read string $addInstanceUrl
 */
class ShippingMethodFamily extends SimpleProperties
{
    static public function createFrom(WC_Shipping_Method $wcMethod)
    {
        $supportsInstances = version_compare(WC()->version, '2.6.0', '>=') && $wcMethod->supports('shipping-zones');
        $supportsGlobalInstance = !$supportsInstances || $wcMethod->supports('global-instance');
        $title = method_exists($wcMethod, 'get_method_title') ? $wcMethod->get_method_title() : $wcMethod->method_title;

        return new self(
            self::getFamilyId($wcMethod),
            $title,
            $supportsInstances,
            $supportsGlobalInstance,
            $supportsInstances ? admin_url('admin.php?page=wc-settings&tab=shipping') : null
        );
    }

    static public function getFamilyId(WC_Shipping_Method $wcMethod)
    {
        return $wcMethod->id;
    }

    public function __construct($id, $title, $supportsInstances, $supportsGlobalInstance, $addInstanceUrl)
    {
        $id = (string)$id;
        if ($id === '' || $id === '0') {
            throw new InvalidArgumentException();
        }

        $this->id = $id;
        $this->title = self::receiveString($title, '(untitled family)');
        $this->supportsInstances = $supportsInstances;
        $this->supportsGlobalInstance = $supportsGlobalInstance;
        $this->addInstanceUrl = $addInstanceUrl;
    }

    public function registerMethod(ShippingMethod $method)
    {
        if ($method->family !== $this) {
            throw new \LogicException();
        }

        $this->methods[] = $method;
    }


    protected $id;
    protected $title;
    protected $supportsInstances;
    protected $supportsGlobalInstance;
    protected $methods = array();
    protected $addInstanceUrl;


    static private function receiveString($string, $default = '(empty)')
    {
        $string = (string)$string;

        if ($string === '') {
            $string = $default;
        }

        return $string;
    }
}