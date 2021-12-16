<?php
namespace Trs\Mapping\Mappers;

use Exception;
use TrsVendors\Dgm\Shengine\Attributes\AbstractAttribute;
use TrsVendors\Dgm\Shengine\Attributes\PriceAttribute;
use TrsVendors\Dgm\Shengine\Attributes\TermsAttribute;
use TrsVendors\Dgm\Shengine\Interfaces\IItemAggregatables;
use Trs\Factory\FactoryTools;
use Trs\Mapping\Interfaces\IMapper;
use Trs\Mapping\Interfaces\IMappingContext;
use Trs\Mapping\Interfaces\IReader;


class AttributeMapper extends AbstractMapper implements IMapper
{
    public function read($data, IReader $reader, IMappingContext $context = null)
    {
        if (!is_array($data)) {
            $data = array('attribute' => $data);
        }

        $attribute = null;
        switch ($attributeName = $data['attribute']) {
            
            case 'classes':
            case 'tags':
            case 'categories':
                
                static $terms = array(
                    'classes' => IItemAggregatables::TAXONOMY_SHIPPING_CLASS,
                    'tags' => IItemAggregatables::TAXONOMY_TAG,
                    'categories' => IItemAggregatables::TAXONOMY_CATEGORY,
                );
                
                $attribute = new TermsAttribute($terms[$attributeName]);
                
                break;

            case 'price':
                $attribute = new PriceAttribute((int)@$data['price_kind']);
                break;
            
            case 'product':
            case 'product_variation':
            case 'item':
            case 'weight':
            case 'volume':
            case 'count':
            case 'destination':
            case 'item_dimensions':
            case 'customer_roles':
            case 'coupons':
                $class = FactoryTools::resolveObjectIdToClass($attributeName, 'Attribute', AbstractAttribute::className());
                $attribute = new $class();
                break;
        }

        if (!isset($attribute)) {
            throw new Exception("Unknown attribute '{$attributeName}'");
        }

        return $attribute;
    }
}