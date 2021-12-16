<?php

class TrsVendors_DgmWpDismissibleNotices
{
    public static function className()
    {
        /** @noinspection ClassConstantCanBeUsedInspection <class>::class supported since PHP 5.5 */
        return get_called_class();
    }

    public static function init()
    {
        add_action('admin_enqueue_scripts', array(__CLASS__, '_enqueueScripts'));
        add_action('wp_ajax_dgm_dismiss_admin_notice', array(__CLASS__, '_dismissNotice'));
    }

    public static function isNoticeDismissed($noticeId)
    {
        return (bool)get_site_transient($noticeId);
    }

    public static function _enqueueScripts()
    {
        wp_enqueue_script(
            'dgm-dismissible-notices',
            plugins_url('dismiss-notice.js', __FILE__),
            array('jquery', 'common'),
            false,
            true
        );

        wp_localize_script(
            'dgm-dismissible-notices',
            'dgm_dismissible_notice',
            array(
                'nonce' => wp_create_nonce('dgm-dismissible-notice'),
            )
        );
    }

    public static function _dismissNotice()
    {
        $id = sanitize_text_field($_POST['id']);
        check_ajax_referer('dgm-dismissible-notice', 'nonce');
        set_site_transient($id, true);
        /** @noinspection ForgottenDebugOutputInspection */
        wp_die();
    }
}
