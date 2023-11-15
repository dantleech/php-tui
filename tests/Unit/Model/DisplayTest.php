<?php

declare(strict_types=1);

namespace PhpTui\Tui\Tests\Unit\Model;

use PhpTui\Tui\DisplayBuilder;
use PhpTui\Tui\Extension\Core\Shape\Points;
use PhpTui\Tui\Extension\Core\Widget\Canvas;
use PhpTui\Tui\Extension\Core\Widget\Paragraph;
use PhpTui\Tui\Extension\Core\Widget\RawWidget;
use PhpTui\Tui\Model\AnsiColor;
use PhpTui\Tui\Model\Backend\DummyBackend;
use PhpTui\Tui\Model\Buffer;
use PhpTui\Tui\Model\Marker;
use PhpTui\Tui\Model\Position;
use PHPUnit\Framework\TestCase;

class DisplayTest extends TestCase
{
    public function testAutoresize(): void
    {
        $backend = DummyBackend::fromDimensions(4, 4);
        $terminal = DisplayBuilder::default($backend)->build();
        $backend->setDimensions(2, 2);

        // intentionally go out of bounds
        $terminal->draw(new RawWidget(function (Buffer $buffer): void {
            for ($y = 0; $y < 4; $y++) {
                for ($x = 0; $x < 4; $x++) {
                    $buffer->putString(new Position($x, $y), 'h');
                }
            }
        }));
        self::assertEquals(<<<'EOT'
            hh  
            hh  
                
                
            EOT, $backend->toString());
    }

    public function testDraw(): void
    {
        $backend = DummyBackend::fromDimensions(4, 4);
        $terminal = DisplayBuilder::default($backend)->build();
        $terminal->draw(new RawWidget(function (Buffer $buffer): void {
            $x = 0;
            for ($y = 0; $y <= 4; $y++) {
                $buffer->putString(new Position($x++, $y), 'x');
            }
        }));
        self::assertEquals(
            <<<'EOT'
                x   
                 x  
                  x 
                   x
                EOT,
            $backend->flushed()
        );
    }

    public function testRender(): void
    {
        $backend = DummyBackend::fromDimensions(4, 4);
        $terminal = DisplayBuilder::default($backend)->build();
        $terminal->draw(Canvas::fromIntBounds(0, 3, 0, 3)->marker(Marker::Dot)->draw(Points::new([
            [3, 3], [2, 2], [1, 1], [0, 0]
        ], AnsiColor::Green)));

        self::assertEquals(
            <<<'EOT'
                   •
                  • 
                 •  
                •   
                EOT,
            $backend->flushed()
        );
    }

    public function testFlushes(): void
    {
        $backend = DummyBackend::fromDimensions(10, 4);
        $terminal = DisplayBuilder::default($backend)->build();
        $terminal->buffer()->putString(new Position(2, 1), 'X');
        $terminal->buffer()->putString(new Position(0, 0), 'X');
        $terminal->flush();
        self::assertEquals(
            implode("\n", [
                'X         ',
                '  X       ',
                '          ',
                '          ',
            ]),
            $backend->toString()
        );
    }

    public function testInlineViewport(): void
    {
        $backend = new DummyBackend(10, 10, Position::at(0, 15));
        $terminal = DisplayBuilder::default($backend)->inline(10)->build();
        $terminal->draw(Paragraph::fromString('Hello'));

        self::assertEquals(6, $terminal->viewportArea()->top());
        self::assertEquals(0, $terminal->viewportArea()->left());
    }

    public function testFixedViewport(): void
    {
        $backend = new DummyBackend(10, 10, Position::at(0, 15));
        $terminal = DisplayBuilder::default($backend)->fixed(1, 2, 20, 15)->build();
        $terminal->draw(Paragraph::fromString('Hello'));

        self::assertEquals(1, $terminal->viewportArea()->position->x);
        self::assertEquals(2, $terminal->viewportArea()->position->y);
        self::assertEquals(2, $terminal->viewportArea()->top());
        self::assertEquals(1, $terminal->viewportArea()->left());
        self::assertEquals(21, $terminal->viewportArea()->right());
        self::assertEquals(17, $terminal->viewportArea()->bottom());
    }
}
