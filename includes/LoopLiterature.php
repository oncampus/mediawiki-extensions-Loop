<?php

use MediaWiki\MediaWikiServices;

class LoopLiterature {

    public static function onParserSetup( Parser $parser ) {
		$parser->setHook ( 'cite', 'LoopLiterature::renderCite' ); 
		$parser->setHook ( 'loop_literature', 'LoopLiterature::renderLoopLiterature' );
		return true;
	}	
	
	static function renderCite( $input, array $args, Parser $parser, PPFrame $frame ) {
		return true;
	}
	static function renderLoopLiterature( $input, array $args, Parser $parser, PPFrame $frame ) {
		return true;
	}
    
}