<?php

namespace PhpTui\Tui\Tests\Example;

use PHPUnit\Framework\TestCase;
use PhpTui\Term\Event;
use PhpTui\Term\EventProvider\LoadedEventProvider;
use PhpTui\Term\Event\CharKeyEvent;
use PhpTui\Term\Terminal;
use PhpTui\Tui\Example\Demo\App;
use PhpTui\Tui\Model\Backend\DummyBackend;
use PhpTui\Tui\Model\Display;
use RuntimeException;

class DemoTest extends TestCase
{
    public function testHome(): void
    {
        $backend = $this->execute(
            null,
            CharKeyEvent::new('q'),
        );
        $this->assertSnapshot(__METHOD__, $backend);
    }

    public function testCanvas(): void
    {
        $backend = $this->execute(
            CharKeyEvent::new('2'),
            null,
            CharKeyEvent::new('q'),
        );
        $this->assertSnapshot(__METHOD__, $backend);
    }

    public function testChart(): void
    {
        $backend = $this->execute(
            CharKeyEvent::new('3'),
            null,
            CharKeyEvent::new('q'),
        );
        $this->assertSnapshot(__METHOD__, $backend);
    }

    public function testList(): void
    {
        $backend = $this->execute(
            CharKeyEvent::new('4'),
            null,
            CharKeyEvent::new('q'),
        );
        $this->assertSnapshot(__METHOD__, $backend);
    }

    public function testTable(): void
    {
        srand(0);
        $backend = $this->execute(
            CharKeyEvent::new('5'),
            null,
            CharKeyEvent::new('q'),
        );
        $this->assertSnapshot(__METHOD__, $backend);
    }

    private function execute(?Event ...$events): DummyBackend
    {
        $terminal = Terminal::new(
            eventProvider: LoadedEventProvider::fromEvents(
                ...$events
            ),
        );

        $backend = DummyBackend::fromDimensions(80, 20);
        $app = App::new($terminal, Display::fullscreen($backend));
        $app->run();
        return $backend;
    }

    private function assertSnapshot(string $name, DummyBackend $backend): void
    {
        $name = substr($name, strrpos($name, ':') + 5);
        $filename = sprintf('%s/snapshot/%s.snapshot', __DIR__ , $name);
        if (!file_exists($filename) || getenv('SNAPSHOT_APPROVE')) {
            file_put_contents($filename, $backend->flushed());
        }

        $existing = file_get_contents($filename);
        if (false === $existing) {
            throw new RuntimeException('Could not get file contents');
        }

        self::assertEquals($existing, $backend->toString(), 'Snapshot matches');
    }
}
