<?php
namespace Trs\Mapping\Interfaces;

use Traversable;
use TrsVendors\Dgm\Shengine\Interfaces\IRule;


interface IMappingContext
{
    /**
     * @return IRule[]|Traversable
     */
    function getCurrentRuleChildren();
}