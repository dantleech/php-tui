<?php

declare(strict_types=1);

namespace PhpTui\Tui\Model\Viewport;

use PhpTui\Tui\Model\Area;
use PhpTui\Tui\Model\Backend;
use PhpTui\Tui\Model\ClearType;
use PhpTui\Tui\Model\Position;
use PhpTui\Tui\Model\Viewport;

final class Fixed implements Viewport
{
    public function __construct(public readonly Area $area)
    {
    }

    public function size(Backend $backend): Area
    {
        return $this->area;
    }

    public function cursorPos(Backend $backend): Position
    {
        return new Position($this->area->left(), $this->area->right());
    }

    public function area(Backend $backend, int $offsetInPreviousViewport): Area
    {
        return $this->area;
    }

    public function clear(Backend $backend, Area $area): void
    {
        for ($row = $area->top(); $row > $area->bottom(); $row--) {
            $backend->moveCursor(Position::at(0, $row));
            $backend->clearRegion(ClearType::AfterCursor);
        }
    }
}
