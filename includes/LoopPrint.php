<?php
#TODO MW 1.35 DEPRECATION
/**
  * @description Display printing area with <loop_print> tag.
  * @ingroup Extensions
  * @author Dustin Neß <dustin.ness@th-luebeck.de>
  */

if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file cannot be run standalone.\n" );
}

class LoopPrint {

	public static function onParserSetup( Parser $parser ) {
		$parser->setHook( 'loop_print', 'LoopPrint::renderLoopPrint' );
		return true;
	}

	static function renderLoopPrint( $input, array $args, Parser $parser, PPFrame $frame ) {
		global $wgOut;
		$user = $wgOut->getUser();
		$loopeditmode = $user->getOption( 'LoopEditMode', false, true );

		$button = array_key_exists( 'button', $args ) ? $args['button'] : "true";
		$html = '';
		if ( strtolower( $button ) === "true" || $loopeditmode ) {
			if( $loopeditmode || $button !== "false" ) {
				$btnId = uniqid();
				$btnIcon = '<span class="ic ic-print-area float-none"></span>';
				$editModeClass = $loopeditmode ? " loopeditmode-hint" : "";
				$html = '<div class="loopprint-container loopprint-button">';
				$html .= '<input id="'. $btnId .'" type="checkbox">';
				$html .= '<label for="'. $btnId .'" class="mb-0"><span data-title="'.wfMessage('loopprint-printingarea')->text().'" class="loopprint-tag '. $btnId;
				$html .= ( $loopeditmode && $button === "false" ) ? ' loopeditmode-hint" data-original-title="'.wfMessage('loop-editmode-hint')->text().'"' : '"';
				$html .= '>' . $btnIcon . '<span class="loopprint-button-text pl-1">'.wfMessage('loopprint-printingarea')->text().'</span></span></label>';
				$html .= '<div class="loopprint-content pb-1">' . $parser->recursiveTagParse( $input, $frame ) . '</div>';
				$html .= '</div>';
			}
		}

		return $html;
	}

}
