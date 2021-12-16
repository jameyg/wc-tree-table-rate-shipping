<style>
    #mainform .form-table, #mainform p.submit {
        display: none;
    }
    .trs-global-method-notice {
        max-width: 750px;
        font-size: 15px;
    }
    #trs_proceed_with_global {
        font-size: 15px;
    }
</style>
<br>
<p class="trs-global-method-notice">
    Here reside global shipping rules. They do not depend on the shipping zones. It might be useful to have global shipping
    rules along with ones under shipping zones, or instead of them. However, if you are not sure, start with
    <a href="<?php esc_html(admin_url('admin.php?page=wc-settings&tab=shipping')) ?>">shipping zones</a>. You can find more
    details about that in <a href="<?php esc_html('https://docs.woocommerce.com/document/setting-up-shipping-zones/') ?>"
    >WooCommerce docs</a>.
</p>
<br>
<a class="button-primary" id="trs_proceed_with_global" href="#">Set up global shipping rules</a>
<script>
    (($) => {
        $('#trs_proceed_with_global').attr('href', document.location.href+'&trs_global');
    })(jQuery)
</script>
