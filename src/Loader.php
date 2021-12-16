<?php
namespace Trs;

use tree_table_rate;
use Trs\Migrations\ConfigStorage;
use Trs\Services\ApiService;
use Trs\Services\IisCompatService;
use Trs\Services\ServiceInstaller;
use Trs\Services\StatsService;
use Trs\Services\UpdateService;
use Trs\Woocommerce\ShippingMethod;
use TrsVendors\Dgm\Shengine\Migrations\MigrationService;
use TrsVendors\Dgm\Shengine\Migrations\Storage\WordpressOptions;
use WC_Cache_Helper;
use WC_Shipping_Zones;


class Loader
{
    public function __construct(PluginMeta $pluginMeta)
    {
        $this->pluginMeta = $pluginMeta;
    }
    
    public function bootstrap()
    {
        $this->installServices();
        add_filter('woocommerce_shipping_methods', array($this, '_registerShippingMethods'));
        add_action('init', array($this, '_init'), PHP_INT_MAX);
    }

    public function _init()
    {
        add_filter("plugin_action_links_{$this->pluginMeta->getPluginBasename()}", array($this, '_injectSettingsLink'));
        $this->fixIncorrectHidingOfShippingSectionWhenNoShippingZoneMethodsDefined();

        // On the plugin settings page only
        if (($method = self::editingShippingMethod()) !== null && !$method->showGlobalSettingsStub()) {

            $enqueueAssets = new EnqueueAssets($method, $this->pluginMeta);
            add_action('admin_enqueue_scripts', $enqueueAssets, PHP_INT_MAX);

            self::removeConflictingScripts();
        }
    }

    public function _registerShippingMethods($shippingMethods)
    {
        static $shippingMethod;

        if (!isset($shippingMethod)) {
            $shippingMethod = new tree_table_rate();
        }

        $shippingMethods[tree_table_rate::className()] = $shippingMethod;

        return $shippingMethods;
    }

    public function _injectSettingsLink($links)
    {
        $base = 'admin.php?page=wc-settings&tab=shipping';

        $customLinks = array(
            $base => 'Shipping zones',
            $base . '&section=' . rawurlencode(tree_table_rate::className()) => 'Global shipping rules',
        );

        foreach ($customLinks as $url => &$text) {
            $text = '<a href="'.esc_html($url).'">'.esc_html($text).'</a>';
        }
        unset($text);

        array_splice($links, 0, 0, $customLinks);

        return $links;
    }

    /**
     * @return ShippingMethod|null
     */
    private static function editingShippingMethod()
    {
        if (isset($_GET['section']) && $_GET['section'] === tree_table_rate::className()) {
            return new ShippingMethod(0);
        }

        $instanceId = isset($_REQUEST['instance_id']) ? $_REQUEST['instance_id'] : null;
        if (isset($instanceId) &&
            class_exists('\\WC_Shipping_Zones') &&
            ($method = WC_Shipping_Zones::get_shipping_method($instanceId)) &&
            ($method instanceof tree_table_rate)) {

            return $method;
        }

        return null;
    }

    private static function removeConflictingScripts()
    {
        // Compatibility with Virtue theme 3.2.2 (https://wordpress.org/themes/virtue/)
        remove_action('admin_enqueue_scripts', 'kadence_admin_scripts');

        // Compatibility with Woocommerce Product Tab Pro 1.8.0 (http://codecanyon.net/item/woocommerce-tabs-pro-extra-tabs-for-product-page/8218941)
        remove_action('admin_print_footer_scripts', '_hc_tinymce_footer_scripts');
    }

    private static function createMigrationService(PluginMeta $meta)
    {
        global $wpdb;

        $options = new WordpressOptions($wpdb);

        return new MigrationService(
            $meta->getVersion(),
            $options->bind('trs_option'),
            $meta->getMigrationsPath(),
            new ConfigStorage('woocommerce\\_tree\\_table\\_rate\\_%settings', $options)
        );
    }


    private $pluginMeta;

    private function fixIncorrectHidingOfShippingSectionWhenNoShippingZoneMethodsDefined()
    {
        $trv = WC_Cache_Helper::get_transient_version('shipping');

        // WC before 3.6.0
        add_filter(
            'transient_wc_shipping_method_count_1_' . $trv,
            function($count) {
                return min(1, $count);
            },
            10, 2
        );

        // WC 3.6.0+
        add_filter(

            'transient_wc_shipping_method_count_legacy',

            function($value) use($trv) {
                static $running = false;

                if ($running) return $value;
                $running = true;

                if (!isset($value['value'], $value['version']) ||
                    $value['value'] == 0 ||
                    $value['version'] !== $trv) {

                    $count = max(1, wc_get_shipping_method_count(true));
                    $value['value'] = $count;
                    $value['version'] = $trv;
                }

                $running = false;

                return $value;
            },
            PHP_INT_MAX
        );
    }

    private function installServices()
    {
        $services = array(
            new IisCompatService($this->pluginMeta),
            new UpdateService($this->pluginMeta),
            new StatsService($this->pluginMeta),
            self::createMigrationService($this->pluginMeta),
            new ApiService(),
        );

        $installer = new ServiceInstaller();
        foreach ($services as $service) {
            $installer->installIfReady($service);
        }
    }
}