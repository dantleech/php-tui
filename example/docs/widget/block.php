<?php

use PhpTui\Tui\Adapter\PhpTerm\PhpTermBackend;
use PhpTui\Tui\Model\Display;
use PhpTui\Tui\Model\Widget\BorderType;
use PhpTui\Tui\Model\Widget\Borders;
use PhpTui\Tui\Model\Widget\Text;
use PhpTui\Tui\Model\Widget\Title;
use PhpTui\Tui\Widget\Block;
use PhpTui\Tui\Widget\Paragraph;

require 'vendor/autoload.php';

$display = Display::fullscreen(PhpTermBackend::new());
$display->drawWidget(
    Block::default()
        ->borders(Borders::ALL)
        ->titles(Title::fromString('Hello World'))
        ->borderType(BorderType::Rounded)
        ->widget(Paragraph::fromText(Text::fromString('This is a block example')))
);
