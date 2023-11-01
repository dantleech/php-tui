## TextShape

Renders text on the canvas.This widget requires a bitmap font in the BDF format.You can use the `PhpTui\Tui\Adapter\Bdf\FontRegistry` to load and manage fonts. It has a default font built in.
### Parameters

Configure the shape using the constructor arguments named as follows:

| Name | Type | Description |
| --- | --- | --- |
| **font** | `PhpTui\BDF\BdfFont` | BDF font |
| **text** | `string` | Text to render |
| **color** | `PhpTui\Tui\Model\Color` | Color of the text |
| **position** | `PhpTui\Tui\Model\Widget\FloatPosition` | Position of the text (bottom left corner) |
### Example
The following code example:

{{% codeInclude file="/data/example/docs/shape/textShape.php" language="php" %}}

Should render as:

{{% terminal file="/data/example/docs/shape/textShape.snapshot" %}}