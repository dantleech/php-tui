<?php

namespace DTL\PhpTui\Widget\Canvas\Shape;

use DTL\PhpTui\Model\Color;
use DTL\PhpTui\Model\Widget\FloatPosition;
use DTL\PhpTui\Widget\Canvas\Painter;
use DTL\PhpTui\Widget\Canvas\Shape;

final class Circle implements Shape
{
    public function __construct(
        public FloatPosition $position,
        public float $radius,
        public Color $color,
    ) {
    }

    public function draw(Painter $painter): void
    {
        foreach (range(0, 360) as $degree) {
            $radians = deg2rad($degree);
            $circleX = $this->radius * cos($radians) + $this->position->x;
            $circleY = $this->radius * sin($radians) + $this->position->y;
            if ($point = $painter->getPoint(FloatPosition::at($circleX, $circleY))) {
                $painter->paint($point, $this->color);
            }
        }
    }

    public static function fromPrimitives(float $x, float $y, float $radius, Color $color): self
    {
        return new self(FloatPosition::at($x, $y), $radius, $color);
    }
}
