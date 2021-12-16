<?php
namespace TrsVendors\Dgm\Shengine\Conditions\Common\Compare;

use Dgm\Comparator\IComparator;
use Dgm\Range\Range;
use Dgm\Shengine\Conditions\Common\AbstractCondition;


class BetweenCondition extends \TrsVendors\Dgm\Shengine\Conditions\Common\AbstractCondition
{
    public function __construct(\TrsVendors\Dgm\Range\Range $range, \TrsVendors\Dgm\Comparator\IComparator $comparator)
    {
        $this->range = $range;
        $this->comparator = $comparator;
    }

    public function isSatisfiedBy($value)
    {
        return $this->range->includes($value, $this->comparator);
    }

    private $range;
    private $comparator;
}