<?php
/**
 * @description 
 * @ingroup Extensions
 * @author Dennis Krohn @krohnden <dennis.krohn@th-luebeck.de>
 */
if ( !defined( 'MEDIAWIKI' ) ) {
    die( "This file cannot be run standalone.\n" );
}

use MediaWiki\MediaWikiServices;

class LoopIndex {

    public static function onParserSetup( Parser $parser ) {
		$parser->setHook ( 'loop_index', 'LoopIndex::renderLoopIndex' ); 
		return true;
    }	
    
	static function renderLoopIndex( $input, array $args, Parser $parser, PPFrame $frame ) {
    
        
        return "";
    }

}

class SpecialLoopIndex extends SpecialPage {

	public function __construct() {
		parent::__construct( 'LoopIndex' );
	}

	public function execute( $sub ) {
		
		$out = $this->getOutput();
		$request = $this->getRequest();
		$user = $this->getUser();
        Loop::handleLoopRequest( $out, $request, $user ); #handle editmode

        
    }
        
	/**
	 * Specify the specialpages-group loop
	 *
	 * @return string
	 */
	protected function getGroupName() {
		return 'loop';
	}
}