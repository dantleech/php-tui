<?php

namespace PhpTui\Tui\Tests\Widget;

use PhpTui\Tui\Model\AnsiColor;
use PhpTui\Tui\Model\AxisBounds;
use PhpTui\Tui\Model\Marker;
use PhpTui\Tui\Model\Style;
use PhpTui\Tui\Model\Widget\Span;
use PhpTui\Tui\Extension\Core\Widget\Chart;
use PhpTui\Tui\Extension\Core\Widget\Chart\Axis;
use PhpTui\Tui\Extension\Core\Widget\Chart\DataSet;

class ChartTest extends WidgetTestCase
{
    public function testRender(): void
    {
        $chart = Chart::new(
            DataSet::new('data1')
                    ->marker(Marker::Dot)
                    ->style(Style::default()->fg(AnsiColor::Green))
                    ->data($this->series(0, 1, 2, 1, 0, -1, -2, -1))
        )
            ->xAxis(Axis::default()->bounds(AxisBounds::new(0, 7)))
            ->yAxis(
                Axis::default()->bounds(AxisBounds::new(-2, 2))
            );

        self::assertEquals(
            [
                '  •     ',
                ' • •    ',
                '•   •   ',
                '     • •',
                '      • ',
            ],
            $this->renderToLines($chart)
        );
    }

    public function testRenderAxisLines(): void
    {
        $chart = Chart::new(
            DataSet::new('data1')
                    ->marker(Marker::Dot)
                    ->style(Style::default()->fg(AnsiColor::Green))
                    ->data($this->series(0, 1, 2, 1, 0, -1, -2, -1))
        )->xAxis(
            Axis::default()->bounds(AxisBounds::new(0, 7))->labels([])
        )->yAxis(
            Axis::default()->bounds(AxisBounds::new(-2, 2))->labels([])
        );

        self::assertEquals(
            [
                '│ •     ',
                '│• •    ',
                '│•  •   ',
                '│    • •',
                '│     • ',
                '└───────',
            ],
            $this->renderToLines($chart, 8, 6)
        );
    }

    public function testRenderXAxisLabels(): void
    {
        $chart = Chart::new(
            DataSet::new('data1')
                    ->marker(Marker::Dot)
                    ->style(Style::default()->fg(AnsiColor::Green))
                    ->data($this->series(0, 1, 2, 1, 0, -1, -2, -1))
        )->xAxis(
            Axis::default()->bounds(AxisBounds::new(0, 7))->labels([Span::fromString('1'), Span::fromString('2')])
        )->yAxis(
            Axis::default()->bounds(AxisBounds::new(-2, 2))
        );

        self::assertEquals(
            [
                ' •••    ',
                '•   •   ',
                '     • •',
                '      • ',
                '────────',
                '1    2  ',

            ],
            $this->renderToLines($chart, 8, 6)
        );
    }

    public function testRenderManyXLabels(): void
    {
        $chart = Chart::new()
            ->datasets(
                DataSet::new('data1')
                    ->marker(Marker::Dot)
                    ->style(Style::default()->fg(AnsiColor::Green))
                    ->data($this->series(0, 1, 0, 1, 0, 1, 0, 1, 0, 1, 0, 1))
            )
            ->xAxis(
                Axis::default()->bounds(AxisBounds::new(0, 11))->labels([
                    Span::fromString('1'),
                    Span::fromString('2'),
                    Span::fromString('3'),
                    Span::fromString('4'),
                ])
            )
            ->yAxis(
                Axis::default()->bounds(AxisBounds::new(0, 1))
            );

        self::assertEquals(
            [
                ' • • • • • •',
                '• • • • • • ',
                '────────────',
                '1   2  3  4 ',
            ],
            $this->renderToLines($chart, 12, 4)
        );
    }

    public function testRenderYAxisLabels(): void
    {
        $chart = Chart::new(
            DataSet::new('data1')
                    ->marker(Marker::Dot)
                    ->style(Style::default()->fg(AnsiColor::Green))
                    ->data(
                        array_map(function (int $x, int $y) {
                            return [$x, $y];
                        }, range(0, 7), [0, 1, 2, 1, 0, -1, -2, -1])
                    )
        )->xAxis(
            Axis::default()->bounds(AxisBounds::new(0, 7))
        )->yAxis(
            Axis::default()->bounds(AxisBounds::new(-2, 2))->labels([Span::fromString('1'), Span::fromString('2')])
        );

        self::assertEquals(
            [
                '2│ •    ',
                ' │• •   ',
                ' │• •   ',
                ' │   • •',
                ' │      ',
                '1│    • ',
            ],
            $this->renderToLines($chart, 8, 6)
        );
    }

    public function testRenderManyXAndYLabels(): void
    {
        $chart = Chart::new()
            ->datasets(
                DataSet::new('data1')
                    ->marker(Marker::Dot)
                    ->style(Style::default()->fg(AnsiColor::Green))
                    ->data($this->series(0, 1, 0, 1, 0, 1, 0, 1, 0, 1, 0, 1))
            )
            ->xAxis(
                Axis::default()->bounds(AxisBounds::new(0, 11))->labels([
                    Span::fromString('1'),
                    Span::fromString('2'),
                    Span::fromString('3'),
                    Span::fromString('4'),
                ])
            )
            ->yAxis(
                Axis::default()->bounds(AxisBounds::new(0, 1))->labels([
                    Span::fromString('one'),
                    Span::fromString('two'),
                    Span::fromString('three'),
                    Span::fromString('four'),
               ])
            );

        self::assertEquals(
            [
                ' four│ •  •  •  •  •   •',
                '     │                  ',
                'three│                  ',
                '     │                  ',
                '  two│                  ',
                '  one│•  •  •  •  •  •  ',
                '     └──────────────────',
                '     1      2   3    4  ',

            ],
            $this->renderToLines($chart, 24, 8)
        );
    }

    /**
     * @return list<array{int,int}>
     */
    private function series(int ...$points): array
    {
        return array_map(function (int $x, int $y) {
            return [$x, $y];
        }, range(0, count($points) - 1), $points);
    }
}
