<?php

namespace Ceg\PriceRender\Model;

/**
 * Currency model
 *
 * @api
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class CurrencyCustom extends \Magento\Directory\Model\Currency
{
    /**
     * @param float $price
     * @param array $options
     * @return string
     */
    public function formatTxt($price, $options = [])
    {
        if (!is_numeric($price)) {
            $price = $this->_localeFormat->getNumber($price);
        }
        /**
         * Fix problem with 12 000 000, 1 200 000
         *
         * %f - the argument is treated as a float, and presented as a floating-point number (locale aware).
         * %F - the argument is treated as a float, and presented as a floating-point number (non-locale aware).
         */
        $price = sprintf("%F", $price);
        $priceparts = explode(".", $price);
        $priceEnt = floor($price);
        $precision=2;
        foreach ($options as $key => $option) {
            if ($key == 'precision') {
                $precision=$option;
            }
        }

        $priceDec = substr($priceparts[1], 0, $precision);
        $currencySymbol="<span class='currencySymbol'>".
        $this->_localeCurrency->getCurrency($this->getCode())->getSymbol().
        "</span>";
        $entPrice="<span class='entPrice'>".number_format($priceEnt, 0)."</span>";
        $cents="<span class='cents'>".$priceDec."</span>";
        return $currencySymbol.$entPrice.$cents;
    }

}
