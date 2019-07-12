<?php

class LoopPrint {
	
	public static function onParserSetup( Parser $parser ) {
		$parser->setHook( 'loop_print', 'LoopPrint::renderLoopPrint' );
		return true;
	}
	
	static function renderLoopPrint( $input, array $args, Parser $parser, PPFrame $frame ) {
		global $wgOut;
		$user = $wgOut->getUser();
		$loopeditmode = $user->getOption( 'LoopEditMode' ,false, true );	

		$html = '';
		if ( $loopeditmode ) {
			$html .= '<div class="loopprint-container">';
			$html .= '<span class="loopprint-tag"><span class="ic-print-area"></span>Druckbereich</span>'; // Todo: translation
			$html .= '<div class="loopprint-content">' . $parser->recursiveTagParse( $input, $frame ) . '</div>';
			$html .= '</div>';		
		}

		return $html;
	}

}