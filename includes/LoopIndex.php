<?php
/**
 * @description Adds index functions
 * @ingroup Extensions
 * @author Dennis Krohn @krohnden <dennis.krohn@th-luebeck.de>
 */
if ( !defined( 'MEDIAWIKI' ) ) {
    die( "This file cannot be run standalone.\n" );
}

use MediaWiki\MediaWikiServices;

class LoopIndex {

	public $index;
    public $refId; 
	public $pageId;
    
    public static function onParserSetup( Parser $parser ) {
		$parser->setHook ( 'loop_index', 'LoopIndex::renderLoopIndex' ); 
		return true;
    }	
    
	static function renderLoopIndex( $input, array $args, Parser $parser, PPFrame $frame ) {
        
       # dd($args);
        $id = "";
        if ( isset ( $args["id"] ) ) {
            $id = "id='" . $args["id"] . "' ";
        }
        $html = "<span class='loop_index_anchor' $id></span>";
        return $html;
    }

    
	# returns whether to show index in TOC or not
	public static function getShowIndex() {

		$showIndex = false;
		
		$loopStructure = new LoopStructure();
		$loopStructure->loadStructureItems();

		$indexItems = self::getAllItems( $loopStructure );

		if ( $indexItems ) {
			$showIndex = true;
		}

		return $showIndex;
    }
    
    /**
	 * Add index item to the database
	 * @return bool true
	 */
	public function addToDatabase() {
		$dbw = wfGetDB( DB_MASTER );
		
        $dbw->insert(
            'loop_index',
            array(
                'li_index' => $this->index,
                'li_pageid' => $this->pageId,
                'li_refid' => $this->refId
            ),
            __METHOD__
		);
        SpecialPurgeCache::purge();
        
        return true;

    }
    
	// deletes all index items of a page
    public static function removeAllPageItemsFromDb ( $article ) {
		$dbr = wfGetDB( DB_MASTER );
		$dbr->delete(
			'loop_index',
			'li_pageid = ' . $article,
			__METHOD__
		);

        return true;
    }
    
	public function checkDublicates( $refId ) {
		
		$dbr = wfGetDB( DB_REPLICA );
		$res = $dbr->select(
			'loop_index',
			array(
                'li_refid'
			),
			array(
				'li_refid = "' . $refId .'"'
			),
			__METHOD__
		);
		
		foreach( $res as $row ) {
            # if res has rows, 
			# given refId is already in use. 
			return false;

		}
		# id is unique in index
		return true;
    }
    
    // returns all index items
    public static function getAllItems ( $loopStructure, $letter = false ) {
    
        $dbr = wfGetDB( DB_REPLICA );
        
        $res = $dbr->select(
            'loop_index',
            array(
                'li_index',
                'li_pageid',
                'li_refid'
            ),
            array(),
            __METHOD__
            );
        
        $objects = array();

        $loopStructureItems = $loopStructure->getStructureItems();
        $glossaryPages = LoopGlossary::getGlossaryPages( "idArray" );
        $pageSequence = array();
        foreach ( $loopStructureItems as $item ) {
            $pageSequence[$item->sequence] = $item->article;
        }
        $structureLength = sizeOf( $loopStructureItems );
        $i = 1;
        foreach ( $glossaryPages as $glossaryPage ) {
            $pageSequence[ $structureLength + $i ] = $glossaryPage;
            $i++;
        }
            
        foreach( $res as $row ) {
			if ( $letter ) {
				if ( in_array( $row->li_pageid, $pageSequence ) && !empty ( $row->li_index ) ) {
					$letter = ucFirst(substr($row->li_index, 0, 1));
					preg_match('/([A-Z]{1})/', $letter, $output_array);
					if ( ! isset($output_array[0] ) ) {
						$letter = "#";
					}
					$objects[ $letter ][$row->li_index][$row->li_pageid][] = $row->li_refid;
				}
			} else {
				$objects[$row->li_index][$row->li_pageid][] = $row->li_refid;
			}
        }
        if ( !empty( $objects ) ) {
            ksort( $objects, SORT_STRING );
		}
		#dd($objects);
        return $objects;
    }

    
	/**
	 * Custom hook called after stabilization changes of pages in FlaggableWikiPage->updateStableVersion()
	 * @param Title $title
	 * @param Content $content
	 */
	public static function onAfterStabilizeChange ( $title, $content, $userId ) {
	    
	    $latestRevId = $title->getLatestRevID();
	    $wikiPage = WikiPage::factory($title);
	    $fwp = new FlaggableWikiPage ( $title );
	    
	    if ( isset($fwp) ) {
	        $stableRevId = $fwp->getStable();
	        
	        if ( $latestRevId == $stableRevId || $stableRevId == null ) {
	            # In Loop Upgrade process, use user LOOP_SYSTEM for edits and review.
	            $user = null;
	            $systemUser = User::newSystemUser( 'LOOP_SYSTEM', [ 'steal' => true, 'create'=> false, 'validate' => true ] );
	            if ( $systemUser->getId() == $userId ) {
	                $user = $systemUser;
	            }
	            
	            self::handleIndexItems( $wikiPage, $title, $content, $user );
	        }
	    }
	    return true;
	}
	/**
	 * Custom hook called after stabilization changes of pages in FlaggableWikiPage->clearStableVersion()
	 * @param Title $title
	 */
	public static function onAfterClearStable( $title ) {
	    $wikiPage = WikiPage::factory($title);
	    self::handleIndexItems( $wikiPage, $title );
	    return true;
	}
	
	/**
	 * When deleting a page, remove all Reference entries from DB.
	 * Attached to ArticleDeleteComplete hook.
	 */
	public static function onArticleDeleteComplete( &$article, User &$user, $reason, $id, $content, LogEntry $logEntry, $archivedRevisionCount ) {
	    
	    LoopIndex::removeAllPageItemsFromDb ( $id );
	    
	    return true;
	}
	
	/**
	 * Checks revision status after saving content and starts db writing function in case of stable revision.
	 * Attached to LinksUpdateConstructed hook.
	 * @param LinksUpdate $linksUpdate
	 */
	public static function onLinksUpdateConstructed( $linksUpdate ) { 
		$title = $linksUpdate->getTitle();
		$wikiPage = WikiPage::factory( $title );
		$latestRevId = $title->getLatestRevID();
		if ( isset($title->flaggedRevsArticle) ) {
			$stableRevId = $title->flaggedRevsArticle;
			$stableRevId = $stableRevId->getStable();

			if ( $latestRevId == $stableRevId || $stableRevId == null ) {
				self::handleIndexItems( $wikiPage, $title );
			}
		}

		return true;
	}
	
	/**
	 * Adds index items to db. Called by onLinksUpdateConstructed and onAfterStabilizeChange (custom Hook)
	 * @param WikiPage $wikiPage
	 * @param Title $title
	 * @param Content $content
	 * @param User $user
	 */
	public static function handleIndexItems( &$wikiPage, $title, $content = null, $user = null ) {
		
		if ($content == null) {
			$content = $wikiPage->getContent();
		}
		if ( $title->getNamespace() == NS_MAIN || $title->getNamespace() == NS_GLOSSARY ) {
			$loopIndex = new LoopIndex();
			self::removeAllPageItemsFromDb ( $title->getArticleID() );
			$contentText = ContentHandler::getContentText( $content );
			$parser = new Parser();

			# check if loop_index is in page content
			$has_reference = false;
			if ( substr_count ( $contentText, 'loop_index' ) >= 1 ) {
				$has_reference = true;
			}
			if ( $has_reference ) {
				$references = array();
				$object_tags = array ();
				$forbiddenTags = array( 'nowiki', 'code', '!--', 'syntaxhighlight', 'source' ); # don't save ids when in here
				$extractTags = array_merge( array('loop_index'), $forbiddenTags );
				$parser->extractTagsAndParams( $extractTags, $contentText, $object_tags );
				$newContentText = $contentText;
				$loopStructure = new LoopStructure();
				$loopStructure->loadStructureItems();
				foreach ( $object_tags as $object ) {
					if ( ! in_array( strtolower($object[0]), $forbiddenTags ) ) { #exclude loop-tags that are in code or nowiki tags
						$tmpLoopIndex = new LoopIndex();
						$tmpLoopIndex->pageId = $title->getArticleID();
						$tmpLoopIndex->index = $object[1];
						
						if ( isset( $object[2]["id"] ) ) {
							if ( $tmpLoopIndex->checkDublicates( $object[2]["id"] ) ) {
								$tmpLoopIndex->refId = $object[2]["id"];
							} else {
								# dublicate id must be replaced
								$newRef = uniqid();
								$newContentText = preg_replace('/(id="'. $object[2]["id"].'")/', 'id="'. $newRef.'"'  , $newContentText, 1 );
								$tmpLoopIndex->refId = $newRef; 
							}
						} else {
							# create new id
							$newRef = uniqid();
							$newContentText = LoopObject::setReferenceId( $newContentText, $newRef, 'loop_index' ); 
							$tmpLoopIndex->refId = $newRef; 
						}
						$tmpLoopIndex->addToDatabase();
					}
				}
				$lsi = LoopStructureItem::newFromIds ( $title->getArticleID() );
				
				if ( $lsi ) {
					LoopObject::updateStructurePageTouched( $title );
				} elseif ( $title->getNamespace() == NS_GLOSSARY ) {
					LoopGlossary::updateGlossaryPageTouched();
				}
				if ( $contentText !== $newContentText ) {
					
					$fwp = new FlaggableWikiPage ( $title );
					$stableRev = $fwp->getStable();
					if ( $stableRev == 0 ) {
						$stableRev = $wikiPage->getRevision()->getId();
					} 

					$summary = wfMessage("loop-summary-id")->text();
					$content = $content->getContentHandler()->unserializeContent( $newContentText );
					$wikiPage->doEditContent ( $content, $summary, EDIT_UPDATE, $stableRev, $user );
				}	
			}
		}
	}

}

class SpecialLoopIndex extends SpecialPage {

	public function __construct() {
		parent::__construct( 'LoopIndex' );
	}

	public function execute( $sub ) {
		
		$out = $this->getOutput();
		$request = $this->getRequest();
		$user = $this->getUser();
        Loop::handleLoopRequest( $out, $request, $user ); #handle editmode
		
		$html = self::renderLoopIndexSpecialPage();
		
        $out->addHtml( $html );
	}
	
	public static function renderLoopIndexSpecialPage () {
		
		$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
		$linkRenderer->setForceArticlePath(true); #required for readable links
        $loopStructure = new LoopStructure();
        $loopStructure->loadStructureItems();
        $allItems = LoopIndex::getAllItems( $loopStructure, true );
		
		$html = "<h1>".wfMessage( 'loopindex' )->text()."</h1>";
        
		$html .= '<table class="table loop_index">';
		$links = array();
		foreach ( $allItems as $letter => $indexArray ) {
			foreach ( $indexArray as $index => $indexPages ) {
				foreach ($indexPages as $pages => $page) {
					foreach ( $page as $refId ) {

						$title = Title::newFromId( $page );
						$lsi = LoopStructureItem::newFromIds( $page );
						$prepend = ( $lsi && strlen( $lsi->tocNumber ) != 0 ) ? $lsi->tocNumber . " " : "";
						$links[$letter][$index][$prepend . $title->mTextform] = $linkRenderer->makelink( 
							$title, 
							new HtmlArmor( $prepend . $title->mTextform ), 
							array( 'title' =>  $prepend . $title->mTextform, "class" => "index-link", "data-target" => $refId ),
							array()
						);
					}
				}
				sort( $links[$letter][$index], SORT_STRING ); # sorts links of an index term
				$ucIndex = ucFirst($index);
				$indexlinks[$letter][$ucIndex] = '<tr scope="row" class="ml-1 pb-3">';
				$indexlinks[$letter][$ucIndex] .= '<td scope="col" class="pl-1 pr-1 font-weight-bold"></td>';
				$indexlinks[$letter][$ucIndex] .= '<td scope="col" class="pl-1 pr-1">'.$index.'</td>';
				$indexlinks[$letter][$ucIndex] .= '<td scope="col" class="pl-1 pr-1">';
				$i = 1;
				foreach ( $links[$letter][$index] as $link ) {
					$indexlinks[$letter][$ucIndex] .= ( $i == 1 ? " " : ", "  ) . $link;
					$i++;
				}
				$indexlinks[$letter][$ucIndex] .= '</td></tr>';
			}
		}
		ksort($indexlinks); # sorts terms
		foreach ($indexlinks as $letter => $indexArray ) {
			$i = 1;
			foreach ( $indexArray as $indexLink ) {
				if ( $i == 1 ) {
					$indexLink = substr_replace($indexLink, $letter, 85, 0);
				}
				$html .= $indexLink;
				$i++;
			}
		}
        
		$html .= '</table>';
		return $html;

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