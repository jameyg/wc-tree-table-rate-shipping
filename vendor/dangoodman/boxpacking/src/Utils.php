<?php
namespace TrsVendors\BoxPacking;


class Utils
{
    public static function fillSkyline(&$projections, \TrsVendors\BoxPacking\Skyline $skyline, $insertCallback)
    {
        while ($projections && !$skyline->isFull()) {

            list($bestItemIndex, $itemProjection) = self::findBestFitItem($projections, $skyline);

            if (isset($bestItemIndex)) {
                $skyline->insertBox($itemProjection);
                call_user_func(\TrsVendors_CCR::kallable($insertCallback), $bestItemIndex, $itemProjection);
            } else {
                $skyline->fillCurrentGap();
            }
        }
    }

    private static function findBestFitItem($projections, \TrsVendors\BoxPacking\Skyline $skyline)
    {
        $bestItemIndex = null;
        $bestItemProjection = null;

        $bestFitnessValue = -1;
        foreach ($projections as $idx => $list) {

            foreach ($list as $projection) {

                $fitness = $skyline->getFitnessValue($projection);

                if ($fitness > $bestFitnessValue) {

                    $bestFitnessValue = $fitness;
                    $bestItemIndex = $idx;
                    $bestItemProjection = $projection;

                    if ($bestFitnessValue == \TrsVendors\BoxPacking\Skyline::MAX_FITNESS_VALUE) {
                        break;
                    }
                }
            }

            if ($bestFitnessValue == \TrsVendors\BoxPacking\Skyline::MAX_FITNESS_VALUE) {
                break;
            }
        }

        return array($bestItemIndex, $bestItemProjection);
    }
}