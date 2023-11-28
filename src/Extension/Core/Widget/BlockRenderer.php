<?php

declare(strict_types=1);

namespace PhpTui\Tui\Extension\Core\Widget;

use PhpTui\Tui\Model\Area;
use PhpTui\Tui\Model\Display\Buffer;
use PhpTui\Tui\Model\HorizontalAlignment;
use PhpTui\Tui\Model\Position\Position;
use PhpTui\Tui\Model\Text\Title;
use PhpTui\Tui\Model\VerticalAlignment;
use PhpTui\Tui\Model\Widget;
use PhpTui\Tui\Model\Widget\Borders;
use PhpTui\Tui\Model\WidgetRenderer;

final class BlockRenderer implements WidgetRenderer
{
    public function render(WidgetRenderer $renderer, Widget $widget, Buffer $buffer): void
    {
        $area = $buffer->area();
        if (!$widget instanceof BlockWidget) {
            return;
        }
        if ($area->area() === 0) {
            return;
        }

        $this->renderBorders($widget, $area, $buffer);
        $this->renderTitles($widget, $area, $buffer);

        if ($widget->widget) {
            $innerArea = $widget->inner($area);
            $subBuffer = Buffer::empty($innerArea);
            $renderer->render(
                $renderer,
                $widget->widget,
                $subBuffer
            );
            $buffer->putBuffer($innerArea->position, $subBuffer);
        }
    }

    private function renderBorders(BlockWidget $block, Area $area, Buffer $buffer): void
    {
        $buffer->setStyle($area, $block->style);
        $lineSet = $block->borderType->lineSet();
        $bottom = max(0, $area->bottom() - 1);
        $right = max(0, $area->right() - 1);
        if ($block->borders & Borders::LEFT) {
            foreach (range($area->top(), $bottom) as $y) {
                $buffer->get(Position::at($area->left(), $y))
                    ->setStyle($block->borderStyle)
                    ->setChar($lineSet->vertical);
            }
        }
        if ($block->borders & Borders::TOP) {
            foreach (range($area->left(), $right) as $x) {
                $buffer->get(Position::at($x, $area->top()))
                    ->setStyle($block->borderStyle)
                    ->setChar($lineSet->horizontal);
            }
        }
        if ($block->borders & Borders::RIGHT) {
            $x = $right;
            foreach (range($area->top(), $bottom) as $y) {
                $buffer->get(Position::at($x, $y))
                    ->setStyle($block->borderStyle)
                    ->setChar($lineSet->vertical);
            }
        }
        if ($block->borders & Borders::BOTTOM) {
            $y = $bottom;
            foreach (range($area->left(), $area->right() - 1) as $x) {
                $buffer->get(Position::at($x, $y))
                    ->setStyle($block->borderStyle)
                    ->setChar($lineSet->horizontal);
            }
        }
        if ($block->borders & (Borders::RIGHT | Borders::BOTTOM)) {
            $buffer->get(Position::at($right, $bottom))
                ->setChar($lineSet->bottomRight)
                ->setStyle($block->borderStyle);
        }
        if ($block->borders & (Borders::RIGHT | Borders::TOP)) {
            $buffer->get(Position::at($right, $area->top()))
                ->setChar($lineSet->topRight)
                ->setStyle($block->borderStyle);
        }
        if ($block->borders & (Borders::LEFT | Borders::TOP)) {
            $buffer->get(Position::at($area->left(), $area->top()))
                ->setChar($lineSet->topLeft)
                ->setStyle($block->borderStyle);
        }
        if ($block->borders & (Borders::LEFT | Borders::BOTTOM)) {
            $buffer->get(Position::at($area->left(), $bottom))
                ->setChar($lineSet->bottomLeft)
                ->setStyle($block->borderStyle);
        }
    }

    private function renderTitles(BlockWidget $block, Area $area, Buffer $buffer): void
    {
        $this->renderTitlePosition($block, VerticalAlignment::Top, $area, $buffer);
        $this->renderTitlePosition($block, VerticalAlignment::Bottom, $area, $buffer);
    }

    private function renderTitlePosition(BlockWidget $block, VerticalAlignment $alignment, Area $area, Buffer $buffer): void
    {
        $this->renderRightTitles($block, $alignment, $area, $buffer);
        $this->renderCenterTitles($block, $alignment, $area, $buffer);
        $this->renderLeftTitles($block, $alignment, $area, $buffer);
    }

    private function renderRightTitles(BlockWidget $block, VerticalAlignment $alignment, Area $area, Buffer $buffer): void
    {
        [$_, $rightBorderDx, $titleAreaWidth] = $this->calculateTitleAreaOffsets($block, $area);
        $offset = $rightBorderDx;
        foreach (array_filter(
            $block->titles,
            function (Title $title) use ($alignment): bool {
                return
                    $title->horizontalAlignment === HorizontalAlignment::Right
                    && $title->verticalAlignment === $alignment;
            }
        ) as $title) {
            $offset += $title->title->width() + 1;
            $titleX = $offset - 1;

            $buffer->putLine(
                Position::at(
                    max(0, $area->width - $titleX) + $area->left(),
                    match ($alignment) {
                        VerticalAlignment::Bottom => max(0, $area->bottom() - 1),
                        VerticalAlignment::Top => $area->top(),
                    }
                ),
                $title->title,
                $titleAreaWidth,
            );
        }
    }

    private function renderLeftTitles(BlockWidget $block, VerticalAlignment $alignment, Area $area, Buffer $buffer): void
    {
        [$leftBorderDx, $_, $titleAreaWidth] = $this->calculateTitleAreaOffsets($block, $area);
        $offset = $leftBorderDx;
        foreach (array_filter(
            $block->titles,
            function (Title $title) use ($alignment): bool {
                return
                    $title->horizontalAlignment === HorizontalAlignment::Left
                    && $title->verticalAlignment === $alignment;
            }
        ) as $title) {
            $titleX = $offset;
            $offset += $title->title->width() + 1;

            foreach ($title->title->spans as $span) {
                $titleStyle = clone $block->titleStyle;
                $span->style = $titleStyle->patchStyle($span->style);
            }

            $buffer->putLine(
                Position::at(
                    $titleX + $area->left(),
                    match ($alignment) {
                        VerticalAlignment::Bottom => max(0, $area->bottom() - 1),
                        VerticalAlignment::Top => $area->top(),
                    }
                ),
                $title->title,
                $titleAreaWidth,
            );
        }
    }

    private function renderCenterTitles(BlockWidget $block, VerticalAlignment $alignment, Area $area, Buffer $buffer): void
    {
        [$_, $_, $titleAreaWidth] = $this->calculateTitleAreaOffsets($block, $area);
        $titles = array_filter(
            $block->titles,
            function (Title $title) use ($alignment): bool {
                return
                    $title->horizontalAlignment === HorizontalAlignment::Center
                    && $title->verticalAlignment === $alignment;
            }
        );
        $sumWidth = array_reduce(
            $titles,
            static fn (int $acc, Title $title): int => $acc + $title->title->width(),
            0
        );
        $offset = max(0, (int) (($area->width - $sumWidth) / 2));
        foreach ($titles as $title) {
            $titleX = $offset;
            $offset += $title->title->width() + 1;

            $buffer->putLine(
                Position::at(
                    $titleX + $area->left(),
                    match ($alignment) {
                        VerticalAlignment::Bottom => max(0, $area->bottom() - 1),
                        VerticalAlignment::Top => $area->top(),
                    }
                ),
                $title->title,
                $titleAreaWidth,
            );
        }
    }

    /**
     * @return array{int,int,int}
     */
    private function calculateTitleAreaOffsets(BlockWidget $block, Area $area): array
    {
        $leftBorderDx = (bool)($block->borders & Borders::LEFT);
        $rightBorderDx = (bool)($block->borders & Borders::RIGHT);

        return [
            $leftBorderDx ? 1 : 0,
            $rightBorderDx ? 1 : 0,
            $area->width - ($leftBorderDx || $rightBorderDx ? 1 : 0),
        ];
    }

}
