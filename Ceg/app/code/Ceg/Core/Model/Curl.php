<?php
declare(strict_types=1);

namespace Ceg\Core\Model;

use Magento\Framework\HTTP\Client\Curl as CurlLibrary;

class Curl extends CurlLibrary
{
    const METHOD_PATCH = "PATCH";
    public $curParams = null;

    /**
     * Make PATCH request
     *
     * @param string $uri
     * @param array|string $params
     * @return void
     * @see \Magento\Framework\HTTP\Client#post($uri, $params)
     */
    public function patch($uri, $params)
    {
        $this->curParams = $params;
        $this->makeRequest(self::METHOD_PATCH, $uri, $params);
    }

    /**
     * Set curl option directly
     * @param string $name
     * @param string $value
     * @return void
     */
    protected function curlOption($name, $value)
    {
        parent::curlOption($name, $value);
        if ($name == CURLOPT_CUSTOMREQUEST && $value == self::METHOD_PATCH) {
            $optionValue = is_array($this->curParams) ? http_build_query($this->curParams) : $this->curParams;
            parent::curlOption(CURLOPT_POSTFIELDS, $optionValue);
        }
    }

}
