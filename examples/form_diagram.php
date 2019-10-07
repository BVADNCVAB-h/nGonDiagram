<?php
    define('SELF_TO_ROOT', '../');
    require_once SELF_TO_ROOT.'examples/common.php';
    require_once SELF_TO_ROOT.'examples/examples_code.php';
    require_once SELF_TO_ROOT.'src/ngondiagram.php';
    $execution_start = floor(microtime(true) * 1000);
    $code_str = null; $base64_form = false;
    if ( isset($_GET['id']) && isset($examples_code[ $_GET['id'] ]) ) 
    { $code_str = $examples_code[ $_GET['id'] ]; }
    elseif ( isset($_GET['code']) ) 
    { $code_str = Base64Url::Decode( $_GET['code'] ); }
    else 
    { exit(); }
    if ( isset($_GET['base64']) && $_GET['base64'] == 'true' )
    { $base64_form = true; }
    eval( $code_str );
    if ( isset($_GET['test']) && $_GET['test'] === 'true' ) {
        $execution_time = floor(microtime(true) * 1000) - $execution_start;
        exit( "{$execution_time}ms taken" );
    }
    if ( !$base64_form ) { $diag->GetImage( 'png'); }
    else {
        ob_start();
        $diag->GetImage( 'png' );
        $img_base64 = base64_encode( ob_get_contents() );
        ob_clean();
        header( 'Content-Type: text/plain' );
        header( 'Content-Length: '.strlen($img_base64) );
        print $img_base64;
    }