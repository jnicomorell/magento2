<?php
declare(strict_types=1);

namespace Ceg\StagingSchedule\Model\Executor;

use Ceg\StagingSchedule\Api\ExecutorInterface;

class Demo implements ExecutorInterface
{
    const ENTITY = 'DEMO';

    public function executeStart($entityId, array $params)
    {
        // TODO: Implement executeStart() method.
        return $this;
    }

    public function executeStop($entityId, array $params)
    {
        // TODO: Implement executeStop() method.
        return $this;
    }

    public function executeUpdate($entityId, array $params)
    {
        // TODO: Implement executeUpdate() method.
        return $this;
    }
}
