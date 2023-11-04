<?php

namespace PhpTui\Tui\Example\Slideshow;

use PhpTui\Term\Actions;
use PhpTui\Term\Event\CodedKeyEvent;
use PhpTui\Term\KeyCode;
use PhpTui\Term\Terminal;
use PhpTui\Tui\Model\Constraint;
use PhpTui\Tui\Model\Direction;
use PhpTui\Tui\Model\Display;
use PhpTui\Tui\Model\Widget;
use PhpTui\Tui\Model\Widget\HorizontalAlignment;
use PhpTui\Tui\Widget\Grid;
use PhpTui\Tui\Widget\Paragraph;

class App
{
    private int $selected = 0;

    /**
     * @param Slide[] $slides
     */
    public function __construct(
        private Terminal $terminal,
        private Display $display,
        private array $slides
    ) {
    }
    public function run(): void
    {
        $this->terminal->enableRawMode();
        $this->terminal->execute(Actions::cursorHide());
        $this->terminal->execute(Actions::alternateScreenEnable());

        $draw = true;
        while (true) {
            while (null !== $event = $this->terminal->events()->next()) {
                $draw = true;
                if ($event instanceof CodedKeyEvent) {
                    if ($event->code === KeyCode::Left) {
                        $this->selected = max(0, $this->selected - 1);
                    }
                    if ($event->code === KeyCode::Right) {
                        $this->selected = min(count($this->slides) - 1, $this->selected + 1);
                    }
                    if ($event->code === KeyCode::Esc) {
                        break 2;
                    }
                }
            }
            if ($draw) {
                $this->display->drawWidget(
                    Grid::default()
                        ->constraints(
                            Constraint::min(10),
                            Constraint::max(1),
                        )
                        ->widgets(
                            $this->currentSlide()->build(),
                            $this->footer(),
                        )
                );
                $draw = false;
            }

            usleep(10_000);
        }

        $this->terminal->disableRawMode();
        $this->terminal->execute(Actions::cursorShow());
        $this->terminal->execute(Actions::alternateScreenDisable());
    }

    private function currentSlide(): Slide
    {
        return $this->slides[$this->selected];
    }

    private function footer(): Widget
    {
        return Grid::default()
            ->direction(Direction::Horizontal)
            ->constraints(
                Constraint::percentage(50),
                Constraint::percentage(50),
            )->widgets(
                Paragraph::fromString(sprintf(
                    '%s/%s',
                    $this->selected + 1,
                    count($this->slides),
                )),
                Paragraph::fromString(sprintf(
                    '%s',
                    $this->currentSlide()->title(),
                ))->alignment(HorizontalAlignment::Right),
            );
    }
}
