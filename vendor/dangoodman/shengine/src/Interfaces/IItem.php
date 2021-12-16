<?php
namespace TrsVendors\Dgm\Shengine\Interfaces;

use Dgm\Shengine\Model\Dimensions;


interface IItem extends \TrsVendors\Dgm\Shengine\Interfaces\IItemAggregatables
{
    /**
     * @return string
     */
    function getProductId();

    /**
     * @return string
     */
    function getProductVariationId();

    /**
     * @return Dimensions
     */
    function getDimensions();
}