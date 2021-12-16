<?php
namespace TrsVendors\Dgm\Shengine\Model;

use Dgm\Shengine\Interfaces\IRuleMeta;


class RuleMeta implements \TrsVendors\Dgm\Shengine\Interfaces\IRuleMeta
{
    public function __construct($title = null, $taxable = null)
    {
        $this->title = $title;
        $this->taxable = $taxable;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function isTaxable()
    {
        return $this->taxable;
    }

    private $title;
    private $taxable;
}