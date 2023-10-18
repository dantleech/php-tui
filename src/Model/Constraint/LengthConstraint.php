<?php

namespace DTL\PhpTui\Model\Constraint;

use DTL\PhpTui\Model\Constraint;

class LengthConstraint extends Constraint
{
    public function __construct(public int $length)
    {
    }

    public function __toString(): string
    {
        return sprintf('Length(%d)', $this->length);
    }
}
