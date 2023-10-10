<?php

namespace DTL\PhpTui\Widget;

use DTL\PhpTui\Model\Area;
use DTL\PhpTui\Model\Buffer;
use DTL\PhpTui\Model\Widget\Borders;
use DTL\PhpTui\Model\Widget\Title;

final class Block
{

    /**
     * @param int-mask-of<Borders::*> $borders
     * @param Title[] $titles
     */
    public function __construct(
        private int $borders,
        private array $titles,
    )
    {
    }

    public static function default(): self
    {
        return new self(Borders::NONE, []);
    }

    public function inner(Area $area): Area
    {
        $x = $area->position->x;
        $y = $area->position->y;
        $width = $area->width;
        $height = $area->height;
        if ($this->borders & Borders::LEFT) {
            $x = min($x + 1, $area->right());
            $width = max(0, $width - 1);
        }
        if ($this->borders & Borders::TOP || [] !== $this->titles) {
            $y = min($y + 1, $area->bottom());
            $height = max(0, $height-1);
        }
        if ($this->borders & Borders::RIGHT) {
            $width = max(0, $width - 1);
        }
        if ($this->borders & Borders::BOTTOM) {
            $height = max(0, $height - 1);
        }

        return Area::fromPrimitives($x, $y, $width, $height);
    }

    /**
     * @param int-mask-of<Borders::*> $flag
     */
    public function borders(int $flag): self
    {
        $this->borders = $flag;
        return $this;
    }

    public function title(Title $title): self
    {
        $this->titles[] = $title;
        return $this;
    }

    public function render(Area $area, Buffer $buffer): void
    {
    }
}
