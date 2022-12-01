<?php
/**
 * Special Page for full text search including special characters that the regular search misses
 * @author Dennis Krohn @krohnden <dennis.krohn@th-luebeck.de>
 */
if ( !defined( 'MEDIAWIKI' ) ) die ( "This file cannot be run standalone.\n" );

use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\SlotRecord;

class SpecialLoopSearch extends SpecialPage {

	function __construct() {
		parent::__construct( 'LoopSearch' );
	}

	function execute( $par ) {

		$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
		$linkRenderer->setForceArticlePath(true);
		$permissionManager = MediaWikiServices::getInstance()->getPermissionManager();

		$user = $this->getUser();
		$out = $this->getOutput();
		$request = $this->getRequest();
		$out->setPageTitle( $this->msg( 'loopsearch' ) );
		$this->setHeaders();

		$html = '<h3>' . $this->msg( 'loopsearch' ) . '</h3>';


		if ( $permissionManager->userHasRight( $user, "read" ) ) {

			$html .= '<div class="form-row">';
			$html .= '<div class="col-12">';
			$html .= "<p>". $this->msg( 'loopsearch-description' ) ."</p>";
			$html .= '<form class="mw-editform mt-3 mb-3" id="loopupdate-form" method="post" novalidate enctype="multipart/form-data">';
			$html .= '<input placeholder="'.$this->msg( 'loopsearch-label' ).'" type="text" name="term" id="term" class="mr-1 form-control">';
			$html .= '<input type="submit" class="mw-htmlform-submit mw-ui-button mw-ui-primary mw-ui-progressive mt-2 d-block" id="submit" value="' . $this->msg( 'search' ) . '"></input>';

			$html .= '</div>';
			$html .= '</div>';
			$html .= '</form>';

			$term = $request->getText("term");
			if ( ! empty ( $term ) ) {

				$html .= $this->msg( 'loopsearch-results', $term ) . "<br>";
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
						'page_namespace = 0'
					),
					__METHOD__
				);

				foreach( $res as $row ) {
					$title = Title::newFromId( $row->page_id, NS_MAIN );
					$tmpFPage = new FlaggableWikiPage ( Title::newFromId( $row->page_id, NS_MAIN ) );
					$stableRev = $tmpFPage->getStable();
					if ( $stableRev == 0 ) {
						$stableRev = $tmpFPage->getRevisionRecord()->getId();
					}

					$latestRevId = $title->getLatestRevID();
					$wikiPage = MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle( $title );
					$fwp = new FlaggableWikiPage ( $title );


					if ( isset( $fwp ) ) {
						$stableRevId = $fwp->getStable();

						if ( $stableRevId == null ) { # page is stable or does not have any stable version
							$pageContent = $wikiPage->getContent(); #latest, but not stable
							$contentText = ContentHandler::getContentText( $pageContent );
						} else {
							$revision = $wikiPage->getRevisionRecord();

							$pageContent = $revision->getContent( SlotRecord::MAIN );
							$contentText = ContentHandler::getContentText( $pageContent );
						}

						preg_match_all("/($term)/i", $contentText, $output_array); #br with id

						if ( !empty( $output_array[0] ) ) {

							$html .= $linkRenderer->makelink(
								$title,
								new HtmlArmor( $title->getText() ),
								array(),
								array("action"=>"edit")
							)."<br>";

						}
					}
				}
			}

		} else {
			$html = '<div class="alert alert-warning" role="alert">' . $this->msg( 'specialpage-no-permission' ) . '</div>';
		}
		$out->addHTML( $html );
	}
	protected function getGroupName() {
		return 'loop';
	}
}
