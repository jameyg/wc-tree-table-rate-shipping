<?php
namespace Trs\Woocommerce\Model\Shipping;

use TrsVendors\Dgm\SimpleProperties\SimpleProperties;
use Trs\Services\ApiService;
use TrsVendors\Dgm\WcTools\WcTools;
use WC_Shipping_Method;


/**
 * @property-read ShippingMethodPersistentId $id
 * @property-read ShippingMethodFamily $family
 * @property-read ShippingZone $zone
 *
 * @property-read string $title
 *
 * @property-read string|null $settingsPageUrl
 * @property-read string $apiUrl
 *
 * @property-read boolean $enabled
 */
class ShippingMethod extends SimpleProperties
{
    static public function createFrom(WC_Shipping_Method $wcMethod, ShippingMethodFamily $family, ShippingZone $zone)
    {
        $isGlobalInstance = self::isGlobalInstance($wcMethod);

        $id = new ShippingMethodPersistentId(
            $isGlobalInstance,
            $isGlobalInstance ? $wcMethod->id : self::getInstanceId($wcMethod)
        );

        $settingsPageUrl =
            $id->global && ($wcMethod->supports('settings') || empty($wcMethod->supports)) ||
            !$id->global && $wcMethod->supports('instance-settings')
                ? self::makeSettingsPageUrl($id, get_class($wcMethod))
                : null;

        return new self(
            $id, $family, $zone,
            $wcMethod->get_title(),
            $settingsPageUrl,
            self::makeApiUrl($id),
            WcTools::yesNo2Bool($wcMethod->enabled)
        );
    }

    static public function isGlobalInstance(WC_Shipping_Method $wcMethod)
    {
        return self::getInstanceId($wcMethod) <= 0;
    }

    static public function getInstanceId(WC_Shipping_Method $wcMethod)
    {
        return method_exists($wcMethod, 'get_instance_id')
            ? $wcMethod->get_instance_id()
            : @$wcMethod->instance_id;
    }

    static public function makeSettingsPageUrl(ShippingMethodPersistentId $id, $wcShippingMethodClass)
    {
        $url = 'admin.php?page=wc-settings&tab=shipping';

        $urlIdKey = null;
        $urlId = $id->id;
        if ($id->global) {
            if (version_compare(WC()->version, '2.6.0', '<')) {
                $urlId = strtolower($wcShippingMethodClass);
            }
            $urlIdKey = 'section';
        } else {
            $urlIdKey = 'instance_id';
        }

        $urlId = rawurlencode($urlId);
        $url .= "&{$urlIdKey}={$urlId}";

        return admin_url($url);
    }

    static public function makeApiUrl(ShippingMethodPersistentId $id)
    {
        return ApiService::url(ApiService::AJAX_ACTION_SHIPPING_METHOD, array('id' => $id->serialize()));
    }

    public function __construct(
        ShippingMethodPersistentId $id, ShippingMethodFamily $family, ShippingZone $zone,
        $title,
        $settingsPageUrl, $apiUrl,
        $enabled)
    {
        $this->id = $id;
        $this->family = $family;
        $this->zone = $zone;

        $this->title = self::receiveString($title, '(untitled method)');

        $this->settingsPageUrl = $settingsPageUrl;
        $this->apiUrl = $apiUrl;

        $this->enabled = $enabled;
    }

    public function formatInstanceId(ShippingMethodPersistentId $id = null)
    {
        if (!isset($id)) {
            $id = $this->id;
        }

        return $id->global ? null : "#{$id->id}";
    }


    protected $id;
    protected $family;
    protected $zone;

    protected $title;

    protected $settingsPageUrl;
    protected $enableApiUrl;

    protected $enabled;


    static private function receiveString($string, $default = '(empty)')
    {
        $string = (string)$string;

        if ($string === '') {
            $string = $default;
        }

        return $string;
    }
}