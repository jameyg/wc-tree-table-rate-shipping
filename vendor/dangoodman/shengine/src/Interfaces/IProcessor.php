<?php
namespace TrsVendors\Dgm\Shengine\Interfaces;

use Traversable;


interface IProcessor
{
    /**
     * @param Traversable|IRule[] $rules
     * @param IPackage $package
     * @return IRate[]
     */
    public function process($rules, \TrsVendors\Dgm\Shengine\Interfaces\IPackage $package);
}