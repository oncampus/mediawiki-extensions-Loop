<?php
/**
 * @description Adds index functions
 * @ingroup Extensions
 * @author Dennis Krohn @krohnden <dennis.krohn@th-luebeck.de>
 */
if ( !defined( 'MEDIAWIKI' ) ) die ( "This file cannot be run standalone.\n" );


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

		$html = '';
        if ( isset ( $args["id"] ) ) {
			$id = $args["id"];
            $htmlid = "id='" . $id . "' ";
		} else {
			return '';
		}

		$item = self::getIndexItem( $id );
		if ( is_object( $item ) ) {
			$articleId = $parser->getTitle()->getArticleID();
			# check if a dublicate id has been used
			if ( $input != $item->li_index || $articleId != $item->li_pageid ) {
				$otherTitle = Title::newFromId( $item->li_pageid );
				$e = new LoopException( wfMessage( 'loopindex-error-dublicate-id', $id, $otherTitle->mTextform, $item->li_index )->text() );
				$parser->addTrackingCategory( 'loop-tracking-category-error' );
				$html .= $e . "\n";
			}
		}
        $htmlid = "";
        $html .= "<span class='loop_index_anchor' $htmlid></span>";
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
		if ( $this->refId !== null ) {

			$dbw = wfGetDB( DB_PRIMARY );

			$dbw->insert(
				'loop_index',
				array(
					'li_index' => $this->index,
					'li_pageid' => $this->pageId,
					'li_refid' => $this->refId
				),
				__METHOD__
			);
			# SpecialPurgeCache::purge();

			return true;
		}
		return false;
	}

    /**
	 * Returns index item
	 * @return bool true
	 */
	public static function getIndexItem( $refId ) {
		$dbr = wfGetDB( DB_REPLICA );
		$res = $dbr->select(
			'loop_index',
			array(
				'li_refid',
                'li_index',
                'li_pageid'
			),
			array(
				'li_refid = "' . $refId .'"'
			),
			__METHOD__
		);

        foreach( $res as $row ) {
			return $row;
		}

        return false;

    }

	// deletes all index items of a page
    public static function removeAllPageItemsFromDb ( $article ) {
		$dbr = wfGetDB( DB_PRIMARY );
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
				$contentText = ContentHandler::getContentText( $content );
	            self::handleIndexItems( $wikiPage, $title, $contentText );
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
	 * Adds index items to db. Called by onLinksUpdateConstructed and onAfterStabilizeChange (custom Hook)
	 * @param WikiPage $wikiPage
	 * @param Title $title
	 * @param String $contentText
	 */
	public static function handleIndexItems( &$wikiPage, $title, $contentText = null ) {

		$content = $wikiPage->getContent();
		if ($contentText == null) {
			$contentText = ContentHandler::getContentText( $content );
		}

		if ( $title->getNamespace() == NS_MAIN || $title->getNamespace() == NS_GLOSSARY ) {

			$parser = new Parser();
			$loopIndex = new LoopIndex();
			$fwp = new FlaggableWikiPage ( $title );
			$stableRevId = $fwp->getStable();
			$latestRevId = $title->getLatestRevID();
			$stable = false;
			if ( $stableRevId == $latestRevId ) {
				$stable = true;
				# on edit, delete all objects of that page from db.
				self::removeAllPageItemsFromDb ( $title->getArticleID() );
			}

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
						$valid = true;
						$tmpLoopIndex = new LoopIndex();
						$tmpLoopIndex->pageId = $title->getArticleID();
						$tmpLoopIndex->index = $object[1];

						if ( isset( $object[2]["id"] ) ) {
							if ( $tmpLoopIndex->checkDublicates( $object[2]["id"] ) ) {
								$tmpLoopIndex->refId = $object[2]["id"];
							} else {
								# dublicate id!
								$valid = false;
							}
						}
						if ( $valid && $stable ) {
							$tmpLoopIndex->addToDatabase();
						}
					}
				}
				$lsi = LoopStructureItem::newFromIds ( $title->getArticleID() );

				if ( $lsi ) {
					LoopObject::updateStructurePageTouched( $title );
				} elseif ( $title->getNamespace() == NS_GLOSSARY ) {
					LoopGlossary::updateGlossaryPageTouched();
				}
				if ( $contentText !== $newContentText ) {
					return $newContentText;
				}
			}
		}
		return $contentText;
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

		$out->setPageTitle(wfMessage('loopindex'));
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
				foreach ($indexPages as $pageId => $page) {
					foreach ( $page as $refId ) {
						#dd($pages);
						$title = Title::newFromId( $pageId );
						$lsi = LoopStructureItem::newFromIds( $pageId );
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
		if ( isset( $indexlinks ) ) {
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
