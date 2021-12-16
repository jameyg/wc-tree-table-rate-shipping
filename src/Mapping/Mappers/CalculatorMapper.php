<?php
namespace Trs\Mapping\Mappers;

use InvalidArgumentException;
use LogicException;
use Trs\Mapping\Interfaces\IMappingContext;
use Trs\Mapping\Interfaces\IReader;
use TrsVendors\Dgm\NumberUnit\NumberUnit;
use TrsVendors\Dgm\Shengine\Calculators\AttributeMultiplierCalculator;
use TrsVendors\Dgm\Shengine\Calculators\ConstantCalculator;
use TrsVendors\Dgm\Shengine\Calculators\FreeCalculator;
use TrsVendors\Dgm\Shengine\Calculators\ProgressiveCalculator;
use TrsVendors\Dgm\Shengine\Units;


class CalculatorMapper extends AbstractMapper
{
    public function __construct(Units $units)
    {
        $this->units = $units;
    }

    public function read($data, IReader $reader, IMappingContext $context = null)
    {
        $calculator = null;

        switch ($type = @$data['calculator']) {

            case 'free':
                $calculator = new FreeCalculator();
                break;

            case 'const':
                $calculator = new ConstantCalculator(self::receiveFloat(@$data['value']));
                break;

            case 'percentage':
                if (($target = @$data['target']) !== 'package_price') {
                    throw new InvalidArgumentException("Unknown percentage calculator target '{$target}'.");
                }

                if (!is_numeric($percentage = @$data['value'])) {
                    throw new InvalidArgumentException("Percentage value is not a number: '{$percentage}'.");
                }

                $attribute = $reader->read('attribute', array(
                    'attribute' => 'price',
                    'price_kind' => @$data['price_kind'],
                ), $context);

                $calculator = new AttributeMultiplierCalculator($attribute, $percentage/100);

                break;

            case 'weight':
            case 'count':
            case 'volume':
            case 'price':

                if ($type === 'price') {
                    $attribute = $reader->read('attribute', array(
                        'attribute' => $type,
                        'price_kind' => @$data['price_kind'],
                    ));
                } else {
                    $attribute = $reader->read('attribute', $type, $context);
                }

                $unit = null;
                switch ($type) {
                    case 'weight': $unit = $this->units->weight; break;
                    case 'count': $unit = NumberUnit::$INT; break;
                    case 'volume': $unit = $this->units->volume; break;
                    case 'price': $unit = $this->units->price; break;
                    default: throw new LogicException();
                }

                $calculator = new ProgressiveCalculator(
                    $attribute,
                    $unit,
                    self::receiveFloat($data['cost']),
                    self::receiveFloat(@$data['step'], 1),
                    self::receiveFloat(@$data['skip'])
                );
                break;

            case 'shipping_method':
                $calculator = $reader->read('shipping_method_calculator', $data, $context);
                break;

            case 'children':
                $calculator = $reader->read('children_calculator', $data, $context);
                break;

            default:
                throw new InvalidArgumentException("Uknown calculator type '{$type}'.");
        }

        return $calculator;
    }

    private static function receiveFloat($value, $default = 0)
    {
        if ($value === '') {
            $value = null;
        }

        if (!isset($value)) {
            $value = $default;
        }

        if (isset($value)) {

            if (!is_numeric($value)) {
                throw new InvalidArgumentException("Invalid number value '{$value}'.");
            }

            $value = (float)$value;
        }

        return $value;
    }


    private $units;
}