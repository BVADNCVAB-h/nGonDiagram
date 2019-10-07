<?php
    class nGonDiagram {
        private static $initialized = false;
        private static $slash = '/';
        private static $vertices_min = 3;
        private static $vertices_max = 16;
        private static $axes = array( 'x', 'y' );
        private static $axis_mdf = array( 'x' => 1, 'y' => -1 );
        private static $color_parameters = array(
            'bgcolor',
            'inner-bgcolor',
            'border-color',
            'graduation-color',
            'graduation-bgcolors',
            'rays-color',
            'graph-color',
            'caption-color',
        );
        private static $graph_colors = array(
            'graph-color',
            'caption-color',
        );
        private static $color_arrays = array(
            'graduation-bgcolors',
        );
        private static $scalable_parameters = array(
            'width',
            'height',
            'radius',
            'center-x',
            'center-y',
            'border-width',
            'graduation-width',
            'rays-width',
            'graph-width',
            'caption-font-size',
            'caption-margin',
            'caption-line-margin'
        );
        private static $default_format = null;
        private static $output_funcs = array();
        private $p;
        private $d;
        private $vertices_num;
        private $vertices_lim;
        private $vertices;
        private $inner_angle;
        private $vectors_components;
        private $cache_grid;
        private $start_point;
        private $img_cached;
        private $img;
        private static function Initialize() {
            $format_map = array(
                IMG_GIF => array('name' => 'gif'),
                IMG_JPG => array('name' => 'jpeg'),
                IMG_PNG => array('name' => 'png'),
                IMG_WBMP => array('name' => 'wbmp'),
                IMG_XPM => array('name' => 'xpm'),
            );
            $supported_formats = imagetypes();
            foreach ( $format_map as $format_code => $format ) {
                if ( !($supported_formats & $format_code) ) continue;
                if ( self::$default_format === null )
                { self::$default_format = $format['name']; }
                if ( isset($format_map['funct']) )
                { self::$output_funcs[$format['name']] = $format_map['funct']; }
                else
                { self::$output_funcs[$format['name']] = "image{$format['name']}"; }
            }
            if ( strtoupper( substr(PHP_OS, 0, 3) ) === 'WIN' ) 
            { self::$slash = '\\'; }
            self::$initialized = true;
        }
        private static function ColorComponents( $color, $comp_num=4 ) {
            $merged = substr( $color, 1 );
            $len = strlen( $merged );
            if ( $len === 3 || $len === 4 ) {
                $devided = str_split( $merged, 1 );
                if ( $len === 3 ) $devided[3] = 'F';
                for ( $i=0; $i < 4; $i++ ) 
                { $devided[$i] += $devided[$i]; }
            } elseif ( $len === 6 || $len === 8 ) {
                $devided = str_split( $merged, 2 );
                if ( $len === 6 ) $devided[3] = 'FF';
            } else {
                $devided = array( '00', '00', '00', '00' );
            }
            $components = array();
            for ( $i=0; $i < 4; $i++ ) 
            { $components[$i] = intval($devided[$i], 16); }
            if ( $comp_num === 3 ) unset($components[3]);
            else $components[3] = 127 - floor($components[3] / 2);
            return $components;
        }
        private function FindRayPoints ( $vertex, $width=1 ) {
            $vertex_projection = array(
                'x' =>  $this->d['center']['x'] + 
                        $this->vectors_components[$vertex]['x'] *
                        $this->d['radius'] * self::$axis_mdf['x'],
                'y' =>  $this->d['center']['y'] + 
                        $this->vectors_components[$vertex]['y'] *
                        $this->d['radius'] * self::$axis_mdf['y'],
            );
            if ( $width != 1 && $width != 0 ) {
                $points = array(
                    'pt' => array(),
                    'fn' => 'imagefilledpolygon',
                    'vt' => 5,
                );
                $a = ceil($width / 2);
                $b = $a / tan( $this->inner_angle / 2 );
                $points['pt'][] = 
                        $this->d['center']['x'] - 
                        $this->vectors_components[$vertex]['y'] * $a * self::$axis_mdf['x'];
                $points['pt'][] = 
                        $this->d['center']['y'] + 
                        $this->vectors_components[$vertex]['x'] * $a * self::$axis_mdf['y'];
                $points['pt'][] = 
                        $this->d['center']['x'] + 
                        $this->vectors_components[$vertex]['y'] * $a * self::$axis_mdf['x'];
                $points['pt'][] = 
                        $this->d['center']['y'] - 
                        $this->vectors_components[$vertex]['x'] * $a * self::$axis_mdf['y'];
                $points['pt'][] = 
                        $vertex_projection['x'] - 
                        ($b * $this->vectors_components[$vertex]['x'] -
                         $a * $this->vectors_components[$vertex]['y']) * self::$axis_mdf['x'];
                $points['pt'][] = 
                        $vertex_projection['y'] -
                        ($b * $this->vectors_components[$vertex]['y'] +
                         $a * $this->vectors_components[$vertex]['x']) * self::$axis_mdf['y'];
                $points['pt'][] = $vertex_projection['x'];
                $points['pt'][] = $vertex_projection['y'];
                $points['pt'][] = 
                        $vertex_projection['x'] - 
                        ($b * $this->vectors_components[$vertex]['x'] +
                         $a * $this->vectors_components[$vertex]['y']) * self::$axis_mdf['x'];
                $points['pt'][] = 
                        $vertex_projection['y'] -
                        ($b * $this->vectors_components[$vertex]['y'] -
                         $a * $this->vectors_components[$vertex]['x']) * self::$axis_mdf['y'];
            } else {
                $points = array(
                    'pt' => array(
                        $this->d['center']['x'],
                        $this->d['center']['y'],
                        $vertex_projection['x'],
                        $vertex_projection['y'],
                    ),
                    'fn' => 'imageline',
                    'vt' => null,
                );
            }
            return $points;
        }
        private function GetBorderDistance( $width ) {
            return round($width / cos(M_PI_2 - $this->inner_angle / 2));
        }
        private function FindVerticesPoints( $radii, $width, $width_as_distance=true  ) {
            if ( !is_array($radii) ) {
                $radius = $radii;
                $radii = array();
                for ( $i=0; $i < $this->vertices_num; $i++ )
                { $radii[] = $radius; }
            }
            $points = array( 
                'pt' => array(), 
                'fn' => 'imagepolygon', 
                'vt' => $this->vertices_num + 1
            );
            if ( !$width_as_distance ) $distance = $this->GetBorderDistance($width);
            else $distance = $width;
            $last_vertex = $this->vertices_num-1;
            $draw_dir_mdf = ( $distance > 0 ) ? 0 : 1;
            if ( $width != 1 && $width != 0 ) {
                $points['fn'] = 'imagefilledpolygon';
                $points['vt'] = ($this->vertices_num + 1) * 2;
                for ( $i=$last_vertex; $i >= 0; $i-- ) {
                    foreach ( self::$axes as $axis ) {
                        $points['pt'][] = 
                            $this->vectors_components[$i][$axis] * 
                            ($radii[$i] + $distance - 1 + $draw_dir_mdf) * 
                            self::$axis_mdf[$axis] +
                            $this->d['center'][$axis];
                    }
                }
                foreach ( self::$axes as $axis ) {
                    $points['pt'][] = 
                        $this->vectors_components[$last_vertex][$axis] * 
                        ($radii[$last_vertex] + $distance - 1 + $draw_dir_mdf) * 
                        self::$axis_mdf[$axis] +
                        $this->d['center'][$axis];
                }
            }
            foreach ( self::$axes as $axis ) {
                $points['pt'][] = 
                    $this->vectors_components[$last_vertex][$axis] * 
                    $radii[$last_vertex] * self::$axis_mdf[$axis] +
                    $this->d['center'][$axis];
            }
            for ( $i=0; $i <= $last_vertex; $i++ ) {
                foreach ( self::$axes as $axis ) {
                    $points['pt'][] = 
                        $this->vectors_components[$i][$axis] * 
                        $radii[$i] * self::$axis_mdf[$axis] +
                        $this->d['center'][$axis];
                }
            }
            return $points;
        }
        private function RecalcDerivatives() {
            $this->d = array();
            $align_mdf_x = 0; $align_mdf_y = 0;
            if ( $this->p['caption-margin'] === null ) {
                $caption_margin_auto = floor($this->p['caption-font-size'] * 0.667);
                $this->p['caption-margin'] = max($caption_margin_auto, 10);
            }
            foreach ( self::$scalable_parameters as $scalable_parameter ) 
            { $this->d[ $scalable_parameter ] = round($this->p[ $scalable_parameter ] * $this->p['internal-scale']); }
            if ( $this->p['centering'] ) {
                $align_mdf_x = $this->d['radius'] * ($this->vertices_lim['x_max'] + $this->vertices_lim['x_min']) / 2;
                $align_mdf_y = $this->d['radius'] * ($this->vertices_lim['y_max'] + $this->vertices_lim['y_min']) / 2;
            }
            $this->d['center'] = array(
                'x' => floor($this->d['width'] / 2 + ($this->d['center-x'] - $align_mdf_x) * self::$axis_mdf['x']),
                'y' => floor($this->d['height'] / 2 + ($this->d['center-y'] - $align_mdf_y) * self::$axis_mdf['y']),
            );
            if ( $this->p['anti-aliasing'] ) {
                $this->d['image-creation'] = 'imagecreatetruecolor';
                $this->d['color-allocation'] = 'imagecolorallocate';
                $this->d['color-components'] = 3;
            } else {
                $this->d['image-creation'] = 'imagecreate';
                $this->d['color-allocation'] = 'imagecolorallocatealpha';
                $this->d['color-components'] = 4;
            }
            $this->d['font-location'] = null;
            if ( $this->p['font-location'] === null ) { 
                $font_file = 'DejaVuSans';
                $font_path = dirname(__FILE__).self::$slash.$font_file;
            } else {
                $font_file = $this->p['font-location'];
                $font_path = $this->p['font-location'];
            }
            if ( is_file($font_path.'.ttf') ) {
                $this->d['font-location'] = $font_path.'.ttf';
            } elseif ( is_file($font_path) ) {
                $this->d['font-location'] = $font_path;
            } elseif ( ($font_dir=getenv( 'GDFONTPATH' )) !== false ) {
                $font_dir = realpath( $font_dir );
                if ( is_file($font_dir.self::$slash.$font_file.'.ttf') ) { 
                    $this->d['font-location'] = $font_dir.self::$slash.$font_file.'.ttf'; 
                } elseif ( is_file($font_dir.self::$slash.$font_file) ) {
                    $this->d['font-location'] = $font_dir.self::$slash.$font_file; 
                }
            }
            if ( $this->p['inner-bgcolor'] === null )
            { $this->p['inner-bgcolor'] = $this->p['bgcolor']; }
            if ( $this->p['graduation-color'] === null )
            { $this->p['graduation-color'] = $this->p['border-color']; }
            if ( $this->p['rays-color'] === null )
            { $this->p['rays-color'] = $this->p['border-color']; }
            $this->d['colors'] = array();
            foreach ( self::$color_parameters as $color_parameter ) {
                if ( !in_array($color_parameter, self::$color_arrays) ) { 
                    $this->d['colors'][$color_parameter] = self::ColorComponents(
                        $this->p[$color_parameter],
                        $this->d['color-components']
                    ); 
                } else {
                    $this->d['colors'][$color_parameter] = array();
                    if ( is_array($this->p[$color_parameter]) ) {
                        foreach ( $this->p[$color_parameter] as $color ) { 
                            $this->d['colors'][$color_parameter][] = self::ColorComponents(
                                $color,
                                $this->d['color-components']
                            );
                        }
                    }
                }
            }
            $this->d['points'] = array();
            $this->d['points']['border'] = $this->FindVerticesPoints( 
                $this->d['radius'], 
                $this->d['border-width'],
                false
            );
            $this->d['points']['graduations'] = array();
            $this->d['points']['graduations-bg'] = array();
            $graduation_step = floor($this->d['radius'] / ($this->p['graduations']+1));
            $gradution_radius = 0;
            for ( $i=0; $i < $this->p['graduations']; $i++ ) {
                $gradution_radius += $graduation_step;
                $this->d['points']['graduations'][] = $this->FindVerticesPoints( 
                    $gradution_radius, 
                    $this->d['graduation-width'],
                    false
                );
                $this->d['points']['graduations-bg'][] = $this->FindVerticesPoints( 
                    $gradution_radius, 1
                );
            }
            $this->d['points']['inner-bg'] = $this->FindVerticesPoints( 
                $this->d['radius'], 1
            );
            if ( $this->p['with-rays'] ) {
                $this->d['points']['rays'] = array();
                $vertices_points = $this->FindVerticesPoints( $this->d['radius'], 1 );
                array_shift( $vertices_points['pt'] );
                array_shift( $vertices_points['pt'] );
                for ( $i=0; $i < $this->vertices_num; $i++ ) {
                    $this->d['points']['rays'][$i] = $this->FindRayPoints( 
                        $i, $this->d['rays-width']
                    );
                }
            }
        }
        private function DrawAxis( $dest_img ) {
            $palette = array();
            if ( is_resource($this->{$dest_img}) )
            { imagedestroy($this->{$dest_img}); }
            $this->{$dest_img} = $this->d['image-creation'](
                $this->d['width'], 
                $this->d['height']
            );
            if ( $this->p['anti-aliasing'] )
            { imageantialias( $this->{$dest_img}, true ); }
            foreach ( $this->d['colors'] as $parameter_name => &$parameter_value ) {
                if ( in_array($parameter_name, self::$graph_colors) )
                { continue; }
                if ( !in_array($parameter_name, self::$color_arrays) ) {
                    $call_args = array($this->{$dest_img});
                    for ( $i=0; $i < $this->d['color-components']; $i++ )
                    { $call_args[] = $parameter_value[$i]; }
                    $palette[$parameter_name] = call_user_func_array(
                        $this->d['color-allocation'],
                        $call_args
                    );
                } else {
                    $palette[$parameter_name] = array();
                    if ( is_array($parameter_value) ) {
                        foreach ( $parameter_value as $color ) {
                            $call_args = array($this->{$dest_img});
                            for ( $i=0; $i < $this->d['color-components']; $i++ )
                            { $call_args[] = $color[$i]; }
                            $palette[$parameter_name][] = call_user_func_array(
                                $this->d['color-allocation'],
                                $call_args
                            );
                        }
                    }
                }
            }
            unset($parameter_value);
            imagefill( $this->{$dest_img}, 0, 0, $palette['bgcolor'] );
            $graduations = count($this->d['points']['graduations']);
            $graduation_bg_defined = is_array($this->p['graduation-bgcolors']);
            if ( $this->p['inner-bgcolor'] !== $this->p['bgcolor'] ) {
                imagefilledpolygon(
                    $this->{$dest_img},
                    $this->d['points']['inner-bg']['pt'],
                    $this->d['points']['inner-bg']['vt'],
                    $palette['inner-bgcolor']
                );
            }
            if ( $graduation_bg_defined ) { 
                $graduation_bgs_number = count($this->p['graduation-bgcolors']);
                $graduation_bgs_counter = $graduation_bgs_number - 1;
            }
            for ( $i=$graduations-1; $i >= 0; $i-- ) {
                if ( $graduation_bg_defined && $graduation_bgs_number > 0 ) {
                    imagefilledpolygon(
                        $this->{$dest_img},
                        $this->d['points']['graduations-bg'][$i]['pt'],
                        $this->d['points']['graduations-bg'][$i]['vt'],
                        $palette['graduation-bgcolors'][$graduation_bgs_counter]
                    );
                    if ( --$graduation_bgs_counter < 0 )
                    { $graduation_bgs_counter = $graduation_bgs_number - 1; }
                }
                if ( $this->d['graduation-width'] !== 0 ) {
                    $this->d['points']['graduations'][$i]['fn'](
                        $this->{$dest_img},
                        $this->d['points']['graduations'][$i]['pt'],
                        $this->d['points']['graduations'][$i]['vt'],
                        $palette['graduation-color']
                    );
                }
            }
            if ( array_key_exists('rays', $this->d['points']) ) {
                for ( $i=0; $i < $this->vertices_num; $i++ ) {
                    if ( $this->d['points']['rays'][$i]['fn'] === 'imageline' ) {
                        imageline( 
                            $this->{$dest_img},
                            $this->d['points']['rays'][$i]['pt'][0],
                            $this->d['points']['rays'][$i]['pt'][1],
                            $this->d['points']['rays'][$i]['pt'][2],
                            $this->d['points']['rays'][$i]['pt'][3],
                            $palette['rays-color']
                        );
                    } else {
                        $this->d['points']['rays'][$i]['fn'](
                            $this->{$dest_img},
                            $this->d['points']['rays'][$i]['pt'],
                            $this->d['points']['rays'][$i]['vt'],
                            $palette['rays-color']
                        );
                    }
                }
            }
            if ( $this->d['border-width'] !== 0 ) {
                $this->d['points']['border']['fn'](
                    $this->{$dest_img},
                    $this->d['points']['border']['pt'],
                    $this->d['points']['border']['vt'],
                    $palette['border-color']
                );
            }
        }
        private function PrepareTemplate() {
            if ( !$this->cache_grid ) {
                $this->RecalcDerivatives();
                $this->DrawAxis( 'img' );
            } else {
                if ( !is_resource($this->img) ) {
                    $this->img = $this->d['image-creation']( 
                        $this->d['width'], 
                        $this->d['height'] 
                    );
                }
                imagecopy(
                    $this->img, $this->img_cached, 
                    0, 0, 0, 0, $this->d['width'], $this->d['height']
                );
            }
        }
        public function AddData( $values, $min_value, $max_value, $captions ) {
            static $palette = array();
            $this->PrepareTemplate();
            foreach ( self::$graph_colors as $parameter_name ) {
                if ( !$this->cache_grid || !isset($palette[$parameter_name]) ) {
                    $call_args = array($this->img);
                    for ( $i=0; $i < $this->d['color-components']; $i++ )
                    { $call_args[] = $this->d['colors'][$parameter_name][$i]; }
                    $palette[$parameter_name] = call_user_func_array(
                        $this->d['color-allocation'],
                        $call_args
                    );
                }
            }
            $values_num = count( $values );
            $captions_num = count( $captions );
            for ( $i=$values_num; $i < $this->vertices_num; $i++ )
            { $values[ $i ] = $min_value; }
            for ( $i=$captions_num; $i < $this->vertices_num; $i++ )
            { $captions[ $i ] = ''; }
            for ( $i=0; $i < $this->vertices_num; $i++ )
            { $captions[ $i ] = explode("\n", $captions[ $i ]); }
            $values_adj = array(); $captions_adj = array();
            $first_vertex_index = $this->p['first-vertex'] - 1;
            for ( $i=0; $i < $first_vertex_index; $i++ ) {
                $values_adj[ $i ] = $values[ $this->vertices_num - $first_vertex_index + $i ];
                $captions_adj[ $i ] = $captions[ $this->vertices_num - $first_vertex_index + $i ];
            }
            $first_value_index = $this->vertices_num - $this->p['first-vertex'] + 1;
            for ( $i=$first_vertex_index; $i < $this->vertices_num; $i++ ) {
                $values_adj[ $i ] = $values[ $i - $first_vertex_index ];
                $captions_adj[ $i ] = $captions[ $i - $first_vertex_index ];
            }
            for ( $i=0; $i < $this->vertices_num; $i++ ) { 
                $values_adj[ $i ] = floor(
                    $this->d['radius'] *
                    ($values_adj[ $i ]-$min_value) / ($max_value-$min_value)
                );
            }
            $graph_points = $this->FindVerticesPoints( 
                $values_adj, 
                $this->d['graph-width'], 
                false
            );
            $graph_points['fn'](
                $this->img,
                $graph_points['pt'],
                $graph_points['vt'],
                $palette['graph-color']
            );
            if ( $this->d['font-location'] === null ) return;
            $line_max_height = 0;
            $captions_dims = array();
            for ( $i=0; $i < $this->vertices_num; $i++ ) {
                $caption_parts = count($captions_adj[$i]);
                $captions_dims[$i] = array();
                for ( $j=0; $j < $caption_parts; $j++ ) {
                    $caption_box = imagettfbbox( 
                        $this->d['caption-font-size'], 0,
                        $this->d['font-location'],
                        $captions_adj[$i][$j]
                    );
                    $captions_dims[$i][$j] = array(
                        'width' => $caption_box[2] - $caption_box[0],
                        'height' => abs($caption_box[7] - $caption_box[1]),
                        'baseline' => abs($caption_box[1]),
                    );
                    if ( $captions_dims[$i][$j]['height'] > $line_max_height )
                    { $line_max_height = $captions_dims[$i][$j]['height']; }
                }
            }
            $box_shifts = array();
            for ( $i=0; $i < $this->vertices_num; $i++ ) {
                $box_shifts[ $i ] = array();
                $line_num = count($captions_dims[$i]);
                $height_acc = 0;
                $acc_mdf = ( $this->vectors_components[$i]['y'] < 0 ) ? -1 : 1;
                if ( $this->vectors_components[$i]['y'] <= 0 ) {
                    $first_line = 0;
                    $last_line = $line_num - 1;
                    $trv_dir = 1;
                } else {
                    $first_line = $line_num - 1;
                    $last_line = 0;
                    $trv_dir = -1;
                }
                if ( $this->vectors_components[$i]['y'] == 0 ) 
                { $first_line_gap_y = $line_max_height - $captions_dims[$i][0]['height']; }
                $total_height = ($line_max_height * $line_num + $this->d['caption-line-margin'] * ($line_num-1));
                for ( $j=$first_line; $j*$trv_dir <= $trv_dir*$last_line; $j+=$trv_dir ) {
                    $box_shifts[$i][$j] = array();
                    $box_center_y = $this->vectors_components[$i]['y'] * $line_max_height / 2;
                    if ( $this->vectors_components[$i]['y'] != 0 ) { 
                        $box_shifts[$i][$j]['y_bottom'] = $box_center_y - $line_max_height / 2 + $height_acc;
                        $height_acc += ($line_max_height + $this->d['caption-line-margin']) * $acc_mdf;
                    } else {
                        $height_acc +=  $line_max_height * $acc_mdf;
                        $box_shifts[$i][$j]['y_bottom'] = $total_height / 2 - $height_acc + $first_line_gap_y;
                        $height_acc += $this->d['caption-line-margin'] * $acc_mdf;
                    }
                    if ( $this->vectors_components[$i]['x'] > 0 ) 
                    { $box_shifts[$i][$j]['x_left'] = 0; }
                    elseif ( $this->vectors_components[$i]['x'] < 0 )
                    { $box_shifts[$i][$j]['x_left'] = -$captions_dims[$i][$j]['width']; }
                    else
                    { $box_shifts[$i][$j]['x_left'] = -$captions_dims[$i][$j]['width'] / 2; }
                    $box_shifts[$i][$j]['x_left'] = floor(
                        $box_shifts[$i][$j]['x_left'] * self::$axis_mdf['x']
                    );
                    $box_shifts[$i][$j]['y_bottom'] = floor(
                        $box_shifts[$i][$j]['y_bottom'] * self::$axis_mdf['y']
                    );
                }
            }
            $border_outer_width = ( $this->d['border-width'] > 0 )
                                ? $this->d['border-width']
                                : 0;
            $border_outer_distance = $this->GetBorderDistance( $border_outer_width );
            $vertices_points = $this->FindVerticesPoints( 
                $this->d['radius'] + $this->d['caption-margin'] + $border_outer_distance, 1
            );
            array_shift( $vertices_points['pt'] );
            array_shift( $vertices_points['pt'] );
            for ( $i=0; $i < $this->vertices_num; $i++ ) {
                $line_num = count($captions_adj[$i]);
                for ( $j=0; $j < $line_num; $j++ ) {
                    imagettftext(
                        $this->img, $this->d['caption-font-size'], 0,
                        $vertices_points['pt'][ $i*2 ] + $box_shifts[ $i ][ $j ]['x_left'],
                        $vertices_points['pt'][ $i*2 + 1 ] + $box_shifts[ $i ][ $j ]['y_bottom'],
                        $palette['caption-color'],
                        $this->d['font-location'],
                        $captions_adj[$i][$j]
                    );
                }
            }
        }
        public function ClearData() {
            if ( is_resource($this->img) ) {
                imagedestroy($this->img);
                $this->img = null;
            }
        }
        public function GetImage( $format, $path=null ) {
            if ( !is_resource($this->img) ) 
            { $this->PrepareTemplate(); }
            $format_f = strtolower($format);
            if ( !isset(self::$output_funcs[$format_f]) ) 
            { $format_f = self::$default_format; }
            $funct = self::$output_funcs[$format_f];
            $dest_img =& $this->img;
            if ( $this->p['internal-scale'] != $this->p['output-scale'] ) {
                $output_width = round($this->p['width'] * $this->p['output-scale']);
                $output_height = round($this->p['height'] * $this->p['output-scale']);
                $rsz_img = $this->d['image-creation'](
                    $output_width, 
                    $output_height
                );
                if ( $this->p['anti-aliasing'] )
                { imageantialias($rsz_img, true); }
                imagecopyresampled(
                    $rsz_img, $this->img,
                    0, 0, 0, 0,
                    $output_width, $output_height,
                    $this->d['width'], $this->d['height']
                );
                $dest_img =& $rsz_img;
            }
            if ( $path === null ) {
                header( "Content-type: image/{$format_f}" );
                $funct( $dest_img ); 
            } else { 
                $funct( $dest_img, $path ); 
            }
            if ( isset($rsz_img) ) imagedestroy($rsz_img);
        }
        public function GetImageResource() {
            if ( !is_resource($this->img) ) 
            { $this->PrepareTemplate(); }
            return $this->img;
        }
        public function __construct( $vertices_num, $parameters=null ) {
            if ( !self::$initialized ) self::Initialize();
            $this->p = array(
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
                'inner-bgcolor' => null,        /*default: bgcolor*/
                'border-color' => '#337799',
                'border-width' => 4,
                'graduations' => 2,
                'graduation-width' => 1,
                'graduation-color' => null,     /*default: border-color*/
                'graduation-bgcolors' => null,  /*default: inner-bgcolor*/
                'with-rays' => true,
                'rays-width' => 1,
                'rays-color' => null,           /*default: border-color*/
                'centering' => true,
                'cache-grid' => false,
                'graph-color' => '#772222',
                'graph-width' => 4,
                'caption-font-size' => 16,
                'caption-color' => '#222222',
                'caption-margin' => null,       /*default: max(0.667 * caption-font-size, 10)*/
                'font-location' => null,        /*default: './DejaVuSans.ttf'*/
                'caption-line-margin' => 0,
            );
            if ( is_array($parameters) ) {
                foreach ( $parameters as $parameter_name => $parameter_value ) 
                { $this->p[ $parameter_name ] = $parameter_value; }
            }
            if ( $vertices_num < self::$vertices_min ) 
            { $this->vertices_num = self::$vertices_min; }
            elseif ( $vertices_num > self::$vertices_max )
            { $this->vertices_num = self::$vertices_max; }
            else { $this->vertices_num = $vertices_num; }
            if ( $this->p['first-vertex'] < 1 )
            { $this->p['first-vertex'] = 1; }
            if ( $this->p['first-vertex'] > $this->vertices_num )
            { $this->p['first-vertex'] = $this->vertices_num; }
            $angular_dist = 2 * M_PI / $this->vertices_num;
            $this->inner_angle = M_PI - $angular_dist;
            if ( $this->vertices_num % 2 === 0 )
            { $this->start_point = deg2rad(90 / ($this->vertices_num / 4) - 90); }
            else
            { $this->start_point = $angular_dist / 2 - M_PI_2; }
            $this->start_point += deg2rad( $this->p['rotation'] );
            $this->vertices = array();
            $this->vectors_components = array(); 
            $this->vertices[0] = $this->start_point;
            $this->vectors_components[0] = array(
                'x' => round( cos($this->vertices[0]), 2 ),
                'y' => round( sin($this->vertices[0]), 2 ),
            );
            $this->vertices_lim = array(
                'x_max' => $this->vectors_components[0]['x'],
                'y_max' => $this->vectors_components[0]['y'],
            );
            $this->vertices_lim['x_min'] = $this->vertices_lim['x_max'];
            $this->vertices_lim['y_min'] = $this->vertices_lim['y_max'];
            for ( $i=1; $i < $this->vertices_num; $i++ ) { 
                $this->vertices[$i] = $this->vertices[$i-1] + $angular_dist;
                $this->vectors_components[$i]['x'] = round( cos($this->vertices[$i]), 2 );
                $this->vectors_components[$i]['y'] = round( sin($this->vertices[$i]), 2 );
                if ( $this->vectors_components[$i]['x'] > $this->vertices_lim['x_max'] )
                { $this->vertices_lim['x_max'] = $this->vectors_components[$i]['x']; }
                if ( $this->vectors_components[$i]['y'] > $this->vertices_lim['y_max'] )
                { $this->vertices_lim['y_max'] = $this->vectors_components[$i]['y']; }
                if ( $this->vectors_components[$i]['x'] < $this->vertices_lim['x_min'] )
                { $this->vertices_lim['x_min'] = $this->vectors_components[$i]['x']; }
                if ( $this->vectors_components[$i]['y'] < $this->vertices_lim['y_min'] )
                { $this->vertices_lim['y_min'] = $this->vectors_components[$i]['y']; }
            }
            $this->img = null;
            $this->img_cached = null;
            $this->cache_grid = $this->p['cache-grid'];
            if ( $this->cache_grid ) {
                $this->RecalcDerivatives();
                $this->DrawAxis( 'img_cached' );
            }
        }
    }







