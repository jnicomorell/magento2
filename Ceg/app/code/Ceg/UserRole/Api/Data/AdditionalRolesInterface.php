<?php
declare(strict_types=1);

namespace Ceg\UserRole\Api\Data;

interface AdditionalRolesInterface
{
    /**
     * @return int
     */
    public function getUserId(): int;

    /**
     * @param int $value
     * @return void
     */
    public function setUserId(int $value): void;

    /**
     * @return string
     */
    public function getRoleIds(): ?string;

    /**
     * @param $value
     * @return void
     */
    public function setRoleIds(string $value): void;
}
