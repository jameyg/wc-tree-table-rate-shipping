<?php
namespace Trs\Woocommerce;

use Exception;
use InvalidArgumentException;
use Trs\Factory\Registries\GlobalRegistry;
use Trs\Log;
use Trs\Mapping\Interfaces\IReader;
use Trs\Settings;
use TrsVendors\Dgm\Shengine\Interfaces\IRule;
use TrsVendors\Dgm\Shengine\Units;
use TrsVendors\Dgm\Shengine\Woocommerce\Converters\PackageConverter;
use TrsVendors\Dgm\Shengine\Woocommerce\Converters\RateConverter;
use TrsVendors\Dgm\WcTools\WcTools;
use WC_Shipping_Method;


class ShippingMethod extends WC_Shipping_Method
{
    /**
     * @noinspection PhpMissingParentConstructorInspection
     * @noinspection MagicMethodsValidityInspection
     */
    public function __construct($instance_id = 0)
    {
        $this->id = 'tree_table_rate';
        $this->title = $this->method_title = 'Tree Table Rate';
        $this->instance_id = absint($instance_id);

        $this->supports = array(
            'settings',
            'shipping-zones',
            'instance-settings',
            'global-instance',
        );

        $this->init();
    }

    public function is_available($package)
    {
        // This fixes the issue with the global TRS method not being triggered by WooCommerce for customers having no location set.
        // It also works fine for instanced TRS methods.
        return $this->is_enabled();
    }

    public function calculate_shipping($_package = array())
    {
        $globals = self::initGlobalRegistry(true);

        $rule = $this->loadRule($globals->reader);
        if (!isset($rule)) {
            return;
        }

        $settings = Settings::instance();

        $package = PackageConverter::fromWoocommerceToCore2(
            $_package,
            WC()->cart,
            $settings->preferCustomPackagePrice,
            $settings->includeNonShippableItems
        );
        $rates = $globals->processor->process(array($rule), $package);

        $_rates = RateConverter::fromCoreToWoocommerce(
            $rates,
            $this->title,
            join(':', array_filter(array($this->id, @$this->instance_id))).':',
            true
        );

        foreach ($_rates as $_rate) {
            $this->add_rate($_rate);
        }
    }

    public function init()
    {
        $this->init_form_fields();
        $this->init_settings();

        $this->enabled = $this->get_option('enabled');
        $this->tax_status = $this->get_option('tax_status');
        $this->title = $this->get_option('label') ?: 'Tree Table Rate';
    }

    public function init_form_fields()
    {
        $meta = array(
            'enabled'    => array(
                'title'   => 'Enable/Disable',
                'type'    => 'checkbox',
                'label'   => 'Enable this shipping method',
                'default' => 'yes',
            ),
            'tax_status' => array(
                'title' 		=> 'Tax Status',
                'type' 			=> 'select',
                'class'         => 'wc-enhanced-select',
                'default' 		=> 'taxable',
                'options'		=> array(
                    'taxable' 	=> 'Taxable',
                    'none' 		=> 'Not taxable',
                ),
            ),
        );

        $rules = array(
            'rule' => array(
                'type' => 'rule',
                'default' => null,
            ),
        );

        /** @noinspection AdditionOperationOnArraysInspection */
        $this->form_fields = $meta + $rules;

        /** @noinspection AdditionOperationOnArraysInspection */
        $this->instance_form_fields =
            $meta +
            array(
                'label' => array(
                    'title' => 'Label',
                    'type' => 'text',
                    'default' => '',
                    'placeholder' => 'Label in the shipping zone table',
                ),
            ) +
            $rules;

        unset($this->instance_form_fields['enabled']);
    }

    public function generate_rule_html()
    {
        ob_start();
        ?>
                <?php echo $this->generate_hidden_html('rule', array()) ?>
            </table>

            <?php
                $this->showGlobalSettingsStub()
                    ? include(__DIR__.'/../../tpl/global.php')
                    : include(__DIR__.'/../../tpl.php');
            ?>

            <table>
        <?php
        return ob_get_clean();
    }

    public function generate_hidden_html($field, $definition)
    {
        $definition['type'] = 'hidden';
        $html = $this->generate_text_html($field, $definition);
        $html = preg_replace('/'.preg_quote('<tr', '/').'/', '<tr style="display:none"', $html, 1);
        return $html;
    }

    public function admin_options()
    {
        $methodTitleBkp = $this->method_title;
        $this->method_title .= ' Shipping';

        try {

            parent::admin_options();

        } catch (Exception $e){
            $this->method_title = $methodTitleBkp;
            /** @noinspection PhpUnhandledExceptionInspection */
            throw $e;
        }

        $this->method_title = $methodTitleBkp;
    }

    /**
     * @param mixed $config
     * @throws InvalidArgumentException On validation errors.
     */
    public function updateConfig($config)
    {
        $optionKey = $this->instance_id ? $this->get_instance_option_key() : $this->get_option_key();

        if (!is_array($config)) {
            throw new InvalidArgumentException('$config must be an array.');
        }

        if ($this->instance_id) {
            unset($config['enabled']);
        } else {
            $config['enabled'] = WcTools::bool2YesNo(isset($config['enabled']) ? $config['enabled'] : true);
        }

        $rule = null;
        if (!isset($config['rule']) || !is_array($rule = $config['rule'])) {
            throw new InvalidArgumentException('$config[\'rule\'] must be an array.');
        }

        $globals = self::initGlobalRegistry(false);

        try {
            $globals->reader->read('rule', $rule);
        } catch (Exception $e) {
            throw new InvalidArgumentException("Configuration validation failed. {$e->getMessage()}", 0, $e);
        }

        $config['rule'] = json_encode($config['rule']);
        $updated = update_option($optionKey, $config);
        if ($updated) {
            WcTools::purgeShippingCache();
        }
    }

    public function get_option($key, $empty_value = null) {

        $result = $empty_value;

        if (empty($this->instance_id) && version_compare(WC()->version, '2.6', '>=')) {

            add_filter(
                $filter = "woocommerce_shipping_instance_form_fields_{$this->id}",
                $stub = static function() { return array(); }
            );

            $exception = null;
            try {
                $result = parent::get_option($key, $empty_value);
            }
            catch (Exception $e) {
                $exception = $e;
            }

            remove_filter($filter, $stub);

            if (isset($exception)) {
                /** @noinspection PhpUnhandledExceptionInspection */
                throw $exception;
            }
        } else {
            $result = parent::get_option($key, $empty_value);
        }

        return $result;
    }

    public function get_instance_id()
    {
        // A hack to prevent Woocommerce 2.6 from skipping global method instance
        // rates in WC_Shipping::calculate_shipping_for_package()
        return (method_exists('parent', 'get_instance_id') ? parent::get_instance_id() : $this->instance_id) ?: -1;
    }

    public function showGlobalSettingsStub()
    {
        if ($this->instance_id || isset($_GET['trs_global'])) {
            return false;
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        $config = $this->get_option('rule');
        if (!$config) {
            return true;
        }

        $config = json_decode($config, true);
        if (!$config) {
            return false;
        }

        return isset($config['children']) && !$config['children'];
    }

    /**
     * @return IRule|null
     */
    private function loadRule(IReader $reader)
    {
        try {
            $json = $this->get_option('rule');
            if (in_array($json, [null, '', false], true)) {
                return null;
            }
            if (!is_string($json)) {
                $this->logError(
                    'failed to load config: not a string',
                    ['type_received' => gettype($json), 'option_value' => $json]
                );
                return null;
            }

            $config = json_decode($json, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->logError(
                    'failed to load config: json_decode failed',
                    ['json_decode_error' => json_last_error_msg(), 'config_json' => $json]
                );
                return null;
            }
            if (!isset($config)) {
                return null;
            }
            if (!is_array($config)) {
                $this->logError(
                    'failed to load config: config after json_decode is not an array',
                    ['type_received' => gettype($json), 'config_json' => $json]
                );
                return null;
            }
            if (empty($config['children'])) {
                return null;
            }

            return $reader->read('rule', $config);
        }
        catch (Exception $e) {
            $this->logError('failed to load config: an exception ocurred', ['exception' => $e->getMessage()]);
            return null;
        }
    }

    private function logError($msg, array $context = [])
    {
        $context['instance_id'] = $this->instance_id;
        Log::error($msg, $context);
    }

    private static function initGlobalRegistry($lazy = true)
    {
        $settings = Units::fromPrecisions(
            pow(10, wc_get_price_decimals()),
            1000,
            1000
        );
        
        $globalRegistry = new GlobalRegistry($settings, $lazy);

        $globalRegistry->mappers->register('shipping_method_calculator', static function() {
            return new ShippingMethodCalculatorMapper(new ShippingMethodLoader());
        });

        return $globalRegistry;
    }
}
