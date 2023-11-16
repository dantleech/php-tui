<?php

declare(strict_types=1);

namespace PhpTui\Tui\Model;

final class BufferUpdate
{
    public function __construct(public Position $position, public Cell $cell)
    {
    }
}
