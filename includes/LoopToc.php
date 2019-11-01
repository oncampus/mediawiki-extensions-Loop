<?php
/**
 * @description Adds TOC
 * @ingroup Extensions
 * @author Dustin Neß <dustin.ness@th-luebeck.de>
 */

if ( !defined( 'MEDIAWIKI' ) ) die ( "This file cannot be run standalone.\n" );

use MediaWiki\MediaWikiServices;

class LoopToc extends LoopStructure {

    static function onParserSetup( Parser $parser ) {
        $parser->setHook( 'loop_toc', 'LoopToc::renderLoopToc' );
		return true;
    }

	static function renderLoopToc( $input, array $args, Parser $parser, PPFrame $frame ) {
		$structure = new LoopStructure();
		$structure->getStructureItems();
		
		$result = $structure->renderAsSimpleStructure();

        $return  = '<div class="looptoc">';
        $return .= $result;
        $return .= '</div>';
        return $return;
	}

}