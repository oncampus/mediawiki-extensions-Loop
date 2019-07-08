<?php
/**
 * @author Dennis Krohn @krohnden
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file cannot be run standalone.\n" );
}

use MediaWiki\MediaWikiServices;

class LoopGlossary {
	
	public static function getShowGlossary() {
		
		$showGlossary = false;

		$dbr = wfGetDB( DB_REPLICA );
		$res = $dbr->select(
			array(
				'page'
			),
			array(
				'page_id'
			),
			array(
				'page_namespace = 3000'
			),
			__METHOD__
		);
		foreach ( $res as $row ) {
			$showGlossary = true;
			break;
		}

		return $showGlossary;
	}
	
}

class SpecialLoopGlossary extends SpecialPage {

	public function __construct() {
		parent::__construct( 'LoopGlossary' );
	}

	public function execute( $sub ) {
		
		$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
		$user = $this->getUser();
		$out = $this->getOutput();

		$out->setPageTitle(wfMessage('loop-glossary-title'));
		$html = '<h1>' . wfMessage('loop-glossary-title') . '</h1>' ;
		$glossaryItems = array();
		
		$dbr = wfGetDB( DB_REPLICA );
		$res = $dbr->select(
			array(
				'page'
			),
			array(
				'page_id',
				'page_title'
			),
			array(
				'page_namespace = 3000'
			),
			__METHOD__
		);
		foreach ( $res as $row ) {
			$glossaryItems[ $row->page_title ] = Title::newFromId ( $row->page_id );
		}
		
		if ( ! empty( $glossaryItems ) ) {
			$html .= '<div class="list-group list-group-flush">';
			sort( $glossaryItems );
			foreach ( $glossaryItems as $pageTitle => $titleObject ) {

				$html .= $linkRenderer->makeLink(
					$titleObject,
					$titleObject->mTextform,
					array( 'class' => 'list-group-item list-group-item-action' )
				);
			}
			$html .= '</div>';

		} else {
			$html .= wfMessage('loop-glossary-empty');
		}

		$out->addHTML( $html );

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