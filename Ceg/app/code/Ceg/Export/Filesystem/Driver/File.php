<?php
declare(strict_types=1);

namespace Ceg\Export\Filesystem\Driver;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Config\ScopeConfigInterface;

class File extends \Magento\Framework\Filesystem\Driver\File
{
    const CONFIG_DELIMITER = 'ceg_export/general/delimiter';
    /**
     * Writes one CSV row to the file.
     *
     * @param resource $resource
     * @param array $data
     * @param string $delimiter
     * @param string $enclosure
     * @return int
     * @throws FileSystemException
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @SuppressWarnings(PHPMD.ErrorControlOperator)
     */
    public function filePutCsv($resource, array $data, $delimiter = ',', $enclosure = '"')
    {
        /**
         * Security enhancement for CSV data processing by Excel-like applications.
         * @see https://bugzilla.mozilla.org/show_bug.cgi?id=1054702
         *
         * @var $value string|Phrase
         */
        // ignore added because of core rewritten code
        // @codingStandardsIgnoreStart
        foreach ($data as $key => $value) {
            if (!is_string($value)) {
                $value = (string)$value;
            }
            if (isset($value[0]) && in_array($value[0], ['=', '+', '-'])) {
                $data[$key] = ' ' . $value;
            }
        }

        //Heredado de core file
        $this->objectManager = ObjectManager::getInstance();
        $scopeConfig = $this->objectManager->create(\Magento\Framework\App\Config\ScopeConfigInterface::class);

        $delimiter = $scopeConfig->getValue(
            self::CONFIG_DELIMITER,
            ScopeInterface::SCOPE_STORE
        );
        //Heredado de core file
        $result = @fputcsv($resource, $data, $delimiter, $enclosure);
        if (!$result) {
            throw new FileSystemException(
                new Phrase(
                    'An error occurred during "%1" filePutCsv execution.',
                    [$this->getWarningMessage()]
                )
            );
        }
        return $result;
        // @codingStandardsIgnoreEnd
    }
}
