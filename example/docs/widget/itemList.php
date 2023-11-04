<?php

use PhpTui\Tui\Adapter\PhpTerm\PhpTermBackend;
use PhpTui\Tui\Model\Buffer;
use PhpTui\Tui\Model\Display;
use PhpTui\Tui\Model\Widget\Text;
use PhpTui\Tui\Widget\ItemList;
use PhpTui\Tui\Widget\ItemList\ItemListState;
use PhpTui\Tui\Widget\ItemList\ListItem;

require 'vendor/autoload.php';

$display = Display::fullscreen(PhpTermBackend::new());
$display->draw(function (Buffer $buffer): void {
    ItemList::default()
        ->highlightSymbol('😼')
        ->state(new ItemListState(0, 2))
        ->items(
            ListItem::new(Text::raw('Item one')),
            ListItem::new(Text::raw('Item two')),
            ListItem::new(Text::raw('Item three')),
            ListItem::new(Text::raw('Item four')),
        )
        ->render($buffer->area(), $buffer);
});
