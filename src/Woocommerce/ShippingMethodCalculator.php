<?php
namespace Trs\Woocommerce;

use Exception;
use RuntimeException;
use TrsVendors\Dgm\Shengine\Interfaces\ICalculator;
use TrsVendors\Dgm\Shengine\Interfaces\ICondition;
use TrsVendors\Dgm\Shengine\Interfaces\IPackage;
use TrsVendors\Dgm\Shengine\Woocommerce\Converters\PackageConverter;
use TrsVendors\Dgm\Shengine\Woocommerce\Converters\RateConverter;
use WC_Shipping_Method;


class ShippingMethodCalculator implements ICalculator
{
    /**
     * @param WC_Shipping_Method[] $shippingMethods
     * @param ICondition $rateNameFilter
     */
    public function __construct(array $shippingMethods, ICondition $rateNameFilter)
    {
        $this->shippingMethods = $shippingMethods;
        $this->rateNameFilter = $rateNameFilter;
    }

    public function calculateRatesFor(IPackage $package)
    {
        $rates = array();

        $wcPackage = PackageConverter::fromCoreToWoocommerce($package);

        foreach ($this->shippingMethods as $shippingMethod) {

            $ratesBkp = $shippingMethod->rates;
            $enabledBkp = $shippingMethod->enabled;

            $e = null;
            try {
                $shippingMethod->rates = array();
                $shippingMethod->enabled = 'yes';

                if ($shippingMethod->is_available($wcPackage)) {

                    /** @noinspection PhpUndefinedMethodInspection */
                    $shippingMethod->calculate_shipping($wcPackage);

                    foreach (RateConverter::fromWoocommerceToCore($shippingMethod->rates) as $rate) {
                        /** @noinspection PhpUndefinedMethodInspection */
                        $title = $rate->getTitle();
                        if ($this->rateNameFilter->isSatisfiedBy($title)) {
                            $rates[] = $rate;
                        }
                    }
                }
            }
            catch (Exception $e) {
                // re-thrown below
            }

            $shippingMethod->enabled = $enabledBkp;
            $shippingMethod->rates = $ratesBkp;

            if (isset($e)) {
                /** @noinspection PhpUndefinedFieldInspection */
                $id = $shippingMethod->instance_id ?: $shippingMethod->id;
                throw new RuntimeException(
                    "An exception occurred while invoking external shipping method
                    '{$shippingMethod->title}'(id: '{$id}')",
                    0, $e
                );
            }
        }

        return $rates;
    }

    public function multipleRatesExpected()
    {
        return !empty($this->shippingMethods);
    }

    private $shippingMethods;
    private $rateNameFilter;
}