<?php
    define( 'SELF_TO_ROOT', '../' );
    require_once SELF_TO_ROOT.'examples/common.php';
    require_once SELF_TO_ROOT.'examples/examples_code.php';
    require_once SELF_TO_ROOT.'src/ngondiagram.php';
    function get_highlighted_code( $code_str ) {
        static $parts_to_exclude = array('&lt;?php', '<br />');
        $highlighted = highlight_string( "<?php\n".$code_str, true );
        $prev_start_pos = 0;
        foreach( $parts_to_exclude as $part_to_exclude ) {
            $excl_part_start = strpos($highlighted, $part_to_exclude, $prev_start_pos);
            if ( $excl_part_start !== false ) {
                $excl_part_end = $excl_part_start + strlen($part_to_exclude);
                $highlighted = substr($highlighted, 0, $excl_part_start) .
                            substr($highlighted, $excl_part_end);
                $prev_start_pos = $excl_part_start;
            }
        }
        return $highlighted;
    }
    $code_highlighted = array();
    foreach ( $examples_code as $index => $example_code ) 
    { $code_highlighted[$index] = get_highlighted_code( $example_code ); }
    $generate_static = isset($_GET['generate_static']);
    if ( isset($_POST['user_code']) ) $user_code = $_POST['user_code'];
    else $user_code = $user_code_def;
    $user_code_encoded = Base64Url::Encode( $user_code );
    if ( $generate_static ) {
        ob_start();
        $execution_start = floor(microtime(true) * 1000);
        $diags_dir = './diags';
        if ( !is_dir($diags_dir) ) {
            $file_name = $diags_dir;
            if ( is_file($file_name) ) {
                $rm_stack = array($file_name);
                while ( true ) {
                    $file_name .= '.bu';
                    array_unshift($rm_stack, $file_name);
                    if ( !is_file($file_name) ) break; 
                }
                $rm_stack_size = count($rm_stack);
                for ( $i=1; $i < $rm_stack_size; $i++ ) 
                { rename($rm_stack[$i], $rm_stack[$i-1]); }
            }
            mkdir( $diags_dir );
        }
        $code_highlighted[] = get_highlighted_code( $user_code_def );
    }
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>nGonDiagram :: Examples</title>
<!--
<script type="text/javascript" src=""></script>
<link rel="stylesheet" type="text/css" href="" media="all">
-->
<?php if ( !$generate_static ) { ?>
<script type="text/javascript">
    var SELF_TO_ROOT = '<?php print SELF_TO_ROOT ?>';
    var elems = {};
    var Base64Url = (function() {
        var rules = { 
            <?php 
            foreach ( Base64Url::$rules as $base64_symb => $replacing_symb ) 
            { print "'{$base64_symb}': '{$replacing_symb}', "; }
            ?>
        };
        var greplace = function( haystack, needle, replacement ) {
            needle = needle.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            var regExp = new RegExp(needle, 'g');
            return haystack.replace(regExp, replacement);
        };
        return {
            Decode: function( str ) {
                for ( var base64Symb in rules ) {
                    var replacingSymb = rules[ base64Symb ];
                    str = greplace( str, replacingSymb, base64Symb );
                }
                return atob(str);
            },
            Encode: function( str ) {
                str = btoa(str);
                for ( var base64Symb in rules ) {
                    var replacingSymb = rules[ base64Symb ];
                    str = greplace( str, base64Symb, replacingSymb );
                }
                return str;
            },
        };
    })();
    window.onload = function(ev) {
        elems.sendCodeButton = document.querySelector('button[name="send_user_code"]');
        elems.userCode = document.querySelector('textarea[name="user_code"]');
        elems.userDiag = document.querySelector('#user_diag');
        elems.sendCodeButton.onclick = function(ev) {
            if ( ev ) ev.preventDefault();
            var xhr = new XMLHttpRequest();
            xhr.onload = function(ev) {
                elems.userDiag.setAttribute( 
                    'src', 
                    'data:image/png;base64,'+this.responseText
                );
            };
            var userCodeEncoded = Base64Url.Encode( elems.userCode.value );
            xhr.open('GET', SELF_TO_ROOT+'examples/form_diagram.php?base64=true&code='+userCodeEncoded );
            xhr.send();
        };
    };
</script>
<?php } ?>
<style type="text/css">
body {
background-color: #dddddd;
}
#header {
margin: 15px 10% 10px 10%;
}
#footer {
margin: 10px 10% 80px 10%;
}
#header, #footer {
float: left;
width: calc(80% - 16px);
font-size: 5vw;
color: #444444;
text-align: center;
}
#main_part {
float: left;
width: 80%;
margin: 10px 10% 10px 10%;
}
.card {
float: left;
width: calc(33% - 16px);
margin-right: 16px;
margin-bottom: 35px;
}
img.diag {
display: block;
max-width: 100%;
min-width: 100%;
margin-bottom: 8px;
}
.img_container {
width: 100%;
min-height: 200px;
}
textarea {
background-color: transparent;
border-width: 0;
}
.textarea-container {
margin-bottom: 4px;
}
button[type="submit"] {
display: block;
width: 100%;
border: 2px solid #222222;
border-radius: 6px;
background-color: transparent;
padding: 4px 30px;
font-size: 18px;
}
@media (min-width: 1050px) and (max-width: 1390px) {
    .card {
    width: calc(49% - 16px);
    }
    #header, #main_part, #footer {
    width: 70%;
    margin-left: 15%;
    margin-right: 15%;
    }
}
@media (min-width: 701px) and (max-width: 1050px) {
    .card {
    width: 100%;
    }
    #header, #main_part, #footer {
    width: 60%;
    margin-left: 20%;
    margin-right: 20%;
    }
}
@media (max-width: 700px) {
    .card {
    width: 100%;
    }
    #header, #main_part, #footer {
    width: 90%;
    margin-left: 5%;
    margin-right: 5%;
    }
}
</style>
</head>
<body>
    <div id="header">
        nGonDiagram/Examples
    </div>
    <div id="main_part">
    <form method="POST" action="<?php print basename($_SERVER['SCRIPT_NAME']) ?>">
    <?php 
        $col_counter = 0;
        foreach ( $code_highlighted as $index => $highlighted ) { 
            if ( $col_counter++ % 3 === 0 ) {
                if ( $col_counter > 3 ) print "</div>"; 
                print "<div class=\"card-line\">";
            } 
            if ( !$generate_static ) {
                $img_src = SELF_TO_ROOT."examples/form_diagram.php?id={$index}";
            } else {
                $img_src = "{$diags_dir}/diag_{$index}.png";
                if ( $index < count($examples_code) )
                { eval( $examples_code[$index] ); }
                else
                { eval( $user_code_def ); }
                $diag->GetImage( 'png', $img_src );
            }
            ?>
            <div class="card">
                <div class="card-inner">
                    <div class="img_container">
                        <img src="<?php print $img_src ?>" class="diag" />
                    </div>
                    <?php if ( $generate_static && $index == count($examples_code) ) { ?>
                    <div style="font-size: 24px; padding: 6px 0px 0px; text-align: left;">
                        Default Values:
                    </div>
                    <?php } ?>
                    <div>
                        <?php print $highlighted ?>
                    </div>
                </div>
            </div>
    <?php 
        }
        if ( !$generate_static ) {
        if ( $col_counter++ % 3 === 0 ) {
            if ( $col_counter > 3 ) print "</div>"; 
            print "<div class=\"card-line\">";
        } ?>
        <div class="card">
            <div class="card-inner">
                <div class="img_container">
                    <img src="<?php print SELF_TO_ROOT ?>examples/form_diagram.php?code=<?php print $user_code_encoded ?>" id="user_diag" class="diag" />
                </div>
                <div class="textarea-container">
                    <textarea name="user_code" rows="42" cols="45" spellcheck="false"><?php print $user_code ?></textarea>
                </div>
                <div>
                    <button type="submit" name="send_user_code">try</button>
                </div>
            </div>
        </div>
    <?php
        }
        print "</div>"; 
    ?>
    </form>
    </div>
    <div id="footer">
        nGonDiagram/Examples
    </div>
</body>
</html>
<?php 
    if ( $generate_static ) {
        $html_doc = ob_get_contents();
        $fd = fopen('./index_static.html', 'wt');
        fwrite($fd, $html_doc);
        fclose($fd);
        ob_end_clean();
        $execution_end = floor(microtime(true) * 1000);
        $time_taken = $execution_end - $execution_start;
        print "done, {$time_taken}ms taken";
    }
?>