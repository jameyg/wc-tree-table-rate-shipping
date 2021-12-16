<?php
namespace TrsVendors\Dgm\NumberUnit;

use Dgm\Comparator\NumberComparator;


class NumberUnit extends \TrsVendors\Dgm\Comparator\NumberComparator
{
    /** @var self */
    static $ASIS;

    /** @var self */
    static $INT;


	/**
	 * Returns how many chunks of $chunk size are in the $value.
	 * Roughly, ceil($value / $chunk).
	 *
	 * @param number $value
	 * @param number $chunk
	 * @return int
	 */
    public function chunks($value, $chunk)
    {
        $chunk = $this->normalize($chunk);
        if ($chunk == 0) {
            throw new \InvalidArgumentException("Chunk size cannot be zero.");
        }

	    return (int)ceil($this->normalize($value) / $chunk);
    }
}

\TrsVendors\Dgm\NumberUnit\NumberUnit::$ASIS = new \TrsVendors\Dgm\NumberUnit\NumberUnit(null);
\TrsVendors\Dgm\NumberUnit\NumberUnit::$INT = new \TrsVendors\Dgm\NumberUnit\NumberUnit(1);
