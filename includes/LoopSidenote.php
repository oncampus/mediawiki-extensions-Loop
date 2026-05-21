<?php
/**
 * @description Adds sidenotes via loop_sidenote
 * @ingroup Extensions
 * @author Dustin Neß <dustin.ness@th-luebeck.de>
 */

if ( !defined( 'MEDIAWIKI' ) ) die ( "This file cannot be run standalone.\n" );

class LoopSidenote {
    static function onParserSetup( Parser $parser ): bool
	{
        $parser->setHook( 'loop_sidenote', 'LoopSidenote::renderSidenote' );
        return true;
    }

    static function renderSidenote( $input, array $args, Parser $parser, PPFrame $frame ): string
	{
        $type = '';

        if( isset( $args['type'] ) ) {
            if( $args['type'] == 'keyword') {
                $type = 'keyword';
            } else if ( $args['type'] == 'marginalnote') {
                $type = 'marginalnote';
            }
        }

        $html = '<div class="loopsidenote loopsidenote_' . $type . '">';
        $html .= $parser->recursiveTagParseFully( $input );
        $html .= '</div>';

        return $html;
    }

}
