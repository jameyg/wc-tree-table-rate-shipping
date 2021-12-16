<?php
namespace Trs\Mapping;

use InvalidArgumentException;
use Trs\Factory\Interfaces\IRegistry;
use Trs\Mapping\Interfaces\IMapper;
use Trs\Mapping\Interfaces\IMappingContext;
use Trs\Mapping\Interfaces\IReader;


class Reader implements IReader
{
    public function __construct(IRegistry $mappers)
    {
        $this->mappers = $mappers;
    }

    public function read($objectType, $data, IMappingContext $context = null)
    {
        /** @var IMapper $mapper */
        $mapper = $this->mappers->get($objectType);
        if (!isset($mapper)) {
            throw new InvalidArgumentException("Mapper with id '{$objectType}' is not registered");
        }

        return $mapper->read($data, $this, $context);
    }

    private $mappers;
}