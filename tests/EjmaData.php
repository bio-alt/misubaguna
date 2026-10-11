<?php

namespace Tests;

use App\Ejma\Coefficients;
use PHPUnit\Framework\Assert;

/**
 * The EJMA coefficient tables are private (resources/ejma-private/, git-ignored). Tests that run
 * the engine call requireOrSkip() (or use RequiresEjmaData) so a checkout without the data skips
 * them instead of failing.
 */
final class EjmaData
{
    public static function requireOrSkip(): void
    {
        if (! Coefficients::available()) {
            Assert::markTestSkipped('Private EJMA data is not installed (resources/ejma-private/coefficients.php).');
        }
    }
}
