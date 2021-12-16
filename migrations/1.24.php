<?php
namespace Trs\Migrations;

use TrsVendors\Dgm\Shengine\Migrations\Interfaces\Migrations\IGlobalMigration;


/** @noinspection PhpIllegalPsrClassPathInspection */
/** @noinspection AutoloadingIssuesInspection */
class Migration_1_24 implements IGlobalMigration
{
    public function migrate()
    {
        self::preserveOldBehaviorForExistingInstallations();
    }

    public static function preserveOldBehaviorForExistingInstallations()
    {
        $settings = get_option($option = 'trs_settings', null);
        if (isset($settings)) {
            return;
        }

        update_option($option, [
            'preferCustomPackagePrice' => false,
            'includeNonShippableItems' => false,
        ]);
    }
}

return new Migration_1_24();
