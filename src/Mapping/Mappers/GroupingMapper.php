<?php
namespace Trs\Mapping\Mappers;

use Trs\Mapping\Interfaces\IMapper;
use Trs\Mapping\Interfaces\IMappingContext;
use Trs\Mapping\Interfaces\IReader;
use TrsVendors\Dgm\Shengine\Grouping\AttributeGrouping;
use TrsVendors\Dgm\Shengine\Grouping\NoopGrouping;


class GroupingMapper implements IMapper
{
    public function read($data, IReader $reader, IMappingContext $context = null)
    {
        if (!isset($data) || $data === '') {
            return new NoopGrouping();
        }

        $attribute = $reader->read('attribute', $data, $context);
        return new AttributeGrouping($attribute);
    }
}
