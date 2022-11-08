<?php
if ( !defined( 'MEDIAWIKI' ) ) die ( "This file cannot be run standalone.\n" );

class LoopCode {

    static function onParserSetup( Parser $parser ) {
        $parser->setHook( 'code', 'LoopCode::renderCodeTag' );
        return true;
    }

    static function renderCodeTag( $input, array $args, Parser $parser, PPFrame $frame ) {
        $content = str_replace('<', '&lt;', $input);
        $content = str_replace('>','&gt;', $content);

        $newcontent = '<code>'.$content.'</code>';
        return $newcontent;
    }

}
