<?php
#TODO MW 1.35 DEPRECATION
/**
  * @description Content as for example notes or comments only for displaying in wikitext.
  * @ingroup Extensions
  * @author Dennis Krohn <dennis.krohn@th-luebeck.de>
  */

if ( !defined( 'MEDIAWIKI' ) ) {
    die( "This file cannot be run standalone.\n" );
}

class LoopComment {

    public static function onParserSetup( Parser $parser) {
        $parser->setHook( 'loop_comment', 'LoopComment::renderLoopComment' );

		return true;
    }

	static function renderLoopComment( $input, array $args, Parser $parser, PPFrame $frame ) {

        global $wgOut;
		$user = $wgOut->getUser();
		$loopeditmode = $user->getOption( 'LoopEditMode', false, true );

        if ( !$loopeditmode ) {
            return "";
        }

        $html = '<div class="loopnorender-container loopeditmode-hint font-italic alert-secondary"  data-original-title="'.wfMessage('loop-editmode-hint')->text().'">';
		$html .= $parser->recursiveTagParse( "<nowiki>$input</nowiki>" );
        $html .= '</div>';

		return $html;

	}

}
