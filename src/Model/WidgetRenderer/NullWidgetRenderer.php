<?php

namespace PhpTui\Tui\Model\WidgetRenderer;

use PhpTui\Tui\Model\Area;
use PhpTui\Tui\Model\Buffer;
use PhpTui\Tui\Model\Widget;
use PhpTui\Tui\Model\WidgetRenderer;

/**
 * This renderer does nothing.
 *
 * It should typically be used as the "renderer" when
 * calling the aggregate renderer to satisfy the contract.
 */
class NullWidgetRenderer implements WidgetRenderer
{
    public function render(WidgetRenderer $renderer, Widget $widget, Area $area, Buffer $buffer): void
    {
    }
}