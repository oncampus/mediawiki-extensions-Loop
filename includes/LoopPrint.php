<?php
/**
  * @description Display printing area with <loop_print> tag.
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
		$parser->getOutput()->addModules( 'loop.print.js' );
		$btnIcon = '<span class="ic-print-area"></span>';
		
		$btnId = uniqid();
		$btnTrue = '';
		if ( isset( $args['button'] ) ) {
			if($args['button'] == true) {
				$btnTrue = 'loopprint-button';
			}
		}

		if(!$loopeditmode) $btnTrue = 'loopprint-button';

		$html = '<div class="loopprint-container '. $btnTrue .'">';
		$html .= '<span class="loopprint-tag '. $btnId.'" data-title="'.wfMessage('loopprint-printingarea')->text().'">' . $btnIcon . '</span>';
		$html .= '<div class="loopprint-content" id="'. $btnId .'">' . $parser->recursiveTagParse( $input, $frame ) . '</div>';
		$html .= '</div>';		

		return $html;
	}

}