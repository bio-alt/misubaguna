<?php

namespace Tests;

use PHPUnit\Framework\Attributes\Before;

/** Skips every test of the class when the private EJMA coefficient tables are not installed. */
trait RequiresEjmaData
{
    #[Before]
    protected function skipWithoutEjmaData(): void
    {
        EjmaData::requireOrSkip();
    }
}
