<?php

declare(strict_types=1);

use PhpTui\Tui\DisplayBuilder;
use PhpTui\Tui\Extension\Core\Widget\BlockWidget;
use PhpTui\Tui\Extension\Core\Widget\ParagraphWidget;
use PhpTui\Tui\Model\Borders;
use PhpTui\Tui\Model\BorderType;
use PhpTui\Tui\Model\Text\Text;
use PhpTui\Tui\Model\Text\Title;

require 'vendor/autoload.php';

$display = DisplayBuilder::default()->build();
$display->draw(
    BlockWidget::default()
        ->borders(Borders::ALL)
        ->titles(Title::fromString('Hello World'))
        ->borderType(BorderType::Rounded)
        ->widget(ParagraphWidget::fromText(Text::fromString('This is a block example')))
);
