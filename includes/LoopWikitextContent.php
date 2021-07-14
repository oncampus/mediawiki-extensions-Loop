<?php
/**
  * @description Cloned WikitextContent objects
  * @author Dennis Krohn <dennis.krohn@th-luebeck.de>
  */

if ( !defined( 'MEDIAWIKI' ) ) die ( "This file cannot be run standalone.\n" );

use MediaWiki\MediaWikiServices;

class LoopWikitextContentHandler extends WikitextContentHandler {
    protected function getContentClass() {
        return 'LoopWikitextContent';
    }
}

class LoopWikitextContent extends WikitextContent {
	/**
	* Copied from WikitextContent.php, overriding it with our own content and a custom Hook
	*
	* Returns a Content object with pre-save transformations applied using
	* Parser::preSaveTransform().
	*
	* @param Title $title
	* @param User $user
	* @param ParserOptions $popts
	*
	* @return Content
	*/
	public function preSaveTransform( Title $title, User $user, ParserOptions $popts ) {

	   	$text = $this->getText();
		$parserFactory = MediaWikiServices::getInstance()->getParserFactory();
		$parser = $parserFactory->create();
	   	$pst = $parser->preSaveTransform( $text, $title, $user, $popts );

		# Custom Hook for changing content before it's saved
	   	$hookContainer = MediaWikiServices::getInstance()->getHookContainer();
		$hookContainer->run( 'PreSaveTransformComplete', [ &$pst, $title, $user ] );

		if ( $text === $pst ) {
			return $this;
		}
		$ret = new static( $pst );
		if ( $parser->getOutput()->getFlag( 'user-signature' ) ) {
			$ret->hadSignature = true;
		}
		return $ret;
   	}
}
