<?php
namespace Trs\Woocommerce;

use TrsVendors\Dgm\Arrays\Arrays;
use TrsVendors\Dgm\Shengine\Calculators\AggregatedCalculator;
use TrsVendors\Dgm\Shengine\Conditions\Common\StringPatternCondition;
use Trs\Mapping\Interfaces\IMappingContext;
use Trs\Mapping\Interfaces\IReader;
use Trs\Mapping\Mappers\AbstractMapper;
use Trs\Woocommerce\Model\Shipping\ShippingMethodPersistentId;


class ShippingMethodCalculatorMapper extends AbstractMapper
{
    public function __construct(ShippingMethodLoader $loader)
    {
        $this->loader = $loader;
    }

    public function read($data, IReader $reader, IMappingContext $context = null)
    {
        $methods = array();
        if (isset($data['ids'])) {

            $loader = $this->loader;

            $methods = Arrays::filter(Arrays::map($data['ids'], function($id) use($loader) {

                $id = ShippingMethodPersistentId::unserialize($id);

                try {
                    return $loader->load($id);
                } catch (ShippingMethodNotLoaded $e) {
                    //TODO log a warning to admin
                    return null;
                }
            }));
        }

        $rateNamePattern = (string)@$data['rate_name'];
        $rateNameFilter = new StringPatternCondition($rateNamePattern === '' ? '*' : $rateNamePattern);

        return new AggregatedCalculator(
            new ShippingMethodCalculator($methods, $rateNameFilter),
            $reader->read('aggregator', @$data['aggregator'], $context)
        );
    }

    private $loader;
}