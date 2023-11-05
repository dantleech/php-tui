<?php

namespace PhpTui\Tui\Model;

interface WidgetRenderer
{
    public function render(WidgetRenderer $renderer, Widget $widget, Area $area, Buffer $buffer): void;
}