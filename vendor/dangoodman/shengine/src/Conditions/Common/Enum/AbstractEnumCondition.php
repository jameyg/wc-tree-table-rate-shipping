<?php
namespace TrsVendors\Dgm\Shengine\Conditions\Common\Enum;

use InvalidArgumentException;
use Dgm\ClassNameAware\ClassNameAware;
use Dgm\Shengine\Interfaces\ICondition;


abstract class AbstractEnumCondition extends \TrsVendors\Dgm\ClassNameAware\ClassNameAware implements \TrsVendors\Dgm\Shengine\Interfaces\ICondition
{
    public function __construct(array $other)
    {
        $this->other = $other;
    }

    protected $other;

    protected function intersect(array $value, array $other)
    {
        return count(array_intersect($this->normalize($value), $this->normalize($other)));
    }

    protected function normalize($list)
    {
        if (!is_array($list)) {
            throw new InvalidArgumentException(sprintf("Array expected, '%s' given.", gettype($list)));
        }

        return array_unique(array_values($list));
    }
}