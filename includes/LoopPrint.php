<?php
/**
  * @description Display printing area with <loop_print> tag.
  * @ingroup Extensions
  * @author Dustin Neß <dustin.ness@th-luebeck.de>
  */

if ( !defined( 'MEDIAWIKI' ) ) die ( "This file cannot be run standalone.\n" );

use MediaWiki\MediaWikiServices;

class LoopPrint {

	public static function onParserSetup( Parser $parser ) {
		$parser->setHook( 'loop_print', 'LoopPrint::renderLoopPrint' );
		return true;
	}

	static function renderLoopPrint( $input, array $args, Parser $parser, PPFrame $frame ) {
		global $wgOut;
		$user = $wgOut->getUser();
		$userOptionsLookup = MediaWikiServices::getInstance()->getUserOptionsLookup();
		$editMode = $userOptionsLookup->getOption( $user, 'LoopEditMode', false, true );

		$button = array_key_exists( 'button', $args ) ? $args['button'] : "true";
		$html = '';
		if ( strtolower( $button ) === "true" || $editMode ) {
			if( $editMode || $button !== "false" ) {
				$btnId = uniqid();
				$btnIcon = '<span class="ic ic-print-area float-none"></span>';
				$editModeClass = $editMode ? " loopeditmode-hint" : "";
				$html = '<div class="loopprint-container loopprint-button">';
				$html .= '<input id="'. $btnId .'" type="checkbox">';
				$html .= '<label for="'. $btnId .'" class="mb-0"><span data-title="'.wfMessage('loopprint-printingarea')->text().'" class="loopprint-tag '. $btnId;
				$html .= ( $editMode && $button === "false" ) ? ' loopeditmode-hint" data-original-title="'.wfMessage('loop-editmode-hint')->text().'"' : '"';
				$html .= '>' . $btnIcon . '<span class="loopprint-button-text pl-1">'.wfMessage('loopprint-printingarea')->text().'</span></span></label>';
				$html .= '<div class="loopprint-content pb-1">' . $parser->recursiveTagParse( $input, $frame ) . '</div>';
				$html .= '</div>';
			}
		}

		return $html;
	}

}
