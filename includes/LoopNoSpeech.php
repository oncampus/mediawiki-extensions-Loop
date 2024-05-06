<?php
/**
 * @description Hide <loop_nospeech> from ssml service.
 * @ingroup Extensions
 * @author Daniel Lembcke
 */

if ( !defined( 'MEDIAWIKI' ) ) die ( "This file cannot be run standalone.\n" );

class LoopNoSpeech {
	var $input='';
	var $args=array();

	public static function onParserSetup( Parser $parser) {
		$parser->setHook( 'loop_nospeech', 'LoopNoSpeech::renderLoopNoSpeech' );

		return true;
	}

	static function renderLoopNoSpeech( $input, array $args, Parser $parser, PPFrame $frame ) {
		$return='';
		$nospeech_id=uniqid();
		$return.='<div class="nospeecharea" id="'.$nospeech_id.'">';
		$output = $parser->recursiveTagParse( $input);
		$return.= $output;
		$return.= '</div>';

		return $return;
	}

}
