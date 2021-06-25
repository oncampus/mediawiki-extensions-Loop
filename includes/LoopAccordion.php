<?php
/**
 * @description Adds content tag <loop_accordion>
 * @ingroup Extensions
 * @author Dennis Krohn <dennis.krohn@th-luebeck.de>
 */
if ( !defined( 'MEDIAWIKI' ) ) die ( "This file cannot be run standalone.\n" );

class LoopAccordion {

	public static function onParserSetup( Parser $parser ) {
		$parser->setHook( 'loop_accordion', 'LoopAccordion::renderLoopAccordion' );
		return true;
	}

	static function renderLoopAccordion( $input, array $args, Parser $parser, PPFrame $frame ) {

        $html = '';
        $parser->extractTagsAndParams ( array( "loop_row" ), $input, $row_matches );

        $html .= '<div class="loop-accordion mb-3">';
        foreach ( $row_matches as $row ) {
            $title_matches = array();
            $parser->extractTagsAndParams ( array( "loop_title" ), $row[1], $title_matches );
            $title_content = "";
            foreach ( $title_matches as $title ) {
                if ( array_key_exists( 1, $title ) ) {
                    $title_content = $parser->recursiveTagParse( $title[1], $frame );
                }
            }
            $id = uniqid();
            $html .= '<div class="accordion-row w-100 mb-1 overflow-hidden">';
            $html .= '<input id="acc-'.$id.'" type="checkbox" name="acc">';
            $html .= '<label for="acc-'.$id.'" class="d-block cursor-pointer mb-0 mr-2 w-100 p-2 pl-2">'.$title_content.'</label>';
            $html .= '<div class="accordion-content overflow-hidden">';
            $html .= '<div class="m-2 m-md-3">'.$parser->recursiveTagParse( $row[1], $frame ).'</div>';
            $html .= '</div>';
            $html .= '</div>';
        }
        $html .= '</div>';


		return $html;
    }
}
