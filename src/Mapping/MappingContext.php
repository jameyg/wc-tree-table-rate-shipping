<?php
namespace Trs\Mapping;

use Trs\Mapping\Interfaces\IMappingContext;


class MappingContext implements IMappingContext
{
    public function __construct($currentRuleChildren)
    {
        $this->currentRuleChildren = $currentRuleChildren;
    }

    public function getCurrentRuleChildren()
    {
        return $this->currentRuleChildren;
    }

    private $currentRuleChildren;
}