<?php

namespace Dew\Core\Contracts;

interface ProvidesDewContext
{
    /**
     * The MNS queue name.
     */
    public function mnsQueue(): ?string;
}
