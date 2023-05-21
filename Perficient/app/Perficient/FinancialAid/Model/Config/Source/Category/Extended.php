<?php
namespace Perficient\FinancialAid\Model\Config\Source\Category;

use Perficient\FinancialAid\Model\Config\Source\CategoryList;

/**
 * Class Extended
 * @package Perficient\FinancialAid\Model\Config\Source\Category
 */
class Extended extends CategoryList
{
    public function toOptionArray($boolean = 1)
    {
        $allowedSchools = explode(",", $this->config->getAllowedSchools());
        $options=[];
        foreach ($allowedSchools as $key => $shcool) {
            $options[$key]['value']=$shcool;
            $options[$key]['label']=$shcool;
        }
        $allOption = $options;

        return array_merge($allOption, parent::toOptionArray());
    }
}
