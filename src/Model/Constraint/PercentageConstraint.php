<?php

namespace DTL\PhpTui\Model\Constraint;

use DTL\PhpTui\Model\Constraint;

class PercentageConstraint extends Constraint
{
    public function __construct(public int $percentage)
    {
    }

    public function __toString(): string
    {
        return sprintf('Percentage(%d)', $this->percentage);
    }

}
