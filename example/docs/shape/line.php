<?php

declare(strict_types=1);

use PhpTui\Tui\DisplayBuilder;
use PhpTui\Tui\Extension\Core\Shape\Line;
use PhpTui\Tui\Extension\Core\Widget\Canvas;
use PhpTui\Tui\Model\AnsiColor;
use PhpTui\Tui\Model\Marker;

require 'vendor/autoload.php';

$display = DisplayBuilder::default()->build();
$display->draw(
    Canvas::fromIntBounds(0, 20, 0, 20)
        ->marker(Marker::Dot)
        ->draw(Line::fromScalars(
            0,  // x1
            0,  // y1
            20, // x2
            20, // y2
        )->color(AnsiColor::Green))
);
