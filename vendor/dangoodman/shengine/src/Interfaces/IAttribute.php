<?php
namespace TrsVendors\Dgm\Shengine\Interfaces;


interface IAttribute
{
    /**
     * @param IPackage $package
     * @return mixed
     */
    function getValue(\TrsVendors\Dgm\Shengine\Interfaces\IPackage $package);
}