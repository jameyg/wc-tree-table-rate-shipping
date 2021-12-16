<?php
namespace Trs;

use Trs\Services\ApiService;
use Trs\Woocommerce\ShippingMethod;


class EnqueueAssets
{
    /**
     * @param ShippingMethod $shippingMethod
     * @param PluginMeta $meta
     */
    public function __construct(ShippingMethod $shippingMethod, PluginMeta $meta)
    {
        $this->shippingMethod = $shippingMethod;
        $this->meta = $meta;
    }

    /**
     * @return void
     */
    public function __invoke()
    {
        $this->deregisterHandles();
        $this->includeStyles();
        $this->includeScripts();
        $this->protectFromForeignSelect2Versions();
    }


    private $shippingMethod;
    private $meta;

    private function deregisterHandles()
    {
        $handles = array(
            'select2',
            'ign_voucher_select2_js',
            'ign_voucher_select2_css'
        );

        foreach ($handles as $handle) {
            wp_dequeue_style($handle);
            wp_dequeue_script($handle);
            wp_deregister_style($handle);
            wp_deregister_script($handle);
        }
    }

    private function includeStyles()
    {
        $adminCssBasename = 'admin.css';
        if (version_compare(get_bloginfo('version'), '5.3', '<')) {
            $adminCssBasename = 'admin-pre53.css';
        }
        wp_enqueue_style(
            'trs-admin-css',
            $this->meta->getAssetUrl("trs/css/{$adminCssBasename}")
        );

        wp_enqueue_style(
            'trs-select2-css',
            $this->meta->getAssetUrl('select2/select2.css')
        );
    }

    private function includeScripts()
    {
        wp_register_script(
            'trs-admin-js',
            $this->meta->getAssetUrl('client.js'),
            array(
                'jquery',
                'jquery-color',
                'jquery-ui-sortable',
                'jquery-form',
                'underscore',
            )
        );

        wp_localize_script('trs-admin-js', 'trs_admin_js_options', array(
            'config_update_url' => ApiService::url(
                ApiService::AJAX_ACTION_CONFIG_UPDATE,
                array('instance_id' => $this->shippingMethod->instance_id)
            ),
            'form_fields_prefix' => $this->shippingMethod->get_field_key(null),
        ));

        wp_enqueue_script('trs-admin-js');

        // Force dot decimal point for client-side validation.
        wp_localize_script('trs-admin-js', 'trs_admin_js_woocommerce_admin_overrides', [
            'i18n_decimal_error' => sprintf(__('Please enter with one decimal point (%s) without thousand separators.', 'woocommerce'), '.'),
            'decimal_point'      => '.',
        ]);
    }

    private function protectFromForeignSelect2Versions()
    {
        $assetUrl = $this->meta->getAssetUrl();

        add_action('wp_print_scripts', function () use ($assetUrl) {

            global $wp_scripts;

            /** @var \_WP_Dependency $dep */
            foreach ($wp_scripts->registered as $dep) {
                if (($src = (string)@$dep->src) !== '') {
                    if (substr_compare($src, $assetUrl, 0, strlen($assetUrl)) !== 0 &&
                        preg_match('#(/|\\\\)(select2|selectWoo)(\.full)?(\.min)?\.(js|css)#i', $src)
                    ) {
                        $wp_scripts->remove($dep->handle);
                    }
                }
            }
        });
    }
}