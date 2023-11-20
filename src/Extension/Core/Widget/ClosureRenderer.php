<?php

declare(strict_types=1);

namespace PhpTui\Tui\Extension\Core\Widget;

use Closure;
use PhpTui\Tui\Model\Display\Buffer;
use PhpTui\Tui\Model\Widget;
use PhpTui\Tui\Model\WidgetRenderer;

final class ClosureRenderer implements WidgetRenderer
{
    /**
     * @param Closure(WidgetRenderer, Widget, Buffer): void $renderer
     */
    public function __construct(private readonly Closure $renderer)
    {
    }

    public function render(WidgetRenderer $renderer, Widget $widget, Buffer $buffer): void
    {
        ($this->renderer)($renderer, $widget, $buffer);
    }
}
