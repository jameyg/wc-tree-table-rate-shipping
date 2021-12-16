<?php
namespace Trs\Mapping\Mappers;

use Exception;
use TrsVendors\Dgm\Range\Range;
use TrsVendors\Dgm\Shengine\Operations\AddOperation;
use TrsVendors\Dgm\Shengine\Operations\ClampOperation;
use TrsVendors\Dgm\Shengine\Operations\MultiplyOperation;
use Trs\Mapping\Interfaces\IMappingContext;
use Trs\Mapping\Interfaces\IReader;


class PackageOperationMapper extends AbstractMapper
{
    public function read($data, IReader $reader, IMappingContext $context = null)
    {
        $operation = @$data['operation'];

        if (!$operation || !method_exists($this, $method = "read{$operation}")) {
            throw new Exception("Uknown operation '{$operation}'");
        }

        return $this->$method($data, $reader, $context);
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    private function readAdd($data, IReader $reader, IMappingContext $context)
    {
        $calculator = @$data['calculator'];

        if (@$calculator['calculator'] == 'percentage' &&
            @$calculator['target'] == 'current_rates' &&
            ($percentage = (float)@$calculator['value'])) {
            $multiplyOperationData = array(
                'operation' => 'multiply',
                'multiplier' => 1 + $percentage/100
            );

            return $this->read($multiplyOperationData, $reader, $context);
        }

        return new AddOperation($reader->read('calculator', $calculator, $context));
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    private function readClamp($data)
    {
        return new ClampOperation(new Range(@$data['min'], @$data['max']));
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    private function readMultiply($data)
    {
        return new MultiplyOperation(@$data['multiplier']);
    }
}