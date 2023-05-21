<?php
namespace Ceg\Impo\Plugin\Product;

use Magento\Catalog\Ui\DataProvider\CatalogEavValidationRules;
use Closure;
use Magento\Catalog\Api\Data\ProductAttributeInterface;

class ValidationRules
{
    public function aroundBuild(
        CatalogEavValidationRules $rulesObject,
        Closure $proceed,
        ProductAttributeInterface $attribute,
        array $data
    ){
        $rules = $proceed($attribute,$data);
        if($attribute->getAttributeCode() == 'qtyinbox'){
            $validationClasses = explode(' ', $attribute->getFrontendClass());
            foreach ($validationClasses as $class) {
                $rules[$class] = true;
            }
        }
        return $rules;
    }
}
