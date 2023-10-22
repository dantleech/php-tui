<?php

namespace DTL\PhpTerm\ProcessRunner;

use Closure;
use DTL\PhpTerm\ProcessResult;
use DTL\PhpTerm\ProcessRunner;

/**
 * Implementation to be used for test scenarios.
 */
class ClosureRunner  implements ProcessRunner
{
    /**
     * @param Closure(string[]): ProcessResult $closure
     */
    public function __construct(private Closure $closure)
    {
    }

    public function run(array $command): ProcessResult
    {
        return ($this->closure)($command);
    }

    /**
     * @param Closure(string[]): ProcessResult $closure
     */
    public static function new(Closure $closure): self
    {
        return new self($closure);
    }
}
