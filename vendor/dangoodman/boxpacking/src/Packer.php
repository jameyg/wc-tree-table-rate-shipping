<?php
namespace TrsVendors\BoxPacking;


class Packer
{
    public function __construct($precision = 1, $checkGrossVolume = true)
    {
        $this->precision = $precision;
        $this->checkGrossVolume = $checkGrossVolume;
    }

    public function canPack($box, $items)
    {
        $box = self::prepareBox($box, $this->precision, false);
        if (!isset($box)) {
            return false;
        }

        $items = self::prepareBoxes($items, $this->precision, true);
        if (!$items) {
            return true;
        }

        // Check if there are unfittable items. Doesn't guarantee there are no those (ex. 2x2x2 doesn't fit
        // into 3x1x1 but passes the test) but quick and expected to catch most cases in real applications.
        foreach ($items as $item) {
            if ($item[0] > $box[0]) {
                return false;
            }
        }

        if ($this->checkGrossVolume && self::calculateVolume($items) > self::calculateVolume(array($box))) {
            return false;
        }

        return self::place($box, $items);
    }

    private static function place($box, $items)
    {
        $items = self::orderByPerimiter($items);

        $rotate = function($box) {
            return array($box[1], $box[2], $box[0]);
        };

        $sideBoxes = self::projectBox($rotate($box));
        $sideItems = array_map($rotate, $items);

        $projectionsBkp = self::projectBoxes($items);
        $sideProjectionsBkp = self::projectBoxes($sideItems);

        unset($items, $sideItems);


        foreach ($sideBoxes as $sideBox) {

            $projections = $projectionsBkp;
            $sideProjections = $sideProjectionsBkp;

            $sideSkyline = new \TrsVendors\BoxPacking\Skyline($sideBox);

            \TrsVendors\BoxPacking\Utils::fillSkyline(

                $sideProjections,
                $sideSkyline,

                function ($bestItemIndex, $bestSideProjection) use ($sideBox, &$sideProjections, &$projections) {

                    $frontSkyline = new \TrsVendors\BoxPacking\Skyline(array(
                        $sideBox[2],
                        $bestSideProjection[0],
                        $bestSideProjection[1]
                    ));

                    $frontSkyline->insertBox(array(
                        $bestSideProjection[2],
                        $bestSideProjection[0],
                        $bestSideProjection[1]
                    ));

                    unset($projections[$bestItemIndex], $sideProjections[$bestItemIndex]);

                    \TrsVendors\BoxPacking\Utils::fillSkyline(
                        $projections,
                        $frontSkyline,
                        function ($bestItemIndex) use (&$sideProjections, &$projections) {
                            unset($projections[$bestItemIndex], $sideProjections[$bestItemIndex]);
                        }
                    );
                }
            );

            if (empty($sideProjections)) {
                return true;
            }
        }

        return false;
    }

    private static function projectBox(array $box)
    {
        return array_unique(array(
            $box,
            array($box[0], $box[2], $box[1]),
            array($box[1], $box[0], $box[2]),
            array($box[1], $box[2], $box[0]),
            array($box[2], $box[0], $box[1]),
            array($box[2], $box[1], $box[0]),
        ), SORT_REGULAR);
    }

    private static function projectBoxes(array $boxes)
    {
        foreach ($boxes as $idx => $box) {
            $boxes[$idx] = self::projectBox($box);
        }

        return $boxes;
    }

    private static function calculateVolume(array $boxes)
    {
        $volume = 0;

        foreach ($boxes as $box) {
            $volume += array_product($box);
        }

        return $volume;
    }

    private static function orderByPerimiter(array $boxes)
    {
        usort($boxes, function($b1, $b2) {
            $diff = array_sum($b2) - array_sum($b1);
            return ($diff > 0) - ($diff < 0);
        });

        return $boxes;
    }

    private static function prepareBox(array $box, $precision = 0, $roundUp = true)
    {
        // Convert box dimensions to integer with the given precision since
        // the bin packing algo we use can only operate on integers.
        foreach ($box as &$dimension) {
            $dimension *= $precision;
            $dimension = $roundUp ? ceil($dimension) : floor($dimension);
            $dimension = (int)$dimension;
        }

        // Layout boxes uniformly
        rsort($box, SORT_DESC);

        if (end($box) == 0) {
            return null;
        }

        return $box;
    }

    private static function prepareBoxes(array $boxes, $precision = 0, $roundUp = true)
    {
        foreach ($boxes as $key => &$box) {
            $box = self::prepareBox($box, $precision, $roundUp);
            if (!isset($box)) {
                unset($boxes[$key]);
            }
        }

        return $boxes;
    }

    private $precision;
    private $checkGrossVolume;
}