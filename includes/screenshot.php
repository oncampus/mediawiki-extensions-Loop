<?php 

$html_file = sys_get_temp_dir().'/'.uniqid().'.html';

$input = urldecode($_POST["html"]);

copy( $input, $html_file );


# /opt/google/chrome/chrome --headless --dump-dom https://www.chromestatus.com/

var_dump($input);