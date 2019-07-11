<?php

if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file cannot be run standalone.\n" );
}


use MediaWiki\MediaWikiServices;

class LoopLiterature {

    public static function onParserSetup( Parser $parser ) {
		$parser->setHook ( 'cite', 'LoopLiterature::renderCite' ); 
		$parser->setHook ( 'loop_literature', 'LoopLiterature::renderLoopLiterature' );
		return true;
	}	
	
	public static function getShowLiterature() {
		
		global $wgOut;

		$showLiterature = false;
		
		$user = $wgOut->getUser();
		$editMode = $user->getOption( 'LoopEditMode', false, true );

		if ( $editMode ) {
			
			$showLiterature = true;

		} else {
			#$literatureItems = self::getLiteratureItems();
	
			#if ( $literatureItems ) {
				$showLiterature = true;
			#}
		}

		return $showLiterature;
	}

	static function renderCite( $input, array $args, Parser $parser, PPFrame $frame ) {
		return true;
	}
	static function renderLoopLiterature( $input, array $args, Parser $parser, PPFrame $frame ) {
		return true;
	}
    
}

class SpecialLoopLiterature extends SpecialPage {

	public function __construct() {
		parent::__construct( 'LoopLiterature' );
	}

	public function execute( $sub ) {
		$user = $this->getUser();

		$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
		$out = $this->getOutput();

		if ( $user->isAllowed('loop-edit-literature') ) {
			
		}
		$html = "Hello world!";

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

class SpecialLoopLiteratureEdit extends SpecialPage {

	public function __construct() {
		parent::__construct( 'LoopLiteratureEdit' );
	}

	public function execute( $sub ) {
		$user = $this->getUser();

		if ( $user->isAllowed('loop-edit-literature') ) {
			
			$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
			$out = $this->getOutput();

			$html = "Hello world!";

			$out->addHTML( $html );
		}
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

class SpecialLoopLiteratureImport extends SpecialPage {

	public function __construct() {
		parent::__construct( 'LoopLiteratureImport' );
	}

	public function execute( $sub ) {
		$user = $this->getUser();

		if ( $user->isAllowed('loop-edit-literature') ) {
			
			$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
			$out = $this->getOutput();

			$html = "Hello world!";

		}
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

