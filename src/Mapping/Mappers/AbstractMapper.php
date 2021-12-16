<?php
namespace Trs\Mapping\Mappers;

use InvalidArgumentException;
use Traversable;
use TrsVendors\Dgm\ClassNameAware\ClassNameAware;
use Trs\Mapping\Interfaces\IMapper;


abstract class AbstractMapper extends ClassNameAware implements IMapper
{
    protected function requireType($value, $type)
    {
        /** @noinspection PhpUnusedLocalVariableInspection */
        $error = false;
        if ($type == 'traversable') {
            $error = !(is_array($value) || $value instanceof Traversable);
        } else {
            $error = !(gettype($value) === $type);
        }

        if ($error) {
            throw new InvalidArgumentException(sprintf("%s expected, %s given", $type, gettype($value)));
        }
    }
}