<?php
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
		
		$html = '';
		if ( isset( $args['button'] ) || $loopeditmode ) {
			if( $loopeditmode || $args['button'] !== "false" ) {
				$parser->getOutput()->addModules( 'loop.print.js' );
				$btnId = uniqid();
				$btnIcon = '<span class="ic ic-print-area float-none"></span>';
				$html = '<div class="loopprint-container loopprint-button">';
				$html .= '<span class="loopprint-tag '. $btnId.'" data-title="'.wfMessage('loopprint-printingarea')->text().'">' . $btnIcon . '</span>';
				$html .= '<div class="loopprint-content" id="'. $btnId .'">' . $parser->recursiveTagParse( $input, $frame ) . '</div>';
				$html .= '</div>';	
			}
		}
			
		return $html;
	}

}