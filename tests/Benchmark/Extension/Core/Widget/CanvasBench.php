<?php

declare(strict_types=1);

namespace PhpTui\Tui\Tests\Benchmark\Extension\Core\Widget;

use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use PhpTui\Term\InformationProvider\AggregateInformationProvider;
use PhpTui\Term\InformationProvider\ClosureInformationProvider;
use PhpTui\Term\Painter\StringPainter;
use PhpTui\Term\RawMode\NullRawMode;
use PhpTui\Term\Size;
use PhpTui\Term\Terminal;
use PhpTui\Tui\Bridge\PhpTerm\PhpTermBackend;

use PhpTui\Tui\DisplayBuilder;
use PhpTui\Tui\Extension\Core\Shape\Map;
use PhpTui\Tui\Extension\Core\Shape\MapResolution;
use PhpTui\Tui\Extension\Core\Widget\Canvas;
use PhpTui\Tui\Model\Display;

#[Iterations(10)]
#[Revs(25)]
final class CanvasBench
{
    private Display $display;

    private StringPainter $painter;

    public function __construct()
    {
        $this->painter = new StringPainter();
        $terminal = Terminal::new(
            infoProvider: new AggregateInformationProvider([
                ClosureInformationProvider::new(function (string $info) {
                    if ($info === Size::class) {
                        return new Size(100, 100);
                    }
                })

            ]),
            rawMode: new NullRawMode(),
            painter: $this->painter,
        );
        $this->display = DisplayBuilder::default(PhpTermBackend::new($terminal))->build();
    }

    public function benchLowResolutionMap(): void
    {
        $this->display->draw(
            Canvas::fromIntBounds(-180, 180, -90, 90)->draw(
                Map::default()->resolution(MapResolution::Low)
            )
        );
    }

    public function benchHighResolutionMap(): void
    {
        $this->display->draw(
            Canvas::fromIntBounds(-180, 180, -90, 90)->draw(
                Map::default()->resolution(MapResolution::High)
            )
        );
    }
}
