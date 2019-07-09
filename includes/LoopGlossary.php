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

		$glossaryItems = self::getGlossaryPages();

		if ( $glossaryItems ) {
			$showGlossary = true;
		}

		return $showGlossary;
	}

	// returns all pages in glossary namespace. 
	// @param String $returnType if null, function will return all information. "idArray" only returns glossary article ids
	public static function getGlossaryPages( $returnType = null ) {

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
				'page_namespace = ' . NS_GLOSSARY
			),
			__METHOD__
		);
		$glossaryItems = array();
		foreach ( $res as $row ) {
			if ( $returnType == "idArray" ) {
				array_push( $glossaryItems, $row->page_id );
			} else {
				$glossaryItems[ $row->page_title ] = Title::newFromId ( $row->page_id );
			}
		}
		
		if ( ! empty( $glossaryItems ) ) {
			sort( $glossaryItems );
			return $glossaryItems;
		} else {
			return false;
		}
	}

	// removes "cache" for 
	public static function updateGlossaryPageTouched() {
		$article_ids = self::getGlossaryPages("idArray");
		// Update page_touched 
		if ( $article_ids ) {
			$article_ids = array_unique ( $article_ids );
			$dbw = wfGetDB ( DB_MASTER );
				
			$dbPageTouchedResult = $dbw->update ( 'page', array (
					'page_touched' => $dbw->timestamp()
			), array (
					0 => 'page_id in (' . implode ( ',', $article_ids ) . ')'
			), __METHOD__ );
		}
		return true;
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
		
		$glossaryItems = LoopGlossary::getGlossaryPages();
		
		if ( $glossaryItems ) {
			$html .= '<div class="list-group list-group-flush">';
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