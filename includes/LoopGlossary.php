<?php
#TODO MW 1.35 DEPRECATION
/**
 * @author Dennis Krohn @krohnden
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file cannot be run standalone.\n" );
}

use MediaWiki\MediaWikiServices;

class LoopGlossary {

	public static function getShowGlossary() {

		global $wgOut;

		$user = $wgOut->getUser();
		$editMode = $user->getOption( 'LoopEditMode', false, true );

		$glossaryItems = self::getGlossaryPages();
		if ( $glossaryItems ) {
			return true;
		} elseif ( $editMode ) {
			return "empty";
		} else {
			return false;
		}
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
			return array();
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

		$out = $this->getOutput();
		$request = $this->getRequest();
		$user = $this->getUser();
		Loop::handleLoopRequest( $out, $request, $user ); #handle editmode

		$out->setPageTitle(wfMessage('loopglossary'));
		$html = '';

		$requestToken = $request->getText( 't' );
		if ( !empty( $requestToken ) ) {
			$html .= $this->makeGlossaryPageFromRequest( $user, $request );

		}


		$html .= self::renderLoopGlossarySpecialPage( $request, $user );

		if ( empty ( $html ) ) {
			$html .= wfMessage('loop-glossary-empty')->text();
		}

		$out->addHTML( $html );

    }

    public static function renderLoopGlossarySpecialPage( $request = null, $user = null ) {

        $linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
        $linkRenderer->setForceArticlePath(true);
        $glossaryItems = LoopGlossary::getGlossaryPages();
        $html = '';

        $html .= '<h1>';
        $html .= wfMessage( 'loopglossary' )->text();
        $html .= '</h1>';

		if ( isset( $request ) && isset( $user ) ) {
			global $wgSecretKey;
			$saltedToken = $user->getEditToken( $wgSecretKey, $request );

			$editMode = $user->getOption( 'LoopEditMode', false, true );
			if ( $editMode && $user->isAllowed('edit') ) {

				$html .= '<form class="mw-editform mt-3 mb-3" id="glossary-entry-form"  enctype="multipart/form-data">';

				$html .= '<div class="form-row">';
				$html .= '<input type="hidden" name="t" id="loopglossary-token" value="' . $saltedToken . '"></input>';

				$html .= '<input type="text" name="pagetitle" id="loopglossary-pagetitle" class="form-control" placeholder="'. wfMessage( "loopglossary-input-placeholder" )->text() .'"></input>';

				$html .= '<div class="w-100">';
				$html .= '<input type="submit" class="mw-htmlform-submit mw-ui-button mw-ui-primary mw-ui-progressive mt-2 d-block float-right" id="loopglossary-submit" value="' . wfMessage( 'submit' )->text() . '"></input>';
				$html .= '</div>';
				$html .= '</div>';
				$html .= '</form>';
			}
		}


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

        }
        return $html;
    }

	public function makeGlossaryPageFromRequest ( $user, $request ) {

		global $wgSecretKey;
		$requestToken = $request->getText( 't' );
		$titleText = $request->getText( 'pagetitle' );

		if ( $user->matchEditToken( $requestToken, $wgSecretKey, $request )  ) {
			if ( !empty( $titleText ) ) {
				$title = Title::newFromText($titleText, NS_GLOSSARY );

				if ( $title->getArticleID() === 0 ) { // 0 when the page does not exist
					$newGlossaryPage = WikiPage::factory( Title::newFromText( $title->mTextform, NS_GLOSSARY ));
					$newGlossaryPageContent = new WikitextContent( wfMessage ("loopstructure-default-newpage-content" )->text() ); #todo
					$newGlossaryPageUpdater = $newGlossaryPage->newPageUpdater( $user );
					$summary = CommentStoreComment::newUnsavedComment( 'New Glossary page' );
					$newGlossaryPageUpdater->setContent( "main", $newGlossaryPageContent );
					$newGlossaryPageUpdater->saveRevision ( $summary, EDIT_NEW );

					return '<div class="alert alert-success" role="alert">' . wfMessage( "loopglossary-alert-saved", $title->mTextform ) . '</div>';
				} else {
					return '<div class="alert alert-danger" role="alert">' . wfMessage( "loopglossary-alert-pageexists-error", $title->mTextform ) . '</div>';
				}
			} else {
				return "";
			}


		} else {
			return '<div class="alert alert-danger" role="alert">' . wfMessage( "loop-token-error" )->text() . '</div>';

		}
		return "unknown error";
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
