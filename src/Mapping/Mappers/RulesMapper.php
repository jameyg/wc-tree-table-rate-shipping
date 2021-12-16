<?php
namespace Trs\Mapping\Mappers;

use TrsVendors\Dgm\Arrays\Arrays;
use Trs\Mapping\Interfaces\ILazyFactory;
use Trs\Mapping\Interfaces\ILazyFactoryAware;
use Trs\Mapping\Interfaces\IMappingContext;
use Trs\Mapping\Interfaces\IReader;


class RulesMapper extends AbstractMapper implements ILazyFactoryAware
{
    public function read($data, IReader $reader, IMappingContext $context = null)
    {
        if (!isset($data)) {
            return array();
        }

        $this->requireType($data, 'traversable');

        return $this->lazyFactory->lazyArray(function() use($reader, $data, $context) {
            return Arrays::filter(Arrays::map($data, function($rule) use($reader, $context) {
                 return $reader->read('rule', $rule, $context);
            }));
        });
    }

    public function setLazyFactory(ILazyFactory $lazyFactory)
    {
        $this->lazyFactory = $lazyFactory;
    }

    /** @var ILazyFactory */
    private $lazyFactory;
}