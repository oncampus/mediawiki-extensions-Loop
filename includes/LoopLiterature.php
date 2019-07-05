<?php

use MediaWiki\MediaWikiServices;

class LoopLiterature {

    public static function onParserSetup(Parser $parser) {
		$parser->setHook ( 'cite', 'LoopLiterature::renderCite' );
		return true;
    }	
    
}