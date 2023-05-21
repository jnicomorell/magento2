<?php
declare(strict_types=1);

namespace Ceg\PriceRender\Model;

use Magento\Framework\Locale\Bundle\DataBundle;

class Format extends \Magento\Framework\Locale\Format
{
    private static $defaultNumberSet = 'latn';

    public function getPriceFormat($localeCode = null, $currencyCode = null)
    {
        $currency = $this->getCurrency($currencyCode); 

        $format = $this->getFormat($localeCode);
        //your main changes are gone here.....
        $decimalSymbol = '.';
        $groupSymbol = ',';

        $pos = strpos($format, ';');
        if ($pos !== false) {
            $format = substr($format, 0, $pos);
        }
        $format = preg_replace("/[^0\#\.,]/", "", $format);
        $totalPrecision = 0;
        $decimalPoint = strlen($format);
        if ($decimalPoint !== false) {
            $totalPrecision = strlen($format) - (strrpos($format, '.') + 1);
            $decimalPoint = strpos($format, '.');
        } 

        $requiredPrecision = $totalPrecision;
        $t = substr($format, $decimalPoint);
        $pos = strpos($t, '#');
        if ($pos !== false) {
            $requiredPrecision = strlen($t) - $pos - $totalPrecision;
        }

        $group = strrpos($format, '.');
        if (strrpos($format, ',') !== false) {
            $group = $decimalPoint - strrpos($format, ',') - 1;
        } 

        $integerRequired = strpos($format, '.') - strpos($format, '0');
        $result = [
            'pattern' => $currency->getOutputFormat(),
            'precision' => $totalPrecision,
            'requiredPrecision' => $requiredPrecision,
            'decimalSymbol' => $decimalSymbol,
            'groupSymbol' => $groupSymbol,
            'groupLength' => $group,
            'integerRequired' => $integerRequired,
        ];

        return $result;
    }

    protected function getCurrency($currencyCode = null)
    {
        $currency = $this->_scopeResolver->getScope()->getCurrentCurrency();

        if ($currencyCode) {
            $currency = $this->currencyFactory->create()->load($currencyCode);
        } 

        return $currency;
    }

    protected function getFormat($localeCode = null)
    {
        $localeCode = $localeCode ?: $this->_localeResolver->getLocale();
        $localeData = (new DataBundle())->get($localeCode);
        $defaultSet = $localeData['NumberElements']['default'] ?: self::$defaultNumberSet;

        return $localeData['NumberElements'][$defaultSet]['patterns']['currencyFormat']
            ?: ($localeData['NumberElements'][self::$defaultNumberSet]['patterns']['currencyFormat']
                ?: explode(';', $localeData['NumberPatterns'][1])[0]);
    }
    
}
