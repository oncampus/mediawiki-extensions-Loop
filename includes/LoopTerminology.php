<?php
/**
 * Class for Lingo extension implementation https://www.mediawiki.org/wiki/Extension:Lingo
 * @author Dennis Krohn @krohnden <dennis.krohn@th-luebeck.de>
 */
if ( !defined( 'MEDIAWIKI' ) ) die ( "This file cannot be run standalone.\n" );

use MediaWiki\MediaWikiServices;
use MediaWiki\Session\CsrfTokenSet;

class LoopTerminology {

    public static function getShowTerminology() {

        global $wgOut;


        $contentText = LoopTerminology::getTerminologyWikiText();
		$user = $wgOut->getUser();
		$userOptionsLookup = MediaWikiServices::getInstance()->getUserOptionsLookup();
		$editMode = $userOptionsLookup->getOption( $user, 'LoopEditMode', false, true );

		if ( !empty( $contentText ) ) {
			return true;
		} elseif ( $editMode ) {
			return "empty";
		} else {
			return false;
		}

    }

    public static function getSortedTerminology( $input ) {
        $items = array();
        $dom = new DomDocument();
        $dom->loadXml($input);
        $tags = $dom->getElementsByTagName( "dl" );
        foreach ( $tags as $tag ) {
            $childNodes = $tag->childNodes;
            if ( !empty( $childNodes ) ) {
                $currentELementTitle = trim($childNodes[0]->nodeValue);
                foreach ( $childNodes as $child ) {
					if ($child->hasChildNodes()) { # math node
						$tmpVal = str_replace( "\n", "", $child->nodeValue );
						$tmpVal = preg_replace('/\s*(\S*)\s*{.*}/', '$1', $tmpVal);
						$items[ $currentELementTitle ][ $child->nodeName ][] = $tmpVal;

					} else {
						$items[ $currentELementTitle ][ $child->nodeName ][] = $child->nodeValue;
					}
                }
            }
		}
        return $items;
    }

    public static function getTerminologyPageContentText() {

		$parserFactory = MediaWikiServices::getInstance()->getParserFactory();
        $parser = $parserFactory->create();
        $tmpTitle = Title::newFromText( 'NO TITLE' );
		$tmpUser = new User();
        $parserOutput = $parser->parse("{{Mediawiki:LoopTerminologyPage}}", $tmpTitle, new ParserOptions( $tmpUser ) );
        $output = $parserOutput->getText();

        return $output;
    }

    public static function getTerminologyOutput() {

        $contentText = self::getTerminologyPageContentText();
        $items = self::getSortedTerminology( $contentText );

        $html = '';
        if ( !empty( $items ) ) {
            ksort( $items, SORT_FLAG_CASE | SORT_STRING ); # ignore case
            foreach ( $items as $item => $content ) {
                if ( array_key_exists( "dt", $content ) &&  array_key_exists( "dd", $content ) ) {
                    $html .= "<div class='loopterminology-term font-weight-bold'><span>";
                    $i = 0;
                    foreach ( $content["dt"] as $term ) {
                        $html .= ( $i == 0 ? "" : ", " );
                        $html .= $term;
                        $i++;
                    }
                    $html .= "</span></div>\n";
                    $html .= "<div class='loopterminology-definition'>";
                    foreach ( $content["dd"] as $def ) {
                        $html .= "<span>" . $def . "</span><br>\n";
                    }
                    $html .= "</div>\n";
                }
            }
        }

        return $html;

    }

    public static function getTerminologyOutputForXML() {

        $parserFactory = MediaWikiServices::getInstance()->getParserFactory();
		$parser = $parserFactory->create();
        $tmpTitle = Title::newFromText( 'NO TITLE' );
		$tmpUser = new User();
        $parserOutput = $parser->parse("{{Mediawiki:LoopTerminologyPage}}", $tmpTitle, new ParserOptions($tmpUser) );
        $output = $parserOutput->getText();

        $items = self::getSortedTerminology( $output );

        $html = '';
        if ( !empty( $items ) ) {
            ksort( $items );
            foreach ( $items as $item => $content ) {
                if ( array_key_exists( "dt", $content ) &&  array_key_exists( "dd", $content ) ) {
                    $html .= "<div class='loopterminology-term font-weight-bold'><span>";
                    $i = 0;
                    foreach ( $content["dt"] as $term ) {
                        $html .= ( $i == 0 ? "" : ", " );
                        $html .= $term;
                        $i++;
                    }
                    $html .= "</span></div>\n";
                    $html .= "<div class='loopterminology-definition'>";
                    foreach ( $content["dd"] as $def ) {
                        $html .= "<span>" . $def . "</span><br>\n";
                    }
                    $html .= "</div>\n";
                }
            }
        }

        return $html;

    }

    public static function getTerminologyWikiText() {

        $title = Title::newFromText( 'LoopTerminologyPage', NS_MEDIAWIKI );
        $wikiPage = MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle( $title );
        $revision = $wikiPage->getRevisionRecord();
        $contentWikitext = '';
        if ( $revision ) {
			$content = $wikiPage->getContent( MediaWiki\Revision\RevisionRecord::RAW );
			$contentWikitext = ContentHandler::getContentText( $content );
        }

        return $contentWikitext;

    }

}

class SpecialLoopTerminology extends SpecialPage {

	public function __construct() {
		parent::__construct ( 'LoopTerminology' );
	}

	public function execute( $sub ) {

		global $wgDefaultUserOptions;

		$out = $this->getOutput();
		$request = $this->getRequest();
        $user = $this->getUser();
		Loop::handleLoopRequest( $out, $request, $user ); #handle editmode

		$mwService = MediaWikiServices::getInstance();
		$userOptionsLookup = $mwService->getUserOptionsLookup();
		$renderMode = $userOptionsLookup->getOption( $user, 'renderMode', $wgDefaultUserOptions['LoopRenderMode'], true );
		$editMode = $userOptionsLookup->getOption( $user, 'editMode', false, true );

		$this->setHeaders();
		$out->setPageTitle( $this->msg( 'loopterminology' ) );

		$html = self::renderLoopTerminologySpecialPage( $editMode, $renderMode, $user );
        $out->addHtml ( $html );
    }

    public static function renderLoopTerminologySpecialPage( $editMode = false, $renderMode = 'default', $user = null ) {

        $html = '<h1>';
	    $html .= wfMessage( 'loopterminology' )->text();
        if ( $user ) {
    	    if( ! $user->isAnon() && $user->isAllowed( 'loop-toc-edit' ) && $renderMode == 'default' && $editMode ) {

                $linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
                $linkRenderer->setForceArticlePath(true);
    	        # show link to the edit page if user is permitted
                $html .= $linkRenderer->makeLink(
                    Title::newFromText( "LoopTerminologyEdit", NS_SPECIAL ),
                    new HtmlArmor( '<i class="ic ic-edit"></i>' ),
                    array( "class" => "ml-2", "id" => "editpagelink" )
                );
    	    }
        }
        $html .= '</h1>';
        $html .= LoopTerminology::getTerminologyOutput();

        return $html;
    }

    protected function getGroupName() {
		return 'loop';
	}
}

class SpecialLoopTerminologyEdit extends SpecialPage {

	public function __construct() {
		parent::__construct ( 'LoopTerminologyEdit' );
	}

	public function execute( $sub ) {

		global $wgSecretKey;

		$mws = MediaWikiServices::getInstance();
		$permissionManager = $mws->getPermissionManager();
		$userGroupManager = $mws->getUserGroupManager();
		$out = $this->getOutput();
		$request = $this->getRequest();
        $user = $this->getUser();
		$csrfTokenSet = new CsrfTokenSet($request);
		Loop::handleLoopRequest( $out, $request, $user ); #handle editmode
		$tabindex = 0;

		$this->setHeaders();
		$out->setPageTitle( $this->msg( 'loopterminologyedit' ) );

           # headline output
           $out->addHtml(
            Html::rawElement(
                'h1',
                array(
                    'id' => 'loopterminology-h1'
                ),
                $this->msg( 'loopterminologyedit' )->parse()
            )
        );

        $saltedToken = $csrfTokenSet->getToken( $request->getSessionId()->__tostring() );
        $newterminologyWikitext = $request->getText( 'loopterminology-content' );
		$requestToken = $request->getText( 't' );

		$userIsPermitted = (! $user->isAnon() && $permissionManager->userHasRight( $user, 'loop-toc-edit' ));
        $terminologyWikitext = LoopTerminology::getTerminologyWikiText();

		$success = null;
		$error = false;
		$feedbackMessageClass = 'success';

        if( ! empty( $requestToken ) ) {
            if ( empty( $newterminologyWikitext ) ) {
				$error = $this->msg( 'loopterminology-warning-deleted' )->parse();
                $feedbackMessageClass = 'warning';
            }
			if ( $userIsPermitted ) {
				if ( $csrfTokenSet->matchToken( $requestToken, $request->getSessionId()->__tostring() )) {

                    $systemUser = User::newFromName( 'LOOP_SYSTEM' );
                    $userGroupManager->addUserToGroup ( $systemUser, "sysop" );

                    $title = Title::newFromText( 'LoopTerminologyPage', NS_MEDIAWIKI );
                    $wikiPage = MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle( $title );
					$contentHandler = $wikiPage->getContentHandler();

                    $wikiPageContent = $contentHandler->unserializeContent( $newterminologyWikitext );
                    $wikiPageUpdater = $wikiPage->newPageUpdater( $systemUser ); # use system user to ensure editing of mediawiki namespace page is successful
                    $summary = CommentStoreComment::newUnsavedComment( $user->getName() ); #add user name to summary to ensure being able to trace back edits
					$wikiPageUpdater->setContent( "main", $wikiPageContent );
					if ( ! $wikiPage->getRevisionRecord() ) {
						$wikiPageUpdater->saveRevision ( $summary, EDIT_NEW );
					} else {
						$wikiPageUpdater->saveRevision ( $summary, EDIT_UPDATE );
					}

                    # save success output
                    $out->addHtml(
                        Html::rawElement(
                            'div',
                            array(
                                'name' => 'loopstructure-content',
                                'class' => 'alert alert-success'
                            ),
                            $this->msg( 'loopterminology-save-success' )->parse()
                        )
                    );
                    $success = true;
                } else {
					$error = $this->msg( 'loop-token-error' )->parse();
                    $feedbackMessageClass = 'danger';
				}
            } else {
				$error = $this->msg( 'loop-permission-error' )->parse();
                $feedbackMessageClass = 'danger';
			}
        }

        # error message output (if exists)
        if( $error !== false ) {
            $out->addHTML(
                Html::rawElement(
                    'div',
                    array(
                        'class' => 'alert alert-'.$feedbackMessageClass,
                        'role' => 'alert'
                    ),
                    $error
                )
            );
        }

        if( $userIsPermitted ) {

        	# user is permitted to edit the toc, print edit form here
			if ( $success ) {
				$displayedWikitext = $newterminologyWikitext;
			} else {
				$displayedWikitext = $terminologyWikitext;
			}
	        $out->addHTML(
	            Html::openElement(
	                'form',
	                array(
	                    'class' => 'mw-editform mt-3 mb-3',
	                    'id' => 'loopterminology-form',
	                    'method' => 'post',
	                    'enctype' => 'multipart/form-data'
	                )
                )
                . Html::rawElement(
	                'p',
                    array(),
                    $this->msg( 'loopterminology-hint' )->parse()
	            ) . Html::rawElement(
	                'textarea',
	                array(
	                    'name' => 'loopterminology-content',
	                    'id' => 'loopterminology-textarea',
	                    'tabindex' => ++$tabindex,
	                    'class' => 'd-block mt-3',
	                ),
	                $displayedWikitext
	            )
	            . Html::rawElement(
	                'input',
	                array(
	                    'type' => 'hidden',
	                    'name' => 't',
	                    'id' => 'loopterminology-token',
	                    'value' => $saltedToken
	                )
	            )
	            . Html::rawElement(
	                'input',
	                array(
	                    'type' => 'submit',
	                    'tabindex' => ++$tabindex,
	                    'class' => 'mw-htmlform-submit mw-ui-button mw-ui-primary mw-ui-progressive mt-2',
	                    'id' => 'loopterminology-submit',
	                    'value' => $this->msg( 'submit' )->parse()
	                )
	            ) . Html::closeElement(
	                'form'
	            ) . Html::rawElement(
	                'p',
                    array(),
                    $this->msg( 'loopterminology-example' )->plain()
	            )
	        );

        } else {

        	# user has no permission, just show content without textarea

        	$out->addHtml(
        		Html::rawElement(
        			'div',
        			array(
        				'class' => 'alert alert-dark',
        				'role' => 'alert',
        				'style' => 'white-space: pre;'
        			),
        			$terminologyWikitext
        		)
        	);

        }



        #$out->addHtml ( $html );
    }

    protected function getGroupName() {
		return 'loop';
	}
}
