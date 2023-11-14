<?php

namespace PhpTui\Tui\Tests\Model;

use PhpTui\Tui\Model\AnsiColor;
use PhpTui\Tui\Model\Modifier;
use PhpTui\Tui\Model\Style;
use PHPUnit\Framework\TestCase;

class StyleTest extends TestCase
{
    public function testDefault(): void
    {
        $style = Style::default();

        self::assertNull($style->fg);
        self::assertNull($style->bg);
        self::assertNull($style->underline);
        self::assertEquals(Modifier::None->value, $style->addModifiers);
        self::assertEquals(Modifier::None->value, $style->subModifiers);
    }

    public function testFg(): void
    {
        $style = Style::default()->fg(AnsiColor::Red);

        self::assertSame(AnsiColor::Red, $style->fg);
    }

    public function testBg(): void
    {
        $style = Style::default()->bg(AnsiColor::Blue);

        self::assertSame(AnsiColor::Blue, $style->bg);
    }

    public function testAddModifier(): void
    {
        $style = Style::default()->addModifier(Modifier::Bold);

        self::assertTrue(($style->addModifiers & Modifier::Bold->value) === Modifier::Bold->value);
    }

    public function testSubModifier(): void
    {
        $style = Style::default()->removeModifier(Modifier::Italic);

        self::assertTrue(($style->subModifiers & Modifier::Italic->value) === Modifier::Italic->value);
    }

    public function testPatch(): void
    {
        $style1 = Style::default()->bg(AnsiColor::Red);
        $style2 = Style::default()
                    ->fg(AnsiColor::Blue)
                    ->addModifier(Modifier::Bold)
                    ->addModifier(Modifier::Underlined);

        $combined = $style1->patch($style2);

        self::assertEquals(Modifier::None->value, $combined->subModifiers);

        self::assertEquals(
            Modifier::Bold->value | Modifier::Underlined->value,
            $combined->addModifiers,
        );

        self::assertSame(
            (string) Style::default()->patch($style1)->patch($style2),
            (string) $combined
        );

        self::assertSame(AnsiColor::Blue, $combined->fg);
        self::assertSame(AnsiColor::Red, $combined->bg);

        $combined2 = Style::default()->patch($combined)->patch(
            Style::default()
                ->removeModifier(Modifier::Bold)
                ->addModifier(Modifier::Italic),
        );

        self::assertEquals(Modifier::Bold->value, $combined2->subModifiers);

        self::assertEquals(
            Modifier::Italic->value | Modifier::Underlined->value,
            $combined2->addModifiers,
        );

        self::assertSame(AnsiColor::Blue, $combined->fg);
        self::assertSame(AnsiColor::Red, $combined->bg);
    }

    public function testToString(): void
    {
        $style = Style::default()
                    ->bg(AnsiColor::Red)
                    ->underline(AnsiColor::Blue)
                    ->addModifier(Modifier::Bold)
                    ->removeModifier(Modifier::Italic)
                    ->removeModifier(Modifier::Underlined);

        $expectedString = sprintf(
            'Style(fg:%s,bg: %s,u:%s,+mod:%d,-mod:%d)',
            '-',
            AnsiColor::Red->debugName(),
            AnsiColor::Blue->debugName(),
            Modifier::Bold->value,
            Modifier::Italic->value | Modifier::Underlined->value,
        );

        self::assertEquals($expectedString, (string) $style);
    }
}
