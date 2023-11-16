<?php

namespace PhpTui\Tui\Extension\Core\Widget;

use PhpTui\Tui\Extension\Core\Widget\BarChart\Bar;
use PhpTui\Tui\Extension\Core\Widget\BarChart\BarGroup;
use PhpTui\Tui\Extension\Core\Widget\BarChart\LabelInfo;
use PhpTui\Tui\Model\Area;
use PhpTui\Tui\Model\Buffer;
use PhpTui\Tui\Model\Direction;
use PhpTui\Tui\Model\Exception\TodoException;
use PhpTui\Tui\Model\Position;
use PhpTui\Tui\Model\Style;
use PhpTui\Tui\Model\Widget;
use PhpTui\Tui\Model\WidgetRenderer;
use PhpTui\Tui\Model\Widget\BarSet;
use PhpTui\Tui\Model\Widget\HorizontalAlignment;

final class BarChartRenderer implements WidgetRenderer
{
    private const TICKS_PER_LINE = 8;

    public function render(WidgetRenderer $renderer, Widget $widget, Area $area, Buffer $buffer): void
    {
        if (!$widget instanceof BarChartWidget) {
            return;
        }
        $buffer->setStyle($area, $widget->style);

        if ($area->isEmpty() || $widget->data === [] || $widget->barWidth === 0) {
            return;
        }

        match($widget->direction) {
            Direction::Vertical => $this->renderVertical($widget, $buffer, $area),
            Direction::Horizontal => $this->renderHorizontal($widget, $buffer, $area),
        };

    }

    /**
     * @return list<list<int>>
     */
    private function groupTicks(BarChartWidget $widget, int $availableSpace, int $barMaxLength): array
    {
        $max = $this->maxDataValue($widget);
        $space = $availableSpace;
        $ticks = [];

        foreach ($widget->data as $group) {
            if ($space === 0) {
                return $ticks;
            }
            $nBars = count($group->bars);
            $groupWidth = $nBars * $widget->barWidth + max(0, $nBars - 1) * $widget->barGap;

            if ($space > $groupWidth) {
                $space = max(0, $space - $groupWidth + $widget->groupGap + $widget->barGap);
            } else {
                $maxBars = ($space + $widget->barGap) / ($widget->barWidth + $widget->barGap);
                if ($maxBars > 0) {
                    $space = 0;
                    $nBars = intval($maxBars);
                } else {
                    return $ticks;
                }
            }

            $bars = $group->bars;
            if ($nBars <= 0) {
                return $ticks;
            }

            $ticks[] = array_map(function (Bar $bar) use ($barMaxLength, $max) {
                    if ($max === 0) {
                        return 0;
                    }
                    return intval($bar->value * $barMaxLength * self::TICKS_PER_LINE / $max);
            }, array_slice($bars, 0, $nBars));
        }
        return $ticks;
    }

    private function maxDataValue(BarChartWidget $widget): int
    {
        if (null !== $widget->max) {
            return $widget->max;
        }

        return array_reduce($widget->data, function (int $max, BarGroup $barGroup) {
            $barGroupMax = $barGroup->max();
            if ($barGroupMax> $max) {
                return $barGroupMax;
            }
            return $max;
        }, 0);
    }

    private function renderVertical(BarChartWidget $widget, Buffer $buffer, Area $area): void
    {
        $labelInfo = $this->labelInfo($widget, $area->height - 1);
        $barsArea = Area::fromScalars(
            $area->position->x,
            $area->position->y,
            $area->width,
            $area->height - $labelInfo->height
        );

        $groupTicks = $this->groupTicks($widget, $area->width, $area->height);
        $this->renderVerticalBars($widget, $buffer, $barsArea, $groupTicks);
        $this->renderLabelsAndVAlues($widget, $area, $buffer, $labelInfo, $groupTicks);

    }

    private function labelInfo(BarChartWidget $widget, int $availableHeight): LabelInfo
    {
        if ($availableHeight === 0) {
            return new LabelInfo(groupLabelVisible: false, barLabelVisible: false, height: 0);
        }

        $barLabelVisible = $widget->isBarLabelVisible();

        if ($availableHeight === 1 && $barLabelVisible) {
            return new LabelInfo(
                groupLabelVisible: false,
                barLabelVisible: true,
                height: 1
            );
        }

        $groupLabelVisible = $widget->isGroupLabelVisible();

        return new LabelInfo(
            $groupLabelVisible,
            $barLabelVisible,
            ($groupLabelVisible ? 1 : 0) + ($barLabelVisible ? 1 : 0),
        );

    }

    /**
     * @param array<int,array<int,int>> $groupTicks
     */
    private function renderVerticalBars(BarChartWidget $widget, Buffer $buffer, Area $area, array $groupTicks): void
    {
        $barX = $area->left();
        foreach ($widget->data as $i => $group) {
            $ticksList = $groupTicks[$i];
            foreach ($group->bars as $ii => $bar) {
                $ticks = $ticksList[$ii];
                for ($j = $area->height - 1; $j >= 0; $j--) {
                    $symbol = BarSet::fromIndex($ticks);

                    $barStyle = $widget->barStyle->patch($bar->style);

                    for ($x = 0; $x < $widget->barWidth; $x++) {
                        $cell = $buffer->get(Position::at($barX + $x, $area->top() + $j));
                        $cell->setChar($symbol);
                        $cell->setStyle($barStyle);
                    }

                    $ticks = max(0, $ticks - self::TICKS_PER_LINE);
                }
                $barX += $widget->barGap + $widget->barWidth;
            }
            $barX += $widget->groupGap;
        }
    }

    /**
     * @param array<int,array<int,int>> $groupTicks
     */
    private function renderLabelsAndVAlues(BarChartWidget $widget, Area $area, Buffer $buffer, LabelInfo $labelInfo, array $groupTicks): void
    {
        $barX = $area->left();
        $barY = $area->bottom() - $labelInfo->height - 1;

        foreach ($widget->data as $i => $group) {
            if ([] === $group->bars) {
                continue;
            }
            $tickList = $groupTicks[$i];
            $bars = $group->bars;

            // print group labels under the bars or the previous labels
            if ($labelInfo->groupLabelVisible) {
                $labelMaxWidth = count($tickList) * ($widget->barWidth + $widget->barGap) - $widget->barGap;
                $groupArea = Area::fromScalars(
                    $barX,
                    $area->bottom() - 1,
                    $labelMaxWidth,
                    1,
                );
                $this->renderGroupLabel($group, $buffer, $groupArea, $widget->labelStyle);
            }

            foreach ($group->bars as $ii => $bar) {
                $ticks = $tickList[$ii];
                if ($labelInfo->barLabelVisible) {
                    $this->renderBarLabel($bar, $buffer, $widget->barWidth, $barX, $barY + 1, $widget->labelStyle);
                }
                $this->renderBarValue($bar, $buffer, $widget->barWidth, $barX, $barY, $widget->valueStyle, $ticks);
                $barX += $widget->barGap + $widget->barWidth;
            }
            $barX += $widget->groupGap;
        }
    }

    private function renderGroupLabel(BarGroup $group, Buffer $buffer, Area $area, Style $style): void
    {
        $label = $group->label;
        if ($label === null) {
            return;
        }

        foreach ($label->spans as $span) {
            $span->style = $style->patch($span->style);
        }

        $xOffset = match ($label->alignment) {
            HorizontalAlignment::Center => max(0, $area->width - $label->width()) >> 1,
            HorizontalAlignment::Right => max(0, $area->width - $label->width()),
            default => 0,
        };

        $buffer->putLine(
            $area->position->withX($area->position->x + $xOffset),
            $label,
            $area->width
        );
    }

    private function renderBarLabel(Bar $bar, Buffer $buffer, int $maxWidth, int $x, int $y, Style $defaultStyleLabel): void
    {
        $label = $bar->label;
        if (null === $label) {
            return;
        }

        foreach ($label->spans as $span) {
            $span->style = $defaultStyleLabel->patch($span->style);
        }

        $buffer->putLine(
            Position::at(
                $x + (max(0, $maxWidth - $label->width()) >> 1),
                $y,
            ),
            $label,
            $maxWidth
        );
    }

    private function renderBarValue(Bar $bar, Buffer $buffer, int $maxWidth, int $x, int $y, Style $defaultValueStyle, int $ticks): void
    {
        $valueLabel = $bar->textValue ? $bar->textValue : (string)$bar->value;
        $width = mb_strlen($valueLabel);
        if ($width <= $maxWidth || ($width === $maxWidth && $ticks >= self::TICKS_PER_LINE)) {
            // why strlen? Ratatui does value_label.len() not sure why.
            $buffer->putString(
                Position::at(
                    $x + (max(0, $maxWidth - strlen($valueLabel)) >> 1),
                    $y,
                ),
                $valueLabel,
                $defaultValueStyle->patch($bar->valueStyle),
            );
        }
    }

    private function renderHorizontal(BarChartWidget $widget, Buffer $buffer, Area $area)
    {
        $labelSize = $widget->maxLabelSize();
        $labelX = $area->position->x;
        $margin = $labelSize === 0 ? 0 : 1;
        $barsArea = Area::fromScalars(
            $area->position->x + $labelSize + $margin,
            $area->position->y,
            $area->width - $labelSize - $margin,
            $area->height
        );

        $groupTicks = $this->groupTicks($widget, $barsArea->height, $barsArea->width);
        $barY = $barsArea->top();

        foreach ($widget->data as $i => $group) {
            $tickList = $groupTicks[$i];
            foreach ($group->bars as $ii => $bar) {
                $ticks = $tickList[$ii];
                $barLength = intval($ticks / 8);
                $barStyle = $widget->barStyle->patch($bar->style);

                for ($y = 0; $y < $widget->barWidth; $y++) {
                    $barY = $barY + $y;
                    for ($x =0; $x < $barsArea->width; $x++) {
                        $symbol = $x < $barLength ? BarSet::FULL : BarSet::EMPTY;
                        $buffer->get(
                            Position::at(
                                $barsArea->left() + $x,
                                $barY,
                            )
                        )->setChar($symbol)->setStyle($barStyle);
                    }
                }
                $barValueArea = Area::fromScalars(
                    $barsArea->position->x,
                    $barY + ($widget->barWidth >> 1),
                    $barsArea->width,
                    $barsArea->height
                );

                if ($bar->label !== null) {
                    $buffer->putLine(
                        Position::at($labelX,$barValueArea->top()),
                        $bar->label,
                        $labelSize,
                    );
                }
                $this->renderBarValueWithDifferentStyles(
                    $bar,
                    $buffer,
                    $barValueArea,
                    $barLength,
                    $widget->valueStyle,
                    $widget->barStyle,
                );
                $barY += $widget->barGap + $widget->barWidth;
            }
            // if group_gap is zero, then there is no place to print the group label
            // check also if the group label is still inside the visible area
            $labelY = $barY - $widget->barGap;
            if ($widget->groupGap > 0 && $labelY < $barsArea->bottom()) {
                $labelRect = $barsArea->withY($labelY);
                $this->renderGroupLabel($group, $buffer, $labelRect, $widget->labelStyle);
                $barY += $widget->groupGap;
            }
        }
    }

    private function renderBarValueWithDifferentStyles(
        Bar $bar,
        Buffer $buffer,
        Area $area,
        int $barLength,
        Style $defaultValueStyle,
        Style $barStyle
    ): void
    {
        $text = $bar->textValue ? $bar->textValue : (string)$bar->value;
        if (!$text) {
            return;
        }

        $style = $defaultValueStyle->patch($bar->valueStyle);
        $buffer->putString($area->position, $text, $style, $barLength);
        if (mb_strlen($text) > $barLength) {
            $first = substr($text, 0, $barLength);
            $second = substr($text, $barLength);
            $style = $barStyle->patch($bar->style);
            $buffer->putString(
                Position::at(
                    $area->position->x + mb_strlen($first),
                    $area->position->y,
                ),
                $second,
                $style,
                $area->width - mb_strlen($first),
            );
        }
    }
}
