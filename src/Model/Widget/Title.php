<?php

declare(strict_types=1);

namespace PhpTui\Tui\Model\Widget;

use PhpTui\Tui\Model\Parseable;

final class Title implements Parseable
{
    private function __construct(
        public Line $title,
        public HorizontalAlignment $horizontalAlignment,
        public VerticalAlignment $verticalAlignment
    ) {
    }

    public static function fromString(string $string): self
    {
        return new self(Line::fromString($string), HorizontalAlignment::Left, VerticalAlignment::Top);
    }

    public static function parse(string $string): self
    {
        return new self(Line::parse($string), HorizontalAlignment::Left, VerticalAlignment::Top);
    }

    public function horizontalAlignmnet(HorizontalAlignment $alignment): self
    {
        $this->horizontalAlignment = $alignment;

        return $this;
    }

    public static function fromLine(Line $line): self
    {
        return new self($line, HorizontalAlignment::Left, VerticalAlignment::Top);
    }

    public function verticalAlignment(VerticalAlignment $alignment): self
    {
        $this->verticalAlignment = $alignment;

        return $this;
    }

    public function horizontalAlignment(HorizontalAlignment $alignment): self
    {
        $this->horizontalAlignment = $alignment;

        return $this;
    }
}
