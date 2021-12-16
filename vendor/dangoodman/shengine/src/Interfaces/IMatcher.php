<?php
namespace TrsVendors\Dgm\Shengine\Interfaces;


interface IMatcher
{
    /**
     * @param IPackage $package
     * @return IPackage|null
     */
    function getMatchingPackage(\TrsVendors\Dgm\Shengine\Interfaces\IPackage $package);

    /**
     * @return bool
     */
    function isCapturingMatcher();
}