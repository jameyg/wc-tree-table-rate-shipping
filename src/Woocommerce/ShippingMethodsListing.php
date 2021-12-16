<?php
namespace Trs\Woocommerce;

use TrsVendors\Dgm\WcTools\WcTools;
use Trs\Woocommerce\Model\Shipping\ShippingMethod as ShippingMethodModel;
use Trs\Woocommerce\Model\Shipping\ShippingMethodFamily;
use Trs\Woocommerce\Model\Shipping\ShippingMethodPersistentId;
use Trs\Woocommerce\Model\Shipping\ShippingZone;
use Trs\Woocommerce\Model\Shipping\WbsShippingMethod;
use WBS_Loader;
use WbsRuleUrls;
use WC_Shipping_Method;
use WC_Shipping_Zones;
use WC_Weight_Based_Shipping;


class ShippingMethodsListing
{
    static public function getListing()
    {
        /** @var ShippingMethodFamily[] $families */
        $families = array();

        /** @var WC_Shipping_Method[] $globalMethods */
        $globalMethods = WC()->shipping()->load_shipping_methods();

        $wbsFamily = self::loadWbsRules($globalMethods);
        if (isset($wbsFamily)) {
            $families[$wbsFamily->id] = $wbsFamily;
        }

        foreach ($globalMethods as $id => $wcMethod) {

            $family = null; {

                $familyId = ShippingMethodFamily::getFamilyId($wcMethod);
                if (!isset($families[$familyId])) {
                    $families[$familyId] = ShippingMethodFamily::createFrom($wcMethod);
                }

                $family = $families[$familyId];
            }

            if ($family->supportsGlobalInstance && ShippingMethodModel::isGlobalInstance($wcMethod)) {
                $family->registerMethod(ShippingMethodModel::createFrom($wcMethod, $family, ShippingZone::$GLOBAL));
            }
        }

        if (class_exists('WC_Shipping_Zones')) {
            $zoneIds = array_keys(WC_Shipping_Zones::get_zones());
            $zoneIds[] = 0;
            foreach ($zoneIds as $id) {

                $wcZone = WC_Shipping_Zones::get_zone($id);
                $zone = new ShippingZone($wcZone->get_zone_name());

                /** @var WC_Shipping_Method $wcMethod */
                foreach ($wcZone->get_shipping_methods(false) as $wcMethod) {

                    $family = null;
                    {
                        $familyId = ShippingMethodFamily::getFamilyId($wcMethod);
                        if (!isset($families[$familyId])) {
                            $family = $families[$familyId] = ShippingMethodFamily::createFrom($wcMethod);
                        } else {
                            $family = $families[$familyId];
                        }
                    }

                    $family->registerMethod(ShippingMethodModel::createFrom($wcMethod, $family, $zone));
                }
            }
        }

        $families = array_filter($families, function(ShippingMethodFamily $family) {
            return $family->methods || $family->supportsInstances;
        });

        $families = FamiliesSorter::sort($families);

        return $families;
    }

    static private function loadWbsRules(array &$globalMethods)
    {
        /** @var WC_Shipping_Method[] $globalMethods */

        $family = null;

        foreach ($globalMethods as $idx => $method) {
            if ($method instanceof WC_Weight_Based_Shipping) {

                unset($globalMethods[$idx]);

                if (!isset($family)) {
                    $family = new ShippingMethodFamily(
                        WBS_Loader::ADMIN_SECTION_NAME,
                        WC_Weight_Based_Shipping::getTitle(),
                        false,
                        true,
                        WbsRuleUrls::generic()
                    );
                }

                $family->registerMethod(new WbsShippingMethod(
                    $id = new ShippingMethodPersistentId(true, $method->id),
                    $family,
                    ShippingZone::$GLOBAL,
                    $method->get_title(),
                    WbsRuleUrls::edit($method),
                    ShippingMethodModel::makeApiUrl($id),
                    WcTools::yesNo2Bool($method->enabled)
                ));
            }
        }

        return $family;
    }
}

class FamiliesSorter
{
    public static function sort(array $families)
    {
        $instance = new self();
        return $instance($families);
    }

    public function __invoke(array $families)
    {
        usort($families, function(ShippingMethodFamily $a, ShippingMethodFamily $b) {

            $result = FamiliesSorter::score($a) - FamiliesSorter::score($b);

            if ($result == 0) {
                $result = strnatcasecmp($a->title, $b->title);
            }

            return -$result;
        });

        return $families;
    }

    public static function score(ShippingMethodFamily $family)
    {
        return
            4 * !self::isEmpty($family) +
            2 * !self::isBuiltIn($family) +
            1 * !self::isLegacy($family);
    }

    private static function isEmpty(ShippingMethodFamily $family)
    {
        return empty($family->methods);
    }

    private static function isBuiltIn(ShippingMethodFamily $family)
    {
        return
            in_array($family->id, array('flat_rate', 'free_shipping', 'local_pickup')) ||
            self::isLegacy($family);
    }

    private static function isLegacy(ShippingMethodFamily $family)
    {
        return strpos($family->id, 'legacy_') === 0;
    }
}




