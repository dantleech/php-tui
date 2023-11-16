---
title:  Text 
description: Renders text on the canvas.This widget requires a bitmap font in the BDF format.You can use the `PhpTui\Tui\Adapter\Bdf\FontRegistry` to load and manage fonts. It has a default font built in.
---
##  Text 

`PhpTui\Tui\Extension\Bdf\Shape\TextShape`

Renders text on the canvas.This widget requires a bitmap font in the BDF format.You can use the `PhpTui\Tui\Adapter\Bdf\FontRegistry` to load and manage fonts. It has a default font built in.
{{% terminal file="/data/example/docs/shape/textShape.html" %}}
{{< details "Show code"  >}}
{{% codeInclude file="/data/example/docs/shape/textShape.php" language="php" %}}

{{< /details >}}
### Parameters

Configure the shape using the constructor arguments named as follows:

| Name | Type | Description |
| --- | --- | --- |
| **font** | `string` | Font name as it is known in the font registry |
| **text** | `string` | Text to render |
| **color** | `PhpTui\Tui\Model\Color` | Color of the text |
| **position** | `PhpTui\Tui\Model\Widget\FloatPosition` | Position of the text (bottom left corner) |
| **scaleX** | `float` | Horizontal scale of the font |
| **scaleY** | `float` | Verttical scale of the font |