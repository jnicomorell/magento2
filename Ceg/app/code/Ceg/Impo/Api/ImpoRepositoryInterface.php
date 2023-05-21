<?php
declare(strict_types=1);

namespace Ceg\Impo\Api;

use Ceg\Impo\Api\Data\ImpoInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;

interface ImpoRepositoryInterface
{
    /**
     * @param int $impoId
     * @return ImpoInterface
     * @throws NoSuchEntityException
     */
    public function getById($impoId);

    /**
     * @param string $cegId
     * @return ImpoInterface
     * @throws NoSuchEntityException
     */
    public function getByCegId($cegId);

    /**
     * @param  ImpoInterface $impo
     * @return ImpoInterface
     * @throws CouldNotSaveException
     */
    public function save(ImpoInterface $impo);

    /**
     * @param  ImpoInterface $impo
     * @return ImpoInterface
     * @throws CouldNotSaveException
     */
    public function open(ImpoInterface $impo);

    /**
     * @param  ImpoInterface $impo
     * @return ImpoInterface
     * @throws CouldNotSaveException
     */
    public function closed(ImpoInterface $impo);

    /**
     * @param  ImpoInterface $impo
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(ImpoInterface $impo);
}
