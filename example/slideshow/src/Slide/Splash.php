<?php

namespace PhpTui\Tui\Example\Slideshow\Slide;

use PhpTui\Tui\Adapter\Bdf\FontRegistry;
use PhpTui\Tui\Adapter\Bdf\Shape\TextShape;
use PhpTui\Tui\Example\Slideshow\Slide;
use PhpTui\Tui\Model\AnsiColor;
use PhpTui\Tui\Model\AxisBounds;
use PhpTui\Tui\Model\Color;
use PhpTui\Tui\Model\Widget;
use PhpTui\Tui\Model\Widget\FloatPosition;
use PhpTui\Tui\Model\Widget\Line;
use PhpTui\Tui\Widget\Canvas;
use PhpTui\Tui\Widget\Canvas\CanvasContext;
use PhpTui\Tui\Widget\Canvas\Shape\Line as PhpTuiLine;

class Splash implements Slide
{
    public function __construct(private FontRegistry $registry)
    {
    }

    public function title(): string
    {
        return 'Building a better world';
    }

    public function build(): Widget
    {
        return Canvas::default()
            ->xBounds(AxisBounds::new(0, 320))
            ->yBounds(AxisBounds::new(0, 240))
            ->paint(function (CanvasContext $context): void {
                $title = new TextShape(
                    $this->registry->get('default'),
                    'PHP-TUI',
                    AnsiColor::Cyan,
                    FloatPosition::at(10, 200),
                    scaleX: 4,
                    scaleY: 4,
                );
                $subTitle = new TextShape(
                    $this->registry->get('default'),
                    'Building better TUIs!',
                    AnsiColor::White,
                    FloatPosition::at(10, 180),
                    scaleX: 2,
                    scaleY: 2,
                );
                $context->draw($title);
                $context->draw($subTitle);
                $context->draw(PhpTuiLine::fromPrimitives(0, 160, 320, 160, AnsiColor::Gray));
                $context->print(12, 140, Line::fromString('Daniel Leech 2024'));
            });
    }
}
