<?php
/**
 * Plugin Name: WooCommerce Tree Table Rate Shipping
 * Description: Ultimate shipping plugin for WooCommerce
 * Version: 1.26.10
 * Author: tablerateshipping.com
 * Plugin URI: https://tablerateshipping.com
 * Author URI: https://tablerateshipping.com
 * Requires PHP: 5.6
 * Requires at least: 4.0
 * Tested up to: 5.8
 * WC requires at least: 3.2
 * WC tested up to: 5.7
 */

define('TRS_ENTRY_FILE', __FILE__);

if (!class_exists('TrsVendors_DgmWpPluginBootstrapGuard', false)) {
    require_once(__DIR__ .'/vendor/dangoodman/wp-plugin-bootstrap-guard/DgmWpPluginBootstrapGuard.php');
}

TrsVendors_DgmWpPluginBootstrapGuard::checkPrerequisitesAndBootstrap(
    'Tree Table Rate Shipping', '5.6', '4.0', '3.2', __DIR__ .'/bootstrap.php');
