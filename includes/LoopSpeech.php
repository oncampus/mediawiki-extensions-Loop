<?php
/**
 * @description Hide <loop_speech> add extra content only for the ssml service
 * @ingroup Extensions
 * @author Daniel Lembcke
 */

if ( !defined( 'MEDIAWIKI' ) ) die ( "This file cannot be run standalone.\n" );

class LoopSpeech {
	var $input='';
	var $args=array();

	public static function onParserSetup( Parser $parser) {
		$parser->setHook( 'loop_speech', 'LoopSpeech::renderLoopSpeech' );

		return true;
	}

	static function renderLoopSpeech( $input, array $args, Parser $parser, PPFrame $frame ) {
		$return='';
		$speech_id=uniqid();
		$return.='<div style="display:none" class="speecharea" id="'.$speech_id.'">';
		$output = $parser->recursiveTagParse( $input);
		$return.= $output;
		$return.= '</div>';

		return $return;
	}

}
