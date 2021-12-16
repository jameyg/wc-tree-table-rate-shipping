<?php
namespace TrsVendors\Dgm\Shengine;

use Dgm\Shengine\Interfaces\ICondition;
use Dgm\Shengine\Interfaces\IMatcher;
use Dgm\Shengine\Interfaces\IPackage;


class RuleMatcher implements \TrsVendors\Dgm\Shengine\Interfaces\IMatcher
{
    public function __construct(\TrsVendors\Dgm\Shengine\RuleMatcherMeta $meta, \TrsVendors\Dgm\Shengine\Interfaces\ICondition $condition)
    {
        $this->meta = $meta;
        $this->condition = $condition;
    }

    public function getMatchingPackage(\TrsVendors\Dgm\Shengine\Interfaces\IPackage $package)
    {
        return $package->splitFilterMerge($this->meta->grouping, $this->condition, $this->meta->requireAllPackages);
    }

    public function isCapturingMatcher()
    {
        return $this->meta->capture;
    }

    private $meta;
    private $condition;
}
