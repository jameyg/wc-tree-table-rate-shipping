<?php
namespace Trs\Mapping\Mappers;

use Trs\Mapping\Interfaces\ILazyFactory;
use Trs\Mapping\Interfaces\ILazyFactoryAware;
use Trs\Mapping\Interfaces\IMappingContext;
use Trs\Mapping\Interfaces\IReader;
use Trs\Mapping\MappingContext;
use TrsVendors\Dgm\Shengine\Model\Rule;
use TrsVendors\Dgm\Shengine\Model\RuleMeta;


class RuleMapper extends AbstractMapper implements ILazyFactoryAware
{
    public function read($data, IReader $reader, IMappingContext $context = null)
    {
        $this->requireType($data, 'array');

        if (($enable = @$data['meta']['enable']) !== null && !$enable) {
            return null;
        }

        $children = $reader->read('rules', @$data['children'], $context);

        $context = new MappingContext($children);
        {
            $meta = new RuleMeta(self::nullIfEmpty(@$data['meta']['title']));

            $matcher = $this->lazyFactory->lazyMatcher(function() use($reader, $data, $context) {
                return $reader->read('rule_matcher', @$data['conditions'], $context);
            });

            $calculator = $this->lazyFactory->lazyCalculator(function() use($reader, $data, $context) {
                return $reader->read('rule_calculator', @$data['operations'], $context);
            });

            return new Rule($meta, $matcher, $calculator);
        }
    }

    public function setLazyFactory(ILazyFactory $lazyFactory)
    {
        $this->lazyFactory = $lazyFactory;
    }

    /** @var ILazyFactory */
    private $lazyFactory;

    private static function nullIfEmpty($string)
    {
        if (!isset($string)) {
            return null;
        }

        $string = (string)$string;

        if ($string === '') {
            $string = null;
        }

        return $string;
    }
}