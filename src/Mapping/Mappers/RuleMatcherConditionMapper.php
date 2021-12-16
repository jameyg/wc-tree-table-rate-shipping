<?php
namespace Trs\Mapping\Mappers;

use InvalidArgumentException;
use TrsVendors\Dgm\Arrays\Arrays;
use TrsVendors\Dgm\Shengine\Conditions\Common\Logic\AndCondition;
use TrsVendors\Dgm\Shengine\Conditions\Common\Logic\OrCondition;
use Trs\Mapping\Interfaces\IMappingContext;
use Trs\Mapping\Interfaces\IReader;


class RuleMatcherConditionMapper extends AbstractMapper
{
    public function read($data, IReader $reader, IMappingContext $context = null)
    {
        $conditions = null; {

            $conditions = @$data['list'];
            if (!isset($conditions)) {
                $conditions = array();
            }

            $this->requireType($conditions, 'array');

            $conditions = Arrays::map($conditions, function ($condition) use ($reader, $context) {
                return $reader->read('package_condition', $condition, $context);
            });
        }

        $mode = null; {

            $mode = @$data['meta']['mode'];
            if (!isset($mode)) {
                $mode = 'and';
            }

            if (!in_array($mode, array('and', 'or'), true)) {
                throw new InvalidArgumentException("Unknown conditions list mode: '{$mode}'. Either 'and' or 'or' expected. ");
            }
        }

        $condition = ($mode === 'and'
            ? new AndCondition($conditions)
            : new OrCondition($conditions));

        return $condition;
    }
}