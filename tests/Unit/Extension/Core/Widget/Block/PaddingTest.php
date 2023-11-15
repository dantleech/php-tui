<?php

declare(strict_types=1);

namespace PhpTui\Tui\Tests\Unit\Extension\Core\Widget\Block;

use PhpTui\Tui\Extension\Core\Widget\Block\Padding;
use PHPUnit\Framework\TestCase;

class PaddingTest extends TestCase
{
    public function testAll(): void
    {
        $all = Padding::all(2);
        self::assertEquals(2, $all->top);
        self::assertEquals(2, $all->bottom);
        self::assertEquals(2, $all->left);
        self::assertEquals(2, $all->right);
    }
}
