<?php
  $examples_code = array();
  $examples_code[0] = <<<'MLT'
$data = array( 8, 5, 6, 8, 9, 7 );
$captions = array( 
  'A', 'B', 'C', 
  'D', 'E', 'F' 
);
$diag = new nGonDiagram( 6 );
$diag->AddData( $data, 1, 10, $captions );
MLT;
  $examples_code[1] = <<<'MLT'
$args = array(
  'width' => 768,
  'height' => 640,
  'radius' => 200,
  'graduations' => 0,
);
$data = array( 8, 5, 6 );
$captions = array( 'A', 'B', 'C' );
$diag = new nGonDiagram( 3, $args );
$diag->AddData( $data, 1, 10, $captions );
MLT;
  $examples_code[2] = <<<'MLT'
$args = array(
  'width' => 768,
  'height' => 640,
  'radius' => 200,
  'internal-scale' => 0.5,
  'anti-aliasing' => false,
  'graduations' => 0,
  'centering' => false,
);
$data = array( 8, 5, 6 );
$captions = array( 'A', 'B', 'C' );
$diag = new nGonDiagram( 3, $args );
$diag->AddData( $data, 1, 10, $captions );
MLT;
  $examples_code[3] = <<<'MLT'
$args = array(
  'caption-font-size' => 28,
  'caption-line-margin' => 4,
  'caption-margin' => 24,
);
$data = array( 8, 5, 6, 8, 9, 7, 8, 9 );
$captions = array( 
  "axis\na", "axis\nb", "axis\nc",
  "axis\nd", "axis\ne", "axis\nf",
  "axis\ng", "axis\nh",
);
$diag = new nGonDiagram( 7, $args );
$diag->AddData( $data, 1, 10, $captions );
MLT;
  $examples_code[4] = <<<'MLT'
$args = array(
  'caption-font-size' => 28,
  'caption-line-margin' => 4,
  'caption-margin' => 24,
  'first-vertex' => 3,
);
$data = array( 8, 5, 6, 8, 9, 7, 8, 9 );
$captions = array( 
  "axis\na", "axis\nb", "axis\nc",
  "axis\nd", "axis\ne", "axis\nf",
  "axis\ng", "axis\nh",
);
$diag = new nGonDiagram( 7, $args );
$diag->AddData( $data, 1, 10, $captions );
MLT;
  $examples_code[5] = <<<'MLT'
$args = array(
  'caption-font-size' => 28,
  'caption-line-margin' => 4,
  'caption-margin' => 24,
  'first-vertex' => 3,
  'rotation' => 15,
);
$data = array( 8, 5, 6, 8, 9, 7, 8, 9 );
$captions = array( 
  "axis\na", "axis\nb", "axis\nc",
  "axis\nd", "axis\ne", "axis\nf",
  "axis\ng", "axis\nh",
);
$diag = new nGonDiagram( 7, $args );
$diag->AddData( $data, 1, 10, $captions );
MLT;
  $examples_code[6] = <<<'MLT'
$args = array(
  'graduations' => 3,
  'border-color' => '#222222',
  'inner-bgcolor' => '#cccccc',
  'graduation-bgcolors' => array(
    '#444444', '#777777', '#999999'
  ),
);
$data = array( 8, 5, 6, 8, 9, 7, 8, 9 );
$captions = array( 
  'A', 'B', 'C', 'D', 
  'E', 'F', 'G', 'H',
);
$diag = new nGonDiagram( 8, $args );
$diag->AddData( $data, 1, 10, $captions );
MLT;
  $examples_code[7] = <<<'MLT'
$args = array(
  'graduations' => 3,
  'border-color' => '#222222',
  'inner-bgcolor' => '#cccccc',
  'graduation-bgcolors' => array(
    '#444444', '#777777', '#999999'
  ),
  'graph-color' => '#222222',
  'border-width' => 8,
  'with-rays' => false,
);
$data = array( 8, 5, 6, 8, 9, 7, 8, 9 );
$captions = array( 
  'A', 'B', 'C', 'D', 
  'E', 'F', 'G', 'H',
);
$diag = new nGonDiagram( 8, $args );
$diag->AddData( $data, 1, 10, $captions );
MLT;
  $user_code_def = <<<'MLT'
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
  'inner-bgcolor' => null,
  'border-color' => '#337799',
  'border-width' => 4,
  'graduations' => 2,
  'graduation-width' => 1,
  'graduation-color' => null,
  'graduation-bgcolors' => null,
  'with-rays' => true,
  'rays-width' => 1,
  'rays-color' => null,
  'centering' => true,
  'cache-grid' => false,
  'graph-color' => '#772222',
  'graph-width' => 4,
  'caption-font-size' => 16,
  'caption-color' => '#222222',
  'caption-margin' => null,
  'font-location' => null,
  'caption-line-margin' => 0,
);
$data = array( 
  8, 7, 6.5, 8, 9, 8, 9, 7, 
  7.5, 6, 7, 9, 9, 8, 7, 9
);
$captions = array( 
  "A", "B", "C", "D", "E", "F", "G", "H",
  "I", "J", "K", "L", "M", "N", "O", "P"
);
$diag = new nGonDiagram( 5, $args );
$diag->AddData( $data, 1, 10, $captions );
MLT;

