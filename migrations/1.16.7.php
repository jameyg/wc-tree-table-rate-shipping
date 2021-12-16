<?php
namespace Trs\Migrations;

use TrsVendors\Dgm\Shengine\Migrations\Interfaces\Migrations\IRuleMigration;


/** @noinspection PhpIllegalPsrClassPathInspection */
/** @noinspection AutoloadingIssuesInspection */
class Migration_1_16_7 implements IRuleMigration
{
    public function migrateRule($rule)
    {
        self::updatePostcodeRangeSeparator($rule);
        return $rule;
    }

    public static function updatePostcodeRangeSeparator(array &$rule)
    {
        foreach ($rule['conditions']['list'] as &$condition) {
            if (@$condition['condition'] === 'destination' && !empty($condition['value']) && is_array($condition['value'])) {
                foreach ($condition['value'] as &$destination) {
                    @list($location, $postcodes) = explode("/zip:", $destination);
                    if (isset($postcodes)) {
                        $destination = $location . "/zip:" . str_replace("-", "...", $postcodes);
                    }
                }
            }
        }
    }
}

return new Migration_1_16_7();
