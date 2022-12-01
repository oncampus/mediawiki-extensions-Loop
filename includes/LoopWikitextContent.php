<?php
/**
  * @description Cloned WikitextContent objects
  * @author Dennis Krohn <dennis.krohn@th-luebeck.de>
  */

if ( !defined( 'MEDIAWIKI' ) ) die ( "This file cannot be run standalone.\n" );

use MediaWiki\Content\Transform\PreSaveTransformParams;
use MediaWiki\MediaWikiServices;

class LoopWikitextContentHandler extends WikitextContentHandler {

/*
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
	public function preSaveTransform( Content $content,	PreSaveTransformParams $pstParams) : Content {#Title $title, User $user, ParserOptions $popts ) {
		$text = $content->getText();

		$parser = MediaWikiServices::getInstance()->getParserFactory()->getInstance();
		$pst = $parser->preSaveTransform(
			$text,
			$pstParams->getPage(),
			$pstParams->getUser(),
			$pstParams->getParserOptions()
		);

		# Custom Hook for changing content before it's saved
		$hookContainer = MediaWikiServices::getInstance()->getHookContainer();
		$hookContainer->run( 'PreSaveTransformComplete', [ &$pst, $pstParams->getPage(), $pstParams->getUser() ] );

		if ( $text === $pst ) {
			return $content;
		}

		$contentClass = $this->getContentClass();
		$ret = new $contentClass( $pst );
		$ret->setPreSaveTransformFlags( $parser->getOutput()->getAllFlags() );
		return $ret;

		/*
	   	$text = $content->getText();
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
		*/
   	}
}
