<?php

namespace Dew\Core\Contracts;

interface ProvidesDewContext
{
    /**
     * The MNS queue name.
     */
    public function mnsQueue(): ?string;

    /**
     * The Tablestore instance name.
     */
    public function tablestoreInstance(): ?string;

    /**
     * The cache table name on Tablestore instance.
     */
    public function tablestoreCache(): string;
}
