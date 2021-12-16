<?php
namespace Trs\Factory\Registries;

use TrsVendors\Dgm\Shengine\Aggregators\SumAggregator;
use Trs\Factory\FactoryTools;
use Trs\Factory\Registry;


class RateAggregatorRegistry extends Registry
{
    public function get($id)
    {
        $aggregator = parent::get($id);

        if (!isset($aggregator)) {
            $class = FactoryTools::resolveObjectIdToClass($id, 'Aggregator', SumAggregator::className());
            $aggregator = new $class();
            $this->set($id, $this->asis($aggregator));
        }

        return $aggregator;
    }
}