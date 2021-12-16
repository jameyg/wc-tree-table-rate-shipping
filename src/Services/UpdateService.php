<?php
namespace Trs\Services;

use Puc_v4_Factory;
use Puc_v4p7_Plugin_UpdateChecker;
use Trs\PluginMeta;
use TrsVendors\Dgm\PluginServices\IService;
use WP_Error;


class UpdateService implements IService
{
    public function __construct(PluginMeta $pluginMeta)
    {
        $this->pluginMeta = $pluginMeta;
    }

    public function install()
    {
        $meta = $this->pluginMeta;

        $apiUpdatesEndpoint = $meta->getApiUpdatesEndpoint();
        $entryFile = $meta->getEntryFile();

        /** @var Puc_v4p7_Plugin_UpdateChecker $updateChecker */
        $updateChecker = Puc_v4_Factory::buildUpdateChecker($apiUpdatesEndpoint, $entryFile);

        $updateChecker->addQueryArgFilter(function($queryArgs) use($meta) {
            $queryArgs['license'] = $meta->getLicense();
            return $queryArgs;
        });

        add_filter('upgrader_pre_download', function($response, $downloadUrl) use($apiUpdatesEndpoint, $entryFile) {

            if (strpos($downloadUrl, $apiUpdatesEndpoint) !== false) {

                if ($response === false) {
                    $downloadUrl .= (strpos($downloadUrl, '?') === false ? '?' : '&') . 'check=1';
                    $checkResponse = wp_safe_remote_get($downloadUrl);
                    if (is_array($checkResponse) && @$checkResponse['body'] && $checkResponse['body'] !== 'OK') {
                        $response = new WP_Error('download_failed', '', $checkResponse['body']);
                    }
                }

                if ($response === false) {
                    if (file_exists(dirname($entryFile).'/.git') || file_exists(dirname($entryFile).'/.idea')) {
                        $response = new WP_Error('download_failed', '', 'Development plugin copy protected from erasing during update.');
                    }
                }
            }

            return $response;

        }, 10, 3);
    }

    private $pluginMeta;
}