<?php
declare(strict_types=1);

namespace Ceg\StagingSchedule\Api;

interface ExecutorInterface
{
    const METHOD_START = 'executeStart';
    const METHOD_STOP = 'executeStop';
    const METHOD_UPDATE = 'executeUpdate';

    /**
     * @param       $entityId
     * @param array $params
     */
    public function executeStart($entityId, array $params);

    /**
     * @param       $entityId
     * @param array $params
     */
    public function executeStop($entityId, array $params);

    /**
     * @param       $entityId
     * @param array $params
     */
    public function executeUpdate($entityId, array $params);
}
