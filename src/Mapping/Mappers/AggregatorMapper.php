<?php
namespace Trs\Mapping\Mappers;

use Trs\Factory\Interfaces\IRegistry;
use Trs\Mapping\Interfaces\IMappingContext;
use Trs\Mapping\Interfaces\IReader;


class AggregatorMapper extends AbstractMapper
{
    public function __construct(IRegistry $rateAggregators)
    {
        $this->rateAggregators = $rateAggregators;
    }

    public function read($aggregatorName, IReader $reader, IMappingContext $context = null)
    {
        $aggregator = null;

        if (isset($aggregatorName) && $aggregatorName != 'all') {
            $aggregator = $this->rateAggregators->get($aggregatorName);
            if (!isset($aggregator)) {
                throw new \Exception("Uknown rate aggregator '{$aggregatorName}'");
            }
        }

        return $aggregator;
    }

    private $rateAggregators;
}