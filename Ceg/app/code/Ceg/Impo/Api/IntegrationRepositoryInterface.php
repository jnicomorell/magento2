<?php
declare(strict_types=1);

namespace Ceg\Impo\Api;

use Ceg\Impo\Api\Data\Integration\ImpoInterface;

interface IntegrationRepositoryInterface
{
    /**
     * @param  ImpoInterface $impo
     * @return mixed
     */
    public function saveImpo(ImpoInterface $impo);

    /**
     * @param string $cegId
     * @return mixed
     */
    public function getImpo($cegId);
}
