<?php

namespace Formax\AlertWidget\Api;

interface AlertWidgetInterface
{
    /**
     * get
     *
     * @return void
     */
    public function get();

    /**
     * set
     *
     * @param  string $value
     * @param  int $duration
     *
     * @return void
     */
    public function set($value, $duration = 86400);

    /**
     * delete
     *
     * @param  int $duration
     *
     * @return void
     */
    public function delete($duration);
}
