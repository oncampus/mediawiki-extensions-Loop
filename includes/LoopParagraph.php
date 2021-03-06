<?php
/**
 * @description Adds citations via loop_paragraph
 * @ingroup Extensions
 * @author Dustin Neß <dustin.ness@th-luebeck.de> 
 */

if ( !defined( 'MEDIAWIKI' ) ) die ( "This file cannot be run standalone.\n" );

class LoopParagraph {
    static function onParserSetup( Parser $parser ) {
        $parser->setHook( 'loop_paragraph', 'LoopParagraph::renderParagraph' );
        return true;
    }

    static function renderParagraph( $input, array $args, Parser $parser, PPFrame $frame ) {

        // for now loop_paragraph only supports citations
        $html = '<div class="loopparagraph">';
        $html .= '<div class="loopparagraph_left">';
        $html .= '<span class="ic ic-citation"></span>';
        $html .= '</div>';
        $html .= '<div class="loopparagraph_right"><blockquote>';
        $html .= $parser->recursiveTagParseFully( $input );
        $html .= '</div>';
        
        if( isset( $args['copyright'] ) ) {
            $html .= '<span class="loopparagraph_copyright">' . $args['copyright'] . '</span>';
        }

        $html .= '</blockquote></div>';

        return $html;
    }

}