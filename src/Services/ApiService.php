<?php
namespace Trs\Services;

use Exception;
use InvalidArgumentException;
use Trs\Woocommerce\Model\Shipping\Exceptions\MalformedPersistentId;
use Trs\Woocommerce\Model\Shipping\ShippingMethodPersistentId;
use Trs\Woocommerce\ShippingMethod;
use TrsVendors\Dgm\PluginServices\IService;
use TrsVendors\Dgm\WcTools\WcTools;


class ApiService implements IService
{
    const AJAX_ACTION_SHIPPING_METHOD = 'trs_shipping_method';
    const AJAX_ACTION_CONFIG_UPDATE = 'trs_config_update';

    const WP_NONCE_ACTION = 'woocommerce-settings';


    public function install()
    {
        add_action('wp_ajax_' . self::AJAX_ACTION_SHIPPING_METHOD, array(__CLASS__, 'shippingMethod'));
        add_action('wp_ajax_' . self::AJAX_ACTION_CONFIG_UPDATE, array(__CLASS__, 'configUpdate'));
    }

    /**
     * @param string $method
     * @param string[] $params
     * @return string
     */
    public static function url($method, array $params = array())
    {
        $params['action'] = $method;
        return admin_url('admin-ajax.php?' . http_build_query($params, '', '&'));
    }

    public static function shippingMethod()
    {
        self::checkUserPermissions();
        self::checkNonce(@$_POST['_wpnonce']);

        $id = @$_GET['id'];
        $enable = isset($_POST['enable']) ? (bool)(int)$_POST['enable'] : true;

        if (!isset($id)) {
            self::respond(400, 'No shipping method id provided.');
        }

        /** @var ShippingMethodPersistentId $id */
        try {
            $id = ShippingMethodPersistentId::unserialize($id);
        } catch (MalformedPersistentId $e) {
            self::respond(400, $e->getMessage());
        }


        /** @noinspection PhpUnusedLocalVariableInspection */
        $updated = false;

        if (!$id->global) {

            global $wpdb;

            $updatedRows = $wpdb->update(
                "{$wpdb->prefix}woocommerce_shipping_zone_methods",
                array('is_enabled' => (int)$enable),
                array('instance_id' => $id->id)
            );

            if ($updatedRows === false) {
                self::respond(500, "An error occurred while updating shipping method: {$wpdb->last_error}.");
            }

            $updated = $updatedRows > 0;

        } else {

            $methods = WC()->shipping()->load_shipping_methods();

            /** @var \WC_Shipping_Method $method */
            $method = @$methods[$id->id];
            if (!isset($method)) {
                self::respond(404, "A shipping method with id '{$id}' not found.");
            }

            $method->init_settings();
            if (!isset($method->settings['enabled'])) {
                self::respond(500,
                    "Unsupported shipping method settings structure. " .
                    "Try to " . ($enable ? 'enable' : 'disable') . " the method manually."
                );
            }

            $method->settings['enabled'] = WcTools::bool2YesNo($enable);

            $optionKey = (
                method_exists($method, 'get_option_key')
                    ? $method->get_option_key()
                    : $method->plugin_id . $method->id . '_settings'
            );

            $updated = update_option(
                $optionKey,
                apply_filters('woocommerce_settings_api_sanitized_fields_' . $method->id, $method->settings)
            );
        }

        if ($updated) {
            WcTools::purgeShippingCache();
        }

        self::respond(200, 'OK');
    }

    public static function configUpdate()
    {
        self::checkUserPermissions();

        $instanceId = null;
        if (!isset($_GET['instance_id']) || !is_numeric($instanceId=$_GET['instance_id'])) {
            self::respond(400, 'Missing or non-numeric shipping method instance ID.');
        }

        $request = file_get_contents('php://input');
        if (!is_string($request)) {
            self::respond(500, 'Couldn\'t read request body.');
        }
        if ($request === '') {
            self::respond(400, 'Empty request body.');
        }

        $request = json_decode($request, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $code = json_last_error();
            $msg = function_exists('json_last_error_msg') ? json_last_error_msg() : null;
            self::respond(400, "Couldn't parse request body. JSON parsing error: [{$code}] {$msg}.");
        }

        if (!is_array($request)) {
            self::respond(400, "Malformed request body.");
        }

        self::checkNonce(@$request['_wpnonce']);

        $config = @$request['config'];
        if (!isset($config) || !is_array($config)) {
            self::respond(400, "Missing or invalid configuration data.");
        }

        try {
            $method = new ShippingMethod($instanceId);
            $method->updateConfig($config);
        } catch (Exception $e) {
            self::respond(
                $e instanceof InvalidArgumentException ? 400 : 500,
                "An error occurred during configuration update. {$e->getMessage()}"
            );
        }

        self::respond(200, wp_create_nonce(self::WP_NONCE_ACTION));
    }

    private static function respond($code, $message)
    {
        wp_die(
            $message,
            null, array('response' => $code)
        );
    }

    private static function checkNonce($nonce)
    {
        if (!wp_verify_nonce($nonce, self::WP_NONCE_ACTION)) {
            self::respond(401, "Nonce validation failed. Please refresh the page and try again.");
        }
    }

    private static function checkUserPermissions()
    {
        if (!current_user_can('manage_woocommerce')) {
            self::respond(403, "You have no permissions to perform the action.");
        }
    }
}