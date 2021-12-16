<?php
namespace Trs\Services;

use Trs\PluginMeta;
use TrsVendors\Dgm\PluginServices\IService;
use TrsVendors\Dgm\PluginServices\IServiceReady;


class IisCompatService implements IService, IServiceReady
{
    public function __construct(PluginMeta $pluginMeta)
    {
        $this->pluginMeta = $pluginMeta;
    }

    public function install()
    {
        $result = wp_remote_get($this->pluginMeta->getAssetUrl('iis-test.txt'));

        // In case we can't access the test file on IIS for ANY reason we'd better remove .htaccess files.
        if (is_wp_error($result) || isset($result['response']['code']) && $result['response']['code'] >= 500) {
            unlink($this->pluginMeta->getAssetsPath('.htaccess'));
            unlink($this->pluginMeta->getPath('.htaccess'));
            $this->isPrepatedToIis(true);
        }
    }

    public function ready()
    {
        return self::isIis() && !$this->isPrepatedToIis();
    }


    private $pluginMeta;

    private function isPrepatedToIis($prepared = null)
    {
        $flagFile = $this->pluginMeta->getPath('.iis');

        if (func_num_args()) {
            if ($prepared) {
                touch($flagFile);
            } else {
                unlink($flagFile);
            }
        }

        return file_exists($flagFile);
    }

    static private function isIis()
    {
        global $is_IIS;

        $iis = null;
        if (isset($is_IIS)) {
            $iis = $is_IIS;
        } else {
            $iis = strpos($_SERVER["SERVER_SOFTWARE"], "Microsoft-IIS") !== false;
        }

        return $iis;
    }
}