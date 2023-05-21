<?php

namespace Ceg\Core\Model\Config;

class Build extends \Magento\Framework\App\Config\Value
{
    const DS = '/';
    /**
     * @return void
     */
    public function afterLoad()
    {
        $this->setValue($this->getBuild());
    }

    /**
     * @return string
     * @throws \Safe\Exceptions\JsonException
     */
    protected function getBuild()
    {
        $result = [];

        try {
            // @codingStandardsIgnoreStart
            if(!defined('DS')){
                define('DS', DIRECTORY_SEPARATOR);
            }

            $result = (file_exists(BP . DS . 'build.txt'))
                ? file(BP . DS . 'build.txt', FILE_USE_INCLUDE_PATH) : [];

            if (!empty($result)) {
                $result[] = '.git/HEAD:';
                $result[] = file_get_contents(BP . DS . '.git' . DS . 'HEAD', FILE_USE_INCLUDE_PATH);
                $result[] = '--------------------------------------------------';
                $result[] = 'Last Commit:';
                $result[] = exec('git log -1 --format=%cd | cat');
                $result[] = exec('git log -1 --format=%s | cat');
            }
            // @codingStandardsIgnoreEnd
        } catch (\Exception $e) {
            $result[] = $e->getMessage();
        }

        return \Safe\json_encode($result);
    }
}
