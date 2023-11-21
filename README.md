PHP TUI
=======

[![CI](https://github.com/php-tui/php-tui/actions/workflows/ci.yml/badge.svg)](https://github.com/php-tui/php-tui/actions/workflows/ci.yml)

<center>
  <img src="https://github.com/php-tui/php-tui/assets/530801/461a654c-e3fc-4073-b350-097a7b5c7325" alt="PHP TUI Logo"/>
</center>

PHP TUI library heavily inspired by Rust TUI /
[Ratatui](https://github.com/ratatui-org/ratatui).

![Demo Gif](https://vhs.charm.sh/vhs-1S7kcYHesqfxhLp5PyWbAj.gif)

Features
--------

- Many core features from Ratatui.
- Advanced terminal control heavily inspired by Rust's [crossterm](https://github.com/crossterm-rs/crossterm) library.
- Font rendering
- Layout control using the Cassowary algorithm via. [php-tui cassowary](https://github.com/php-tui/cassowary)

Installation
------------

Require in your project with composer:

```
composer require php-tui/php-tui
```

Documentation
-------------

Read the [documentation here](https://php-tui.github.io/php-tui).

Demo
----

Checkout the project and run:

```
./example/demo/bin/demo
```

Screenshots
-----------

![elephants starfield](https://github.com/php-tui/php-tui/assets/530801/3f063a8d-1589-477c-b9d6-21c60a907e48)
<p><i>Page from the PHP-TUI demo showing a scaled, scrolling text on a canvas with elephants on a starfield (framerate reduced for GIF)</i></p>

![image](https://github.com/php-tui/php-tui/assets/530801/cabe761f-9f4b-4c3f-8d2f-a63f059b47fa)
<p><i>Image rendering via. image magick</i></p>

![image](https://github.com/php-tui/php-tui/assets/530801/4d39f63b-8192-48ee-b66b-ed817cde1068)

<p><i>Colors demo</i></p>

Widgets
-------

- [x] Canvas
- [x] Chart
- [x] Block
- [x] Paragraph
- [x] List
- [x] Table
- [ ] Tabs
- [ ] Calendar
- [x] Barchart
- [ ] Scrollbar
- [ ] Sparkline
- [x] Gauge

Limitations
-----------

- Windows support: shouldn't be hard to implement, but I don't have windows so...

TODO
----

- [ ] Grapheme support
- [ ] Cursor positioning after after draw (removed "frame" concept)
- [ ] Change modifiers to use bitmask
- [ ] Layout SegmentSize
- [ ] Chart legend
