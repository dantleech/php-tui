<?php

namespace DTL\PhpTerm\ProcessRunner;

use DTL\PhpTerm\ProcessResult;
use DTL\PhpTerm\ProcessRunner;
use RuntimeException;

final class ProcRunner implements ProcessRunner
{
    public function run(array $command): ProcessResult
    {
        $spec = [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];
        $process = proc_open($command, $spec, $pipes, null, null, ['suppress_errors' => true]);
        if (!\is_resource($process)) {
            throw new RuntimeException(sprintf('Could not spawn process: "%s"', implode('", "', $command)));
        }

        $info = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);

        return new ProcessResult($exitCode, $info);
    }
}
