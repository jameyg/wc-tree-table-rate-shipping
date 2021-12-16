<?php
namespace TrsVendors\Dgm\Shengine\Model;

use Dgm\Shengine\Interfaces\ICalculator;
use Dgm\Shengine\Interfaces\IMatcher;
use Dgm\Shengine\Interfaces\IRule;
use Dgm\Shengine\Interfaces\IRuleMeta;


class Rule implements \TrsVendors\Dgm\Shengine\Interfaces\IRule
{
    public function __construct(\TrsVendors\Dgm\Shengine\Interfaces\IRuleMeta $meta, \TrsVendors\Dgm\Shengine\Interfaces\IMatcher $matcher, \TrsVendors\Dgm\Shengine\Interfaces\ICalculator $calculator)
    {
        $this->meta = $meta;
        $this->matcher = $matcher;
        $this->calculator = $calculator;
    }

    public function getMeta()
    {
        return $this->meta;
    }

    public function getMatcher()
    {
        return $this->matcher;
    }

    public function getCalculator()
    {
        return $this->calculator;
    }

    private $meta;
    private $matcher;
    private $calculator;
}