<?php
namespace TrsVendors\Dgm\Shengine\Woocommerce\Model\Item;

use Dgm\Shengine\Model\Item;
use RuntimeException;
use Dgm\Arrays\Arrays;
use WC_Product;


class WoocommerceItem extends \TrsVendors\Dgm\Shengine\Model\Item
{
    public function getTerms($taxonomy)
    {
        $terms = null;

        if (isset(self::$map[$taxonomy])) {
            $taxonomy = self::$map[$taxonomy];
        }

        $taxonomy = (string)$taxonomy;

        $productId = $this->getProductId();
        $variationId = $this->getProductVariationId();

        $termsResult = false;

        // Find a term if the taxonomy is related to some product attribute used in this product variation.
        // That requires a special handling since Woocommerce stores variation attributes in a special way.
        if ($termsResult === false && isset($variationId) && 
            in_array($taxonomy, wc_get_attribute_taxonomy_names(), true) &&
            ($attributeTermSlug = @$this->variationAttributes[$taxonomy]) !== null) {

            $termsResult = array();

            foreach (get_the_terms($productId, $taxonomy) as $term) {

                if ($term->slug === $attributeTermSlug) {
                    $termsResult[] = $term;
                    break;
                }
            }
        }

        if ($termsResult === false && isset($variationId)) {
            $termsResult = get_the_terms($variationId, $taxonomy);
        }

        if ($termsResult === false) {
            $termsResult = get_the_terms($productId, $taxonomy);
        }

        if ($termsResult === false) {
            $termsResult = array();
        }

        if (is_wp_error($termsResult)) {
            throw new RuntimeException($termsResult->get_error_message());
        }

        $terms = \TrsVendors\Dgm\Arrays\Arrays::map($termsResult, function ($term) {
            return $term->term_id;
        });

        return $terms;
    }

    public function setTerms($taxonomy, array $terms = null)
    {
        throw new \BadMethodCallException("Setting terms on woocommerce item is not supported");
    }
    
    public function getOriginalProductObject()
    {
        return $this->originalProductObject;    
    }

    public function setOriginalProductObject(WC_Product $product)
    {
        $this->originalProductObject = $product;
    }

    public function setVariationAttributes(array $attributes)
    {
        $this->variationAttributes = $attributes;
    }

    /** @var WC_Product */
    private $originalProductObject;
    private $variationAttributes;

    static private $map = array(
        self::TAXONOMY_SHIPPING_CLASS => 'product_shipping_class',
        self::TAXONOMY_TAG => 'product_tag',
        self::TAXONOMY_CATEGORY => 'product_cat',
    );
}