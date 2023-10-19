<?php

namespace DTL\PhpTui\Widget\Chart;

use DTL\PhpTui\Model\AxisBounds;

final class Axis
{
    public function __construct(public AxisBounds $bounds)
    {
    }
    public static function default(): self
    {
        return new self(AxisBounds::default());
    }
}
