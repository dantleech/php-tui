<?php

declare(strict_types=1);

namespace PhpTui\Tui\Model;

interface ConstraintSolver
{
    /**
     * @param Constraint[] $constraints
     */
    public function solve(Layout $layout, Area $area, array $constraints): Areas;
}
