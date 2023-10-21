<?php

namespace DTL\PhpTerm\Action;

use DTL\PhpTerm\Colors;
use DTL\PhpTerm\Action;

final class SetBackgroundColor implements Action
{
    public function __construct(public readonly Colors $color)
    {
    }

    public function __toString(): string
    {
        return sprintf('SetBackgroundColor(%s)', $this->color->name);
    }
}
