<?php

declare(strict_types=1);

namespace PhpTui\Tui\Model\Display;

use Countable;
use OutOfBoundsException;
use PhpTui\Tui\Model\Area;
use PhpTui\Tui\Model\Position\Position;
use PhpTui\Tui\Model\Style;
use PhpTui\Tui\Model\Text\Line;
use PhpTui\Tui\Model\Text\Span;
use Stringable;

final class Buffer implements Countable, Stringable
{
    /**
     * @param Cell[] $content
     */
    private function __construct(
        private Area $area,
        private array $content
    ) {
    }

    public function __toString(): string
    {
        $out = [];
        foreach ($this->content as $cell) {
            $out[] = $cell->__toString();
        }

        return implode("\n", $out);
    }

    public static function empty(Area $area): self
    {
        return self::filled($area, Cell::empty());
    }

    public static function filled(Area $area, Cell $cell): self
    {
        $content = [];
        for ($i = 0; $i < $area->area(); $i++) {
            $content[] = clone $cell;
        }

        return new self($area, $content);
    }

    public function setStyle(?Area $area, Style $style): void
    {
        if ($area === null) {
            $area = $this->area;
        }
        if ($area->height === 0) {
            return;
        }
        foreach (range($area->top(), $area->bottom() - 1) as $y) {
            foreach (range($area->left(), $area->right() - 1) as $x) {
                $this->get(Position::at($x, $y))->setStyle($style);
            }
        }
    }

    /**
     * @param string[] $lines
     */
    public static function fromLines(array $lines): self
    {
        $height = count($lines);
        $width = array_reduce(
            $lines,
            static fn ($acc, $line) => mb_strwidth($line) > $acc ? mb_strwidth($line) : $acc,
            0
        );

        $buffer = self::empty(Area::fromScalars(0, 0, $width, $height));
        foreach (array_values($lines) as $y => $line) {
            /** @phpstan-ignore-next-line */
            $buffer->putString(new Position(0, $y), $line, Style::default());
        }

        return $buffer;
    }

    /**
     * @return Cell[]
     */
    public function content(): array
    {
        return $this->content;
    }

    public function diff(Buffer $buffer): BufferUpdates
    {
        $previous = $this->content();
        $next = $buffer->content();
        $updates = [];
        $counter = count($next);
        $toSkip = 0;

        for ($i = 0; $i < $counter; $i++) {
            $previousCell = $previous[$i];
            $currentCell = $next[$i];
            if (false === $previousCell->equals($currentCell) && $toSkip === 0) {
                $updates[] = new BufferUpdate(Position::fromIndex($i, $this->area), $currentCell);
            }

            // unicode chars can have 0, 1 or 2 width, so this will only
            // ever be 0 or 1
            $toSkip = max(0, mb_strwidth($currentCell->char) - 1);
        }

        return new BufferUpdates($updates);

    }

    /**
     * Return the number of cells.
     */
    public function count(): int
    {
        return count($this->content);
    }

    public function toString(): string
    {
        $string = '';
        $toSkip = 0;
        foreach ($this->content as $i => $cell) {
            if ($toSkip === 0) {
                if ($i > 0 && $i % $this->area->width === 0) {
                    $string .= "\n";
                }

                $string .= $cell->char;
            }
            $toSkip = max(0, mb_strwidth($cell->char) - 1);
        }

        return $string;
    }

    public function putString(Position $position, string $line, ?Style $style = null, int $width = PHP_INT_MAX): Position
    {
        $style ??= Style::default();

        try {
            $index = $position->toIndex($this->area);
        } catch (OutOfBoundsException) {
            return $position;
        }
        $chars = mb_str_split($line, 1);
        $xOffset = $position->x;
        $chars = array_slice(
            $chars,
            0,
            min($width, count($this->content))
        );
        foreach ($chars as $char) {
            if ($index >= count($this->content)) {
                break;
            }

            $width = mb_strwidth($char);

            // note the above function doesn't return 0 so this check
            // is technically never going to pass, but it _should_ return 0
            if ($width === 0) {
                continue;
            }
            $this->content[$index]->setChar($char);
            $this->content[$index]->setStyle($style);

            for ($i = 1; $i < $width; $i++) {
                $this->content[$i + $index] = Cell::empty();
            }
            $index += $width;
            $xOffset += $width;
        }

        return $position->withX($xOffset);
    }

    /**
     * @return string[]
     */
    public function toLines(): array
    {
        $text = $this->toString();

        return explode("\n", $text);
    }

    public function area(): Area
    {
        return $this->area;
    }

    public function putLine(Position $position, Line $line, int $width): Position
    {
        $remainingWidth = $width;
        $x = $position->x;
        foreach ($line as $span) {
            if ($remainingWidth === 0) {
                return $position;
            }
            $newPosition = $this->putString(
                $position,
                $span->content,
                $span->style,
                $remainingWidth,
            );
            $w = max(0, $position->x - $newPosition->x);
            $position = $position->withX($newPosition->x);
            $remainingWidth -= max(0, $remainingWidth - $position->x);
        }

        return $position;
    }

    public function putSpan(Position $position, Span $span, int $width): Position
    {
        return $this->putString(
            $position,
            $span->content,
            $span->style,
            $width,
        );
    }

    public function get(Position $position): Cell
    {
        $index = $position->toIndex($this->area);

        return $this->content[$index];
    }

    /**
     * Insert the contents of the given buffer at the given position.
     */
    public function putBuffer(Position $position, Buffer $buffer): void
    {
        $bArea = $buffer->area();
        $area = $this->area();

        foreach ($buffer->content as $bi => $cell) {
            $y = $position->y + (int) (floor($bi / $bArea->width));
            $x = $position->x + ($bi % $bArea->width);
            if ($y > $area->bottom()) {
                continue;
            }
            if ($x > $area->right()) {
                continue;
            }
            $this->content[Position::at($x, $y)->toIndex($this->area())] = $cell;
        }
    }

    public function toUpdates(): BufferUpdates
    {
        $updates = [];
        foreach ($this->content as $offset => $cell) {
            /** @phpstan-ignore-next-line */
            $position = Position::fromIndex($offset, $this->area());
            $updates[] = new BufferUpdate($position, $cell);
        }

        return new BufferUpdates($updates);
    }

    /**
     * @return string[]
     */
    public function toChars(): array
    {
        return array_map(static fn (Cell $cell): string => $cell->char, $this->content);
    }
}
