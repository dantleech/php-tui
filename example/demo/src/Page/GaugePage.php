<?php

declare(strict_types=1);

namespace PhpTui\Tui\Example\Demo\Page;

use PhpTui\Term\Event;
use PhpTui\Tui\Example\Demo\Component;
use PhpTui\Tui\Extension\Core\Widget\Block;
use PhpTui\Tui\Extension\Core\Widget\Block\Padding;
use PhpTui\Tui\Extension\Core\Widget\GaugeWidget;
use PhpTui\Tui\Extension\Core\Widget\Grid;
use PhpTui\Tui\Extension\Core\Widget\Paragraph;
use PhpTui\Tui\Model\AnsiColor;
use PhpTui\Tui\Model\Constraint;
use PhpTui\Tui\Model\Direction;
use PhpTui\Tui\Model\Style;
use PhpTui\Tui\Model\Widget;
use PhpTui\Tui\Model\Widget\Borders;
use PhpTui\Tui\Model\Widget\Span;
use PhpTui\Tui\Model\Widget\Title;

class GaugePage implements Component
{
    /**
     * @var array<int,Download>
     */
    private array $downloads = [];

    public function build(): Widget
    {
        foreach ($this->downloads as $index => $download) {
            $download->downloaded += rand(0, 100) / 10;
            if ($download->downloaded > $download->size) {
                $download->downloaded = 0;
                unset($this->downloads[$index]);
            }
        }

        if ([] === ($this->downloads)) {
            for ($i = 0; $i <= rand(10, 20); $i++) {
                $this->downloads[] = new Download(rand(0, 1000));
            }
        }

        return Block::default()
            ->borders(Borders::ALL)
            ->padding(Padding::all(2))
            ->titles(Title::fromString(sprintf('Downloading %s files', count($this->downloads))))
            ->widget(
                Grid::default()
                ->constraints(
                    ...array_map(fn () => Constraint::length(1), $this->downloads),
                    ...[Constraint::min(0)],
                )
                ->widgets(
                    ...array_map(
                        function (Download $download) {
                            return Grid::default()
                                ->direction(Direction::Horizontal)
                                ->constraints(
                                    Constraint::percentage(30),
                                    Constraint::percentage(70),
                                )
                                ->widgets(
                                    Paragraph::fromSpans(
                                        Span::fromString('Downloaded')->style(
                                            Style::default()->fg(AnsiColor::Green)
                                        ),
                                        Span::fromString(sprintf(
                                            ' %d/%s bytes',
                                            $download->downloaded,
                                            $download->size,
                                        ))->style(
                                            Style::default()->fg(AnsiColor::White)
                                        )
                                    ),
                                    GaugeWidget::default()
                                        ->ratio($download->ratio())
                                        ->style(Style::default()->fg(AnsiColor::Yellow))
                                );
                        },
                        $this->downloads
                    )
                )
            );
    }

    public function handle(Event $event): void
    {
    }
}