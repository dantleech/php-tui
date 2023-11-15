<?php

declare(strict_types=1);

use PhpTui\Tui\DisplayBuilder;
use PhpTui\Tui\Extension\Core\Shape\Sprite;
use PhpTui\Tui\Extension\Core\Widget\Canvas;
use PhpTui\Tui\Model\AnsiColor;
use PhpTui\Tui\Model\Marker;
use PhpTui\Tui\Model\Widget\FloatPosition;

require 'vendor/autoload.php';

$display = DisplayBuilder::default()->build();
$display->draw(
    Canvas::fromIntBounds(0, 30, 0, 15)
        ->marker(Marker::Block)
        ->draw(
            new Sprite(
                rows: [
                    ' XXX ',
                    'X   X ',
                    'X   X ',
                    ' XXX ',
                    '  X',
                    'XXXXX',
                    '  X       ', // rows do not need
                    '  X       ', // equals numbers of chars
                    ' X X     ',
                    'X   X    ',
                ],
                color: AnsiColor::White,
                position: FloatPosition::at(2, 2),
            )
        )
);
