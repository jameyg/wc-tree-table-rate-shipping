<?php
namespace Trs\Mapping\Mappers;

use TrsVendors\BoxPacking\Packer;
use TrsVendors\Dgm\Shengine\Conditions\Common\Enum\EmptyEnumCondition;
use Exception;
use InvalidArgumentException;
use LogicException;
use TrsVendors\Dgm\NumberUnit\NumberUnit;
use TrsVendors\Dgm\Range\Range;
use TrsVendors\Dgm\Shengine\Conditions\Common\Compare\BetweenCondition;
use TrsVendors\Dgm\Shengine\Conditions\Common\Compare\EqualCondition;
use TrsVendors\Dgm\Shengine\Conditions\Common\Compare\GreaterCondition;
use TrsVendors\Dgm\Shengine\Conditions\Common\Compare\GreaterOrEqualCondition;
use TrsVendors\Dgm\Shengine\Conditions\Common\Compare\LessCondition;
use TrsVendors\Dgm\Shengine\Conditions\Common\Compare\LessOrEqualCondition;
use TrsVendors\Dgm\Shengine\Conditions\Common\Compare\NotEqualCondition;
use TrsVendors\Dgm\Shengine\Conditions\Common\Enum\DisjointCondition;
use TrsVendors\Dgm\Shengine\Conditions\Common\Enum\EqualEnumCondition;
use TrsVendors\Dgm\Shengine\Conditions\Common\Enum\IntersectCondition;
use TrsVendors\Dgm\Shengine\Conditions\Common\Enum\SubsetCondition;
use TrsVendors\Dgm\Shengine\Conditions\Common\Enum\SupersetCondition;
use TrsVendors\Dgm\Shengine\Conditions\Common\Logic\NotCondition;
use TrsVendors\Dgm\Shengine\Conditions\Common\Stub\TrueCondition;
use TrsVendors\Dgm\Shengine\Conditions\DestinationCondition;
use TrsVendors\Dgm\Shengine\Conditions\ItemsPackableCondition;
use TrsVendors\Dgm\Shengine\Conditions\Package\AbstractPackageCondition;
use TrsVendors\Dgm\Shengine\Conditions\Package\PackageAttributeCondition;
use TrsVendors\Dgm\Shengine\Conditions\Package\TermsCondition;
use TrsVendors\Dgm\Shengine\Interfaces\IAttribute;
use TrsVendors\Dgm\Shengine\Units;
use Trs\Mapping\Interfaces\IMappingContext;
use Trs\Mapping\Interfaces\IReader;


class PackageConditionMapper extends AbstractMapper
{
    public function __construct(Packer $boxPacker, Units $units)
    {
        $this->boxPacker = $boxPacker;
        $this->units = $units;
    }

    public function read($data, IReader $reader, IMappingContext $context = null)
    {
        $type = (string)$data['condition'];

        if ($type === 'true') {
            return new TrueCondition();
        }

        
        $operator = $data['operator'];

        $condition = null;
        $attribute = $type;
        switch ($type) {

            case 'terms':

                $termsByTaxonomy = array();
                foreach ($data['value'] as $termWithTaxonomy) {
                    list($taxonomy, $term) = explode(':', $termWithTaxonomy, 2);
                    $termsByTaxonomy[$taxonomy][] = $term;
                }

                @list($searchMode, $allowOthers) = explode('&', $operator, 2);
                
                static $searchModeMap = array(
                    'any' => TermsCondition::SEARCH_ANY,
                    'all' => TermsCondition::SEARCH_ALL,
                    'no' =>  TermsCondition::SEARCH_NO,
                );
                
                $searchMode = @$searchModeMap[$searchMode];
                if (!isset($searchMode)) {
                    throw new Exception("Invalid terms search mode '{$operator}'");
                }
                
                if ($allowOthers !== null && $allowOthers !== 'only') {
                    throw new Exception("Invalid terms search operator '{$operator}'");
                } else {
                    $allowOthers = ($allowOthers === null);
                }

                $subcondition = null;
                if (!empty($data['subcondition']['condition'])) {
                    $subcondition = $this->read($data['subcondition'], $reader, $context);
                }
                
                $condition = new TermsCondition($termsByTaxonomy, $searchMode, $allowOthers, $subcondition);
                
                break;
            
            case 'destination':

                $condition = new DestinationCondition($data['value']);

                if ($operator == 'disjoint') {
                    $condition = new NotCondition($condition);
                }

                break;

            case 'package':

                $condition = new ItemsPackableCondition($this->boxPacker, $data['box']);

                if ($operator == 'larger') {
                    $condition = new NotCondition($condition);
                }

                $attribute = 'item_dimensions';

                break;
            
            case 'customer':

                $condition = static::createEnumCondition($operator, $data['value']);
                $attribute = "{$type}_{$data['attribute']}";
                
                break;

            case 'coupons':

                $value = isset($data['value']) ? array_map('strtolower', $data['value']) : array();
                $condition = static::createEnumCondition($operator, $value);
                $attribute = 'coupons';

                break;

            case 'weight':
            case 'volume':
            case 'price':
            case 'count':

                $compareWith =
                    $operator == 'btw'
                        ? new Range(
                            ($min = (string)@$data['min']) === '' ? null : $min,
                            ($max = (string)@$data['max']) === '' ? null : $max
                        )
                        : $data['value'];

                $unit = null;
                switch ($type) {
                    case 'price': $unit = $this->units->price; break;
                    case 'weight': $unit = $this->units->weight; break;
                    case 'volume': $unit = $this->units->volume; break;
                    case 'count': $unit = NumberUnit::$INT; break;
                    default: throw new LogicException();
                }

                $condition = static::getNumberCondition($operator, $compareWith, $unit);

                if ($type == 'price') {
                    $attribute = array(
                        'attribute' => $attribute,
                        'price_kind' => @$data['price_kind'],
                    );
                }

                break;
        }

        if (!($condition instanceof AbstractPackageCondition)) {
            
            if (!isset($condition, $attribute)) {
                throw new Exception("Could not instantiate condition " . json_encode($data));
            }

            if (!($attribute instanceof IAttribute)) {
                $attribute = $reader->read('attribute', $attribute, $context);
            }

            $condition = new PackageAttributeCondition($condition, $attribute);
        }

        return $condition;
    }


    private $boxPacker;
    private $units;

    static private function createEnumCondition($operator, $value)
    {
        $innerConditionClass = static::getEnumConditionClass($operator);
        $condition = new $innerConditionClass($value);
        return $condition;
    }

    static private function getNumberCondition($operator, $compareWith, NumberUnit $unit)
    {
        switch ((string)$operator) {
            case 'btw' : return new BetweenCondition($compareWith, $unit);
            case 'eq'  : return new EqualCondition($compareWith, $unit);
            case 'ne'  : return new NotEqualCondition($compareWith, $unit);
            case 'gt'  : return new GreaterCondition($compareWith, $unit);
            case 'gte' : return new GreaterOrEqualCondition($compareWith, $unit);
            case 'lt'  : return new LessCondition($compareWith, $unit);
            case 'lte' : return new LessOrEqualCondition($compareWith, $unit);
            default:
                throw new InvalidArgumentException("Unknown number condition operator '{$operator}'");
        }
    }

    static private function getEnumConditionClass($operator)
    {
        $conditions = array(
            'intersect' => IntersectCondition::className(),
            'disjoint' => DisjointCondition::className(),
            'superset' => SupersetCondition::className(),
            'subset' => SubsetCondition::className(),
            'equal' => EqualEnumCondition::className(),
            'empty' => EmptyEnumCondition::className(),
        );

        if (!$condition = $conditions[$operator]) {
            throw new Exception("Uknown condition operator '{$operator}'");
        }

        return $condition;
    }
}