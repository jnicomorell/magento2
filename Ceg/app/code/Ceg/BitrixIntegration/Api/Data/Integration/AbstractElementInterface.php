<?php
declare(strict_types=1);

namespace Ceg\BitrixIntegration\Api\Data\Integration;

interface AbstractElementInterface
{
    /**
     * @param object $value
     * @return $this
     */
    public function setModel($value);

    /**
     * @return string
     */
    public function getType();

    /**
     * @return string
     */
    public function getId();

    /**
     * @return string
     */
    public function getWebsiteId();

    /**
     * @return bool
     */
    public function isEnabled();

    /**
     * @return bool
     */
    public function useAccessToken();

    /**
     * @return string
     */
    public function getUrl();

    /**
     * @return array
     */
    public function getRequestData();

    /**
     * @param $response
     */
    public function validateResponse($response);
}
