<?php

if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file cannot be run standalone.\n" );
}


use MediaWiki\MediaWikiServices;

class LoopGlossary {

	public function hasObjects( $type ) {
		$objects = LoopObjectIndex::getObjectsOfType ( $type );
		$structureItems = $this->getStructureItems();

		foreach ( $structureItems as $item ) {
			if ( isset ( $objects[$item->article] ) ) {
				return true;
			}
		}

		return false;
	}
	
}

class SpecialLoopGlossary extends SpecialPage {

	public function __construct() {
		parent::__construct( 'LoopGlossary' );
	}

	public function execute( $sub ) {
		$user = $this->getUser();

		$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
		$out = $this->getOutput();

		
		$dbr = wfGetDB( DB_REPLICA );
		$res = $dbr->select(
			array(
				'page'
			),
			array(
				'page_id',
				'page_namespace'
			),
			array(
				'page_namespace = 3000'
			),
			__METHOD__
		);
		dd($res);


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