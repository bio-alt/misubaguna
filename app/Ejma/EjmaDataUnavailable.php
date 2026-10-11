<?php

namespace App\Ejma;

/**
 * The private EJMA data (Appendix I coefficient tables) is not installed. It lives in
 * resources/ejma-private/, which is git-ignored and copied to the server by hand, so a
 * deployment straight from the public repository has none. Not a visitor input problem.
 */
final class EjmaDataUnavailable extends \RuntimeException
{
    public static function missing(string $file): self
    {
        return new self("EJMA coefficient data is not installed (expected {$file}). Copy resources/ejma-private/ to this server; see its README.md.");
    }

    public static function invalid(string $file, string $table): self
    {
        return new self("EJMA coefficient data in {$file} is incomplete: table '{$table}' is missing or empty.");
    }
}
