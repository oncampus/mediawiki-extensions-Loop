<?php
/**
 * @description Adds content tag <loop_accordion>
 * @ingroup Extensions
 * @author Dennis Krohn <dennis.krohn@th-luebeck.de>
 */
if ( !defined( 'MEDIAWIKI' ) ) {
    die( "This file cannot be run standalone.\n" );
}

class LoopAccordion {

	public static function onParserSetup( Parser $parser ) {
		$parser->setHook( 'loop_accordion', 'LoopAccordion::renderLoopAccordion' );
		return true;
	}
	
	static function renderLoopAccordion( $input, array $args, Parser $parser, PPFrame $frame ) {
        
        $html = '';
        $parser->extractTagsAndParams ( array( "loop_row" ), $input, $row_matches );

        foreach ( $row_matches as $row ) {
            #dd($row);
            $title_matches = array();
            $parser->extractTagsAndParams ( array( "loop_title" ), $row[1], $title_matches );
            $title_content = "";
            foreach ( $title_matches as $title ) {
                if ( array_key_exists( 1, $title ) ) {
                    $title_content = $parser->recursiveTagParse( $title[1], $frame );
                }
            }
            #dd($row, $title_matches, $title, $title_content);
            #$content
            $id = uniqid();
            $html .= '<div class="accordion">';
            $html .= '<input id="acc-'.$id.'" type="checkbox" name="acc">';
            $html .= '<label for="acc-'.$id.'">'.$title_content.'</label>';
            $html .= '<div class="accordion_content">';
            $html .= $parser->recursiveTagParse( $row[1], $frame );
            $html .= '</div>';
            $html .= '</div>';
        }
        

		return $html;
    }
}