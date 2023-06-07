<?php

namespace Dew\Core\Contracts;

interface FunctionComputeEvent
{
    /**
     * Retrieve raw event data as an array.
     *
     * @return array
     */
    public function toArray(): array;
}