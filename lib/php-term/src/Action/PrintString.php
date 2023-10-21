<?php

namespace DTL\PhpTerm\Action;

use DTL\PhpTerm\Action;

final class PrintString implements Action
{
    public function __construct(public readonly string $string)
    {
    }

    public function __toString(): string
    {
        return sprintf('Print("%s")', $this->string);
    }
}
