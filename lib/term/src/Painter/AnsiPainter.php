<?php

declare(strict_types=1);

namespace PhpTui\Term\Painter;

use PhpTui\Term\Action;
use PhpTui\Term\Action\AlternateScreenEnable;
use PhpTui\Term\Action\Clear;
use PhpTui\Term\Action\CursorShow;
use PhpTui\Term\Action\EnableMouseCapture;
use PhpTui\Term\Action\MoveCursor;
use PhpTui\Term\Action\PrintString;
use PhpTui\Term\Action\RequestCursorPosition;
use PhpTui\Term\Action\Reset;
use PhpTui\Term\Action\SetBackgroundColor;
use PhpTui\Term\Action\SetForegroundColor;
use PhpTui\Term\Action\SetModifier;
use PhpTui\Term\Action\SetRgbBackgroundColor;
use PhpTui\Term\Action\SetRgbForegroundColor;
use PhpTui\Term\Attribute;
use PhpTui\Term\ClearType;
use PhpTui\Term\Colors;
use PhpTui\Term\Painter;
use PhpTui\Term\Writer;
use RuntimeException;

final class AnsiPainter implements Painter
{
    public function __construct(private readonly Writer $writer)
    {
    }

    public static function new(Writer $writer): self
    {
        return new self($writer);
    }

    public function paint(array $actions): void
    {
        foreach ($actions as $action) {
            $this->drawCommand($action);
        }
    }

    private function drawCommand(Action $action): void
    {
        if ($action instanceof PrintString) {
            $this->writer->write($action->string);

            return;
        }
        if ($action instanceof SetForegroundColor && $action->color === Colors::Reset) {
            $this->writer->write($this->esc('39m'));

            return;
        }
        if ($action instanceof SetBackgroundColor && $action->color === Colors::Reset) {
            $this->writer->write($this->esc('49m'));

            return;
        }

        if ($action instanceof EnableMouseCapture) {
            $this->writer->write(implode('', array_map(fn (string $code): string => $this->esc($code), $action->enable ? [
                // Normal tracking: Send mouse X & Y on button press and release
                '?1000h',
                // Button-event tracking: Report button motion events (dragging)
                '?1002h',
                // Any-event tracking: Report all motion events
                '?1003h',
                // RXVT mouse mode: Allows mouse coordinates of >223
                '?1015h',
                // SGR mouse mode: Allows mouse coordinates of >223, preferred over RXVT mode
                '?1006h',
            ] : [
                // same as above but reversed
                '?1006h',
                '?1015h',
                '?1003h',
                '?1002h',
                '?1000h',
            ])));

            return;
        }

        $this->writer->write($this->esc(match (true) {
            $action instanceof SetForegroundColor => sprintf('%dm', $this->colorIndex($action->color, false)),
            $action instanceof SetBackgroundColor => sprintf('%dm', $this->colorIndex($action->color, true)),
            $action instanceof SetRgbBackgroundColor => sprintf('48;2;%d;%d;%dm', $action->r, $action->g, $action->b),
            $action instanceof SetRgbForegroundColor => sprintf('38;2;%d;%d;%dm', $action->r, $action->g, $action->b),
            $action instanceof CursorShow => sprintf('?25%s', $action->show ? 'h' : 'l'),
            $action instanceof AlternateScreenEnable => sprintf('?1049%s', $action->enable ? 'h' : 'l'),
            $action instanceof MoveCursor => sprintf('%d;%dH', $action->line, $action->col),
            $action instanceof Reset => '0m',
            $action instanceof Clear => match ($action->clearType) {
                ClearType::All => '2J',
                ClearType::Purge => '3J',
                ClearType::FromCursorDown => 'J',
                ClearType::FromCursorUp => '1J',
                ClearType::CurrentLine => '2K',
                ClearType::UntilNewLine => 'K',
            },
            $action instanceof SetModifier => $action->enable ?
                sprintf('%dm', $this->modifierOnIndex($action->modifier)) :
                sprintf('%dm', $this->modifierOffIndex($action->modifier)),
            $action instanceof RequestCursorPosition => '6n',
            default => throw new RuntimeException(sprintf(
                'Do not know how to handle action: %s',
                $action::class
            ))
        }));
    }

    private function colorIndex(Colors $termColor, bool $background): int
    {
        $offset = $background ? 10 : 0;

        return match ($termColor) {
            Colors::Black => 30,
            Colors::Red => 31,
            Colors::Green => 32,
            Colors::Yellow => 33,
            Colors::Blue => 34,
            Colors::Magenta => 35,
            Colors::Cyan => 36,
            Colors::Gray => 37,
            Colors::DarkGray => 90,
            Colors::LightRed => 91,
            Colors::LightGreen => 92,
            Colors::LightYellow => 93,
            Colors::LightBlue => 94,
            Colors::LightMagenta => 95,
            Colors::LightCyan => 96,
            Colors::White => 97,
            default => throw new RuntimeException(sprintf('Do not know how to handle color: %s', $termColor->name)),
        } + $offset;
    }

    private function modifierOnIndex(Attribute $modifier): int
    {
        return match($modifier) {
            Attribute::Reset => 0,
            Attribute::Bold => 1,
            Attribute::Dim => 2,
            Attribute::Italic => 3,
            Attribute::Underline => 4,
            Attribute::SlowBlink => 5,
            Attribute::RapidBlink => 6,
            Attribute::Hidden => 8,
            Attribute::Strike => 9,
            Attribute::Reverse => 7,
        };
    }

    private function modifierOffIndex(Attribute $modifier): int
    {
        return match($modifier) {
            Attribute::Reset => 0,
            Attribute::Bold => 22,
            Attribute::Dim => 22,
            Attribute::Italic => 23,
            Attribute::Underline => 24,
            Attribute::SlowBlink => 25,
            // same code as disabling slow blink according to crossterm
            Attribute::RapidBlink => 25,
            Attribute::Hidden => 28,
            Attribute::Strike => 29,
            Attribute::Reverse => 27,
        };
    }
    private function esc(string $action): string
    {
        return sprintf("\x1B[%s", $action);
    }
}
