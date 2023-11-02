## ImageShape

Renders an image on the canvas.
### Parameters

Configure the shape using the constructor arguments named as follows:

| Name | Type | Description |
| --- | --- | --- |
| **image** | `Imagick` | Imagck to render (use `ImageShape::fromFilename` constructor) |
| **position** | `PhpTui\Tui\Model\Widget\FloatPosition` | Position to render at (bottom left) |
### Example
The following code example:

{{% codeInclude file="/data/example/docs/shape/imageShape.php" language="php" %}}

Should render as:

{{% terminal file="/data/example/docs/shape/imageShape.snapshot" %}}