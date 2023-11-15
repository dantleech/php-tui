<?php

declare(strict_types=1);

namespace PhpTui\Term\InformationProvider;

use PhpTui\Term\InformationProvider;
use PhpTui\Term\Size;
use PhpTui\Term\TerminalInformation;

class SizeFromEnvVarProvider implements InformationProvider
{
    public function for(string $classFqn): ?TerminalInformation
    {
        if ($classFqn !== Size::class) {
            return null;
        }

        $lines = getenv('LINES');
        $cols = getenv('COLUMNS');

        if ('' === $lines || '' === $cols || false === $cols || false === $lines) {
            return null;
        }

        /** @phpstan-ignore-next-line */
        return new Size((int) $lines, (int) $cols);
    }

    public static function new(): self
    {
        return new self();
    }
}
