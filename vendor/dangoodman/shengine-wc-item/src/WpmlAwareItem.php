<?php
namespace TrsVendors\Dgm\Shengine\Woocommerce\Model\Item;

use Deferred\Deferred;


class WpmlAwareItem extends \TrsVendors\Dgm\Shengine\Woocommerce\Model\Item\WoocommerceItem
{
    public function getTerms($taxonomy)
    {
        global $sitepress;

        if (isset($sitepress)) {

            $lang = $sitepress->get_current_language();
            $restoreLanguage = new \TrsVendors\Deferred\Deferred(function() use($sitepress, $lang) {
                $sitepress->switch_lang($lang);
            });

            $sitepress->switch_lang($sitepress->get_default_language());
        }

        return parent::getTerms($taxonomy);
    }

}