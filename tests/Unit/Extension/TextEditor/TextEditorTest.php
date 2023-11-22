<?php

namespace PhpTui\Tui\Tests\Unit\Extension\TextEditor;

use OutOfRangeException;
use PHPUnit\Framework\TestCase;
use PhpTui\Tui\Extension\TextEditor\TextEditor;
use PhpTui\Tui\Model\Position\Position;

class TextEditorTest extends TestCase
{
    public function testInsert(): void
    {
        $editor = TextEditor::fromString('');
        $editor->insert('H');
        $editor->insert('ello');
        $editor->insert(' World');
        self::assertEquals('Hello World', $editor->toString());

        $editor = TextEditor::fromString('Hello World');
        $editor->insert('Hello ');
        self::assertEquals('Hello Hello World', $editor->toString());

        $editor = TextEditor::fromString('');
        $editor->insert('Hello🐈Cat');
        $editor->insert('Hai');
        self::assertEquals('Hello🐈CatHai', $editor->toString());
    }

    public function testInsertReplace(): void
    {
        $editor = TextEditor::fromString('Hello World');
        $editor->insert('World', 5);

        self::assertEquals('World World', $editor->toString());

        $editor->insert(' Space', 6);
        self::assertEquals('World Space', $editor->toString());
    }

    public function testInsertReplaceNegativeLength(): void
    {
        $this->expectException(OutOfRangeException::class);
        $editor = TextEditor::fromString('Hello World');
        /** @phpstan-ignore-next-line */
        $editor->insert('World', -5);
    }

    public function testDelete(): void
    {
        $editor = TextEditor::fromString('');
        $editor->insert('Hello');
        $editor->delete();
        self::assertEquals('Hell', $editor->toString());
        $editor->delete();
        self::assertEquals('Hel', $editor->toString());

        $editor->delete(2);
        self::assertEquals('H', $editor->toString());

        $editor = TextEditor::fromString('');
        $editor->insert('Hello🐈Cat');
        $editor->delete(4);
        self::assertEquals('Hello', $editor->toString());
    }

    public function testNavigate(): void
    {
        $editor = TextEditor::fromLines([
            '0123',
            '01234567',
        ]); 

        self::assertEquals(Position::at(0, 0), $editor->cursorPosition());

        $editor->cursorLeft();
        self::assertEquals(Position::at(0, 0), $editor->cursorPosition());

        $editor->cursorRight();
        self::assertEquals(Position::at(1, 0), $editor->cursorPosition());
        $editor->cursorRight();
        self::assertEquals(Position::at(2, 0), $editor->cursorPosition());
        $editor->cursorRight();
        self::assertEquals(Position::at(3, 0), $editor->cursorPosition());
        $editor->cursorRight();
        self::assertEquals(Position::at(4, 0), $editor->cursorPosition());
        $editor->cursorRight();
        self::assertEquals(Position::at(4, 0), $editor->cursorPosition());

        $editor->cursorLeft();
        $editor->cursorLeft();
        $editor->cursorLeft();
        $editor->cursorLeft();
        self::assertEquals(Position::at(0, 0), $editor->cursorPosition());

        $editor->cursorDown();
        self::assertEquals(Position::at(0, 1), $editor->cursorPosition());

        $editor->cursorDown();
        self::assertEquals(Position::at(0, 1), $editor->cursorPosition());

        $editor->cursorUp();
        $editor->cursorUp();
        self::assertEquals(Position::at(0, 0), $editor->cursorPosition());
    }
}
