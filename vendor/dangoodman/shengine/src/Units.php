<?php
namespace TrsVendors\Dgm\Shengine;

use Dgm\NumberUnit\NumberUnit;
use Dgm\SimpleProperties\SimpleProperties;


/**
 * @property-read NumberUnit $weight
 * @property-read NumberUnit $dimension
 * @property-read NumberUnit $price
 * @property-read NumberUnit $volume
 */
class Units extends \TrsVendors\Dgm\SimpleProperties\SimpleProperties
{
    public function __construct(\TrsVendors\Dgm\NumberUnit\NumberUnit $price, \TrsVendors\Dgm\NumberUnit\NumberUnit $weight, \TrsVendors\Dgm\NumberUnit\NumberUnit $dimension, \TrsVendors\Dgm\NumberUnit\NumberUnit $volume)
    {
        $this->weight = $weight;
        $this->dimension = $dimension;
        $this->price = $price;
        $this->volume = $volume;
    }

    static public function fromPrecisions($price, $weight, $dimension, $volume = null)
    {
        return new self(
            new \TrsVendors\Dgm\NumberUnit\NumberUnit($price),
            new \TrsVendors\Dgm\NumberUnit\NumberUnit($weight),
            new \TrsVendors\Dgm\NumberUnit\NumberUnit($dimension),
            new \TrsVendors\Dgm\NumberUnit\NumberUnit(isset($volume) ? $volume : pow($dimension, 3))
        );
    }

    protected $weight;
    protected $dimension;
    protected $price;
    protected $volume;
}