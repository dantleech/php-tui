<?php

namespace DTL\PhpTerm\Command;

use DTL\PhpTerm\TermCommand;

final class CursorShow  implements TermCommand
{
    public function __construct(public readonly bool $show)
    {
    }

    public function __toString(): string
    {
        return sprintf('CursorShow(%s)', $this->show ? 'true':'false');
    }
}
