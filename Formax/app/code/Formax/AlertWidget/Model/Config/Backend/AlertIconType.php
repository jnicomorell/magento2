<?php

namespace Formax\AlertWidget\Model\Config\Backend;

class AlertIconType extends \Magento\Config\Model\Config\Backend\File
{
    /**
     * @return string[]
     */
    public function getAllowedExtensions()
    {
        return ['png', 'ico', 'svg'];
    }
}
