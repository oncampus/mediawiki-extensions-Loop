<?php

if ( !defined( 'MEDIAWIKI' ) ) die ( "This file cannot be run standalone.\n" );

use MediaWiki\MediaWikiServices;

class LoopWikiPre
{
	public static function onParserSetup( Parser $parser ) {
		$parser->setHook( 'nowiki_pre', 'LoopWikiPre::renderTag' );
		return true;
	}

	static function renderTag($input, array $args, Parser $parser, PPFrame $frame )
	{
		return '<pre>' . htmlspecialchars($input) . '</pre>';
	}


}
