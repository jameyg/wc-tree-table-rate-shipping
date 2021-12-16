<?php
namespace Trs\Mapping\Mappers;

use TrsVendors\Dgm\Shengine\RuleMatcher;
use TrsVendors\Dgm\Shengine\RuleMatcherMeta;
use Trs\Mapping\Interfaces\IMappingContext;
use Trs\Mapping\Interfaces\IReader;


class RuleMatcherMapper extends AbstractMapper
{
    public function read($data, IReader $reader, IMappingContext $context = null)
    {
        $grouping = $reader->read('grouping', @$data['meta']['grouping'], $context);
        $meta = new RuleMatcherMeta((bool)@$data['meta']['capture'], $grouping, (bool)@$data['meta']['require_all_packages']);
        $condition = $reader->read('rule_matcher_condition', $data, $context);

        return new RuleMatcher($meta, $condition);
    }
}