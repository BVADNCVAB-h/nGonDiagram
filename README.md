### The class draws an n-gon graph with vertices determined by data inside a regular n-gon shape. The result is similar to diagrams in sport sim games like, for instance, football manager.

### Addition
```php
require_once 'ngondiagram.php';
```

### Instantiation with default parameters (Minimal number of vertices 3, maximal - 16).
```php
$diag = new nGonDiagram( $number_of_vertices );
```

### Addition of data
```php
/*
$lower_lim and $upper_lim define a range 
or allowed values of data
*/ 
$data = array( 8.1, 7.2, 6.7, 8.2, 9 );
$captions = array( 'a', 'b', 'c', 'd', 'e' );
$diag->AddData( $data, $lower_lim, $upper_lim, $captions );
/*
data is assigned from a left-most vertex belonging to  
the interval (-PI/2; 0] of the circumscribed circle
*/
```

### Supported output formats
```php
$supported_formats = array(
  'gif','jpeg', 'png', 'wbmp', 'xpm'
);
```

### Output to browser
```php
/* 
note that no other data should be sent
from the script, for example, by constructs such
as print or echo
*/
$diag->GetImage( 'png' );
```

### Output to file
```php
$diag->GetImage( 'png', $file_name );
```

### List of all available parameters set to their default values
```php
$args = array(
  'width' => 768,
  'height' => 640,
  'radius' => 200,
  'internal-scale' => 4,
  'output-scale' => 1,
  'center-x' => 0,
  'center-y' => 0,
  'rotation' => 0,
  'first-vertex' => 1,
  'anti-aliasing' => true,
  'bgcolor' => '#eeeeeeff',
  'inner-bgcolor' => null,         /*default: bgcolor*/
  'border-color' => '#337799',
  'border-width' => 4,
  'graduations' => 2,
  'graduation-width' => 1,
  'graduation-color' => null,      /*default: border-color*/
  'graduation-bgcolors' => null,   /*default: inner-bgcolor*/
  'with-rays' => true,
  'rays-width' => 1,
  'rays-color' => null,            /*default: border-color*/
  'centering' => true,
  'cache-grid' => false,
  'graph-color' => '#772222',
  'graph-width' => 4,
  'caption-font-size' => 16,
  'caption-color' => '#222222',
  'caption-margin' => null,        /*default: max(0.667 * caption-font-size, 10)*/
  'font-location' => null,         /*default: './DejaVuSans.ttf'*/
  'caption-line-margin' => 0,
);
```

### Instantiation with modified parameters
```php
$args = array(/*parameters, name-value pairs*/);
$diag = new nGonDiagram( $number_of_vertices, $args );
```

### Multiline captions
```php
/*
note that strings of multiline captions must be
double-quoted
*/
$captions = array( 
  "line1\nline2\nline3", 'b', 'c', 'd', 'e'
);
```

### Fonts
#### The output of textual information requires a TrueType font file to be accessible. By default, a file with name 'DejaVuSans.ttf' will be searched in the class's file directory.
```php
/**Selecting Arial font on Windows**/
$args['font-location'] = 'C:\\Windows\\Fonts\\Arial.ttf';
/**OR**/
putenv('GDFONTPATH=C:\\Windows\\Fonts');
$args['font-location'] = 'Arial';
/*********************************************/
/**Selecting bold DejaVu Sans-Serif on Linux (some)**/
$args['font-location'] = '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf';
/**OR**/
putenv('GDFONTPATH=/usr/share/fonts/truetype');
$args['font-location'] = 'dejavu/DejaVuSans-Bold';
```

### Performance / Quality
#### By default, the parameters are configured to produce smoother images. If it causes excessive resource consumption it is possible to shift the balance towards performance by lowering the value of 'internal-scale' and turning off 'anti-aliasing'. For instance, each lowering 'internal-scale' by 2 lessen the execution time by 4, and turning off 'anti-aliasing' lessen it additionally by around 8.
```php
/**this should work about 100 times faster as compared to default settings**/
$args = array(
  'internal-scale' => 1,
  'anti-aliasing' => false,
);
```

### Definition of resulting image dimensions
```php
/**the dimensions of an output image will be 1024 x 1024**/
$args = array(
  'width' => 512,
  'height' => 512,
  'output-scale' => 2,
);
```

### Alpha component of color
#### The alpha component of color is not taken into account if 'anti-aliasing'
is turned on.
```php
/**setting semitransparent background**/
$args = array(
  'anti-aliasing' => false,
  'bgcolor' => '#eeeeee77'
);
```

#### Some examples can be found [here](https://bvadncvab-h.github.io/n-gon-diagram-examples/index.html)
