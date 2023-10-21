<?php

use DTL\PhpTerm\Backend\BufferBackend;
use DTL\PhpTerm\TermControl;
use DTL\PhpTui\Adapter\PhpTerm\PhpTermBackend;
use DTL\PhpTui\Adapter\Symfony\SymfonyBackend;
use DTL\PhpTui\Model\AnsiColor;
use DTL\PhpTui\Model\Area;
use DTL\PhpTui\Model\AxisBounds;
use DTL\PhpTui\Model\Buffer;
use DTL\PhpTui\Model\Color;
use DTL\PhpTui\Model\Constraint;
use DTL\PhpTui\Model\Direction;
use DTL\PhpTui\Model\Layout;
use DTL\PhpTui\Model\Marker;
use DTL\PhpTui\Model\Modifier;
use DTL\PhpTui\Model\Position;
use DTL\PhpTui\Model\Style;
use DTL\PhpTui\Model\Terminal;
use DTL\PhpTui\Model\Widget;
use DTL\PhpTui\Model\Widget\Borders;
use DTL\PhpTui\Model\Widget\Line;
use DTL\PhpTui\Model\Widget\Title;
use DTL\PhpTui\Widget\Block;
use DTL\PhpTui\Widget\Canvas;
use DTL\PhpTui\Widget\Canvas\CanvasContext;
use DTL\PhpTui\Widget\Canvas\Shape\Circle;
use DTL\PhpTui\Widget\Canvas\Shape\Map;
use DTL\PhpTui\Widget\Canvas\Shape\MapResolution;
use DTL\PhpTui\Widget\Canvas\Shape\Rectangle;
use Symfony\Component\Console\Cursor;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Terminal as SymfonyTerminal;

require_once __DIR__ . '/../vendor/autoload.php';


class App
{
    private function __construct(
        private float $x,
        private float $y,
        private Circle $ball,
        private Area $playground,
        private float $vx,
        private float $vy,
        private int $tickCount,
        private Marker $marker,
    ) {
    }

    public static function new(): self
    {
        return new self(
            x: 0,
            y: 0,
            ball: Circle::fromPrimitives(20, 40, 10, AnsiColor::Yellow),
            playground: Area::fromPrimitives(10, 10, 200, 100),
            vx: 1.0,
            vy: 1.0,
            tickCount: 0,
            marker: Marker::Dot,
        );
    }

    public static function run(): void
    {
        $app = App::new();
        $cursor = new Cursor(new ConsoleOutput());
        $cursor->hide();
        $cursor->clearScreen();
        $b = BufferBackend::new();
        $backend = new PhpTermBackend(TermControl::new($b), new SymfonyTerminal());
        $backend = PhpTermBackend::new();
        $terminal = Terminal::fullscreen($backend);
        while (true) {
            $terminal->draw(function (Buffer $buffer) use ($app, $b): void {
                $app->ui($buffer);
            });
            usleep(16000);
            $app->onTick();
        }
    }

    public function ui(Buffer $buffer): void
    {
        $mainLayout = Layout::default()
            ->direction(Direction::Horizontal)
            ->constraints([
                Constraint::percentage(50),
                Constraint::percentage(50),
            ])
            ->split($buffer->area());
        $rightLayout = Layout::default()
            ->direction(Direction::Vertical)
            ->constraints([
                Constraint::percentage(50),
                Constraint::percentage(50),
            ])
            ->split($mainLayout->get(1));

        $this->mapCanvas($mainLayout->get(0))->render($mainLayout->get(0), $buffer);
        $this->pongCanvas($rightLayout->get(0))->render($rightLayout->get(0), $buffer);
        $this->boxesCanvas($rightLayout->get(1))->render($rightLayout->get(1), $buffer);
    }

    private function mapCanvas(Area $area): Widget
    {
        return Canvas::default()
            ->block(Block::default()->borders(Borders::ALL)->title(Title::fromString('World')))
            ->marker($this->marker)
            ->paint(function (CanvasContext $context) {
                $context->draw(Map::default()->resolution(MapResolution::High)->color(AnsiColor::Green));
                $context->print($this->x, -$this->y, Line::fromString('You are here!')->patchStyle(Style::default()->fg(AnsiColor::Yellow)->addModifier(Modifier::Italic)));
            })
            ->xBounds(AxisBounds::new(-180, 180))
            ->yBounds(AxisBounds::new(-90, 90));
    }

    private function pongCanvas(Area $area): Widget
    {
        return Canvas::default()
            ->block(Block::default()->borders(Borders::ALL)->title(Title::fromString('Pong')))
            ->marker($this->marker)
            ->paint(function (CanvasContext $context) {
                $context->draw($this->ball);
            })
            ->xBounds(AxisBounds::new(10, 210))
            ->yBounds(AxisBounds::new(10, 110));
    }

    private function onTick(): void
    {
        $this->tickCount++;
        if ($this->tickCount % 30 === 0) {
            $this->marker = match ($this->marker) {
                Marker::Dot => Marker::Braille,
                Marker::Braille => Marker::Block,
                Marker::Block => Marker::Bar,
                //Marker::HalfBlock => Marker::Bar,
                Marker::Bar => Marker::Dot,
            };
        }

        // bounce the ball by flipping the velocity vector
        $ball = $this->ball;
        $playground = $this->playground;
        if (
            $ball->position->x - $ball->radius < $playground->left()
            || $ball->position->x + $ball->radius > $playground->right()
        ) {
            $this->vx = -$this->vx;
        }
        if (
            $ball->position->y - $ball->radius < $playground->top()
            || $ball->position->y + $ball->radius > $playground->bottom()
        ) {
            $this->vy = -$this->vy;
        }

        $this->ball->position->update($this->ball->position->x + $this->vx , $this->ball->position->y + $this->vy);
    }

    private function boxesCanvas(Area $area): Widget
    {
        [$left, $right, $bottom, $top] = [ 0.0, $area->width, 0.0, $area->height * 2 - 4.0];
        return Canvas::default()
            ->block(Block::default()->borders(Borders::ALL)->title(Title::fromString('Rectangles')))
            ->marker($this->marker)
            ->xBounds(AxisBounds::new($left, $right))
            ->yBounds(AxisBounds::new($bottom, $top))
            ->paint(function (CanvasContext $context) {
                for ($i = 0; $i <= 11; $i++) {
                    $context->draw(Rectangle::fromPrimitives(
                        x: $i * $i + 3 * $i / 2.0 + 2.0,
                        y: 2.0,
                        width: $i,
                        height: $i,
                        color: AnsiColor::Red,
                    ));
                    $context->draw(Rectangle::fromPrimitives(
                        x: $i * $i + 3 * $i / 2.0 + 2.0,
                        y: 21.0,
                        width: $i,
                        height: $i,
                        color: AnsiColor::Blue,
                    ));
                }

                for ($i = 0; $i <= 100; $i++) {
                    if ($i % 10 != 0) {
                        $context->print($i + 1.0, 0.0, Line::fromString(sprintf('%d', $i % 10)));
                    }
                    if ($i % 2 == 0 && $i % 10 != 0) {
                        $context->print(0.0, $i, Line::fromString(sprintf('%d', $i % 10)));
                    }
                }
            });
    }
}

App::run();
