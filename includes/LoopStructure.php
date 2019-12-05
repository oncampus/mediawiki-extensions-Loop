<?php

/**
 * Class representing a book structure and other meta information
 * @author Kevin Berg @bedoke <kevin-dominick.berg@th-luebeck.de>
 * @author Dennis Krohn @krohnden <dennis.krohn@th-luebeck.de>
 */
if ( !defined( 'MEDIAWIKI' ) ) {
    die( "This file cannot be run standalone.\n" );
}

use MediaWiki\MediaWikiServices;

class LoopStructure {

	private $id = 0; // id of the structure
	private $properties = array(); // array of structure properties
	private $propertiesLoaded = false; // bool properties loaded from database
	public $mainPage; // article id of the main page
	public $structureItems = array(); // array of structure items

	function __construct( $id = 0 ) {
		$this->id = $id;
	}

	public function getId() {
		return $this->id;
	}

	public function render() {

		global $wgLoopPageNumbering;

		$text = '';
		$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
		$linkRenderer->setForceArticlePath(true);

		foreach( $this->structureItems as $structureItem ) {

			if( intval( $structureItem->tocLevel ) === 0 ) {

				$text .= '<h2>'.$structureItem->tocText.'</h2>';

			} else {

				if( isset( $structureItem->tocLevel ) && $structureItem->tocLevel > 0 ) {
					$tabLevel = $structureItem->tocLevel;
				} else {
					$tabLevel = 1;
				}
				$title = Title::newFromID( $structureItem->article );
				$link = $structureItem->tocNumber . ' '. $structureItem->tocText;

				if( $wgLoopPageNumbering ) {
					$pageNumber = '<span class="loopstructure-number">' . $structureItem->tocNumber . '</span> ';
				} else {
					$pageNumber = '';
				}

				if ( $title ) {
					$link = $linkRenderer->makeLink(
						Title::newFromID( $structureItem->article ),
						new HtmlArmor( $pageNumber . $structureItem->tocText )
					);
					
				} 
				$text .= '<div class="loopstructure-listitem loopstructure-level-'.$structureItem->tocLevel.'">' . str_repeat(' ',  $tabLevel ) . $link . '</div>';
				
			}

		}

		return $text;

	}

	/**
	 * Converts the structureitems to the table of contents as wikitext.
	 */
	public function renderAsWikiText() {

		$wikiText = '';

		foreach( $this->structureItems as $structureItem ) {

			if( intval( $structureItem->tocLevel ) === 0 ) {
				$wikiText .= '[['.$structureItem->tocText.']]'.PHP_EOL.PHP_EOL;
			} else {
				$wikiText .= str_repeat( '=', $structureItem->tocLevel ).' '.$structureItem->tocText.' '.str_repeat( '=', $structureItem->tocLevel ).PHP_EOL;
			}

		}

		return $wikiText;

	}

	/**
	 * Converts wikitext to LoopStructureItems.
	 * @param $wikiText
	 */
	public function setStructureItemsFromWikiText( $wikiText, User $user ) {

		$regex = "/(<a )(.*?)(>)(.*?)(<\\/a>)/";
		preg_match($regex, $wikiText, $matches);
		if ( isset( $matches[4] ) ) {
			$rootTitleText = $matches[4];
		} else {
			$rootTitleText = "";
			#dd();
		}

		# Title objects has to start with a letter else an error will occur.
		if( ctype_alpha( $rootTitleText[0] )) {
            $rootTitle = Title::newFromText( $rootTitleText );
            if( is_object( $rootTitle )) {
                $this->mainPage = $rootTitle->getArticleID();
            } else {
                return false;
            }
        } else {
		    return false;
        }

		# create new root page
		if( $this->mainPage == 0 ) {
			$newPage = WikiPage::factory( Title::newFromText( $rootTitleText ));
			$newContent = new WikitextContent( wfMessage( 'loopstructure-default-newpage-content' )->inContentLanguage()->text() );
			$newPage->doEditContent( $newContent, '', EDIT_NEW, false, $user );
			$newTitle = $newPage->getTitle();
			$this->mainPage = $newTitle->getArticleId();
		}

		# Set new MW main page from LOOP main page
		$mainPage = Title::newFromId( $this->mainPage );
		$mainPageWP =  WikiPage::factory( $mainPage );
		$mwMainPageTitle = Title::newFromText( "Mainpage", NS_MEDIAWIKI );
		$mwMainPageWP = WikiPage::factory( $mwMainPageTitle );
		$content = $mainPageWP->getContent();
		$newMainPageContent = $content->getContentHandler()->unserializeContent( $mainPage->mTextform );
		$mwMainPageWP->doEditContent ( $newMainPageContent, "", 0, false, $user ); #TODO deprecated

		$parent_id = array();
		$parent_id[0] = $this->mainPage;
		$max_level = 0;
		$sequence = 0;

		unset( $this->structureItems );

		$loopStructureItem = new LoopStructureItem();
		$loopStructureItem->structure = $this->id;
		$loopStructureItem->article = $this->mainPage;
		$loopStructureItem->previousArticle = 0;
		$loopStructureItem->nextArticle = 0;
		$loopStructureItem->parentArticle = 0;
		$loopStructureItem->tocLevel = 0;
		$loopStructureItem->sequence = $sequence;
		$loopStructureItem->tocNumber = '';
		$loopStructureItem->tocText = $rootTitleText;

		$this->structureItems[$sequence] = $loopStructureItem;
		$sequence++;

		$regex = "/(<li class=\"toclevel-)(\\d)( tocsection-)(.*)(<span class=\"tocnumber\">)([\\d\\.]+)(<\\/span> <span class=\"toctext\">)(.*)(<\\/span)/";
		preg_match_all( $regex, $wikiText, $matches );

		for( $i=0; $i < count( $matches[0] ); $i++ ) {

			$tocLevel = $matches[2][$i];
			$tocNumber = $matches[6][$i];
			$tocText = $matches[8][$i];
			$tocArticleId = 0;

			$itemTitle = Title::newFromText($tocText);
			$tocArticleId = $itemTitle->getArticleID();

			# create new page for item
			if( $tocArticleId == 0 ) {
				$newPage = WikiPage::factory( Title::newFromText( $tocText ) );
				$newContent = new WikitextContent( wfMessage( 'loopstructure-default-newpage-content' )->inContentLanguage()->text());
				$newPage->doEditContent( $newContent, '', EDIT_NEW,	false, $user );
				$newTitle = $newPage->getTitle();
				$tocArticleId = $newTitle->getArticleId();
			}

			# get parent article
			$parent_id[$tocLevel] = $tocArticleId;

			if($tocLevel > $max_level) {
				$max_level = $tocLevel;
			}

			for( $j = $tocLevel + 1; $j <= $max_level; $j++ ) {
				$parent_id[$j] = 0;  # clear lower levels to prevent using an old value in case some intermediary levels are omitted
			}

			$parentArticleId = $parent_id[$tocLevel - 1];
			$parentArticleId = intval($parentArticleId);

			# set next item from the last structure item.
			$previousItem = $this->structureItems[$sequence-1];
			$previousArticleId = $previousItem->article;
			$previousItem->nextArticle = $tocArticleId;

			$loopStructureItem = new LoopStructureItem();
			$loopStructureItem->structure = $this->id;
			$loopStructureItem->article = $tocArticleId;
			$loopStructureItem->previousArticle = $previousArticleId;
			$loopStructureItem->nextArticle = 0; # next article will be set when building the next structure item.
			$loopStructureItem->parentArticle = $parentArticleId;
			$loopStructureItem->tocLevel = $tocLevel;
			$loopStructureItem->sequence = $sequence;
			$loopStructureItem->tocNumber = $tocNumber;
			$loopStructureItem->tocText = $tocText;

			$this->structureItems[$sequence] = $loopStructureItem;
			$sequence++;

		}

		return true;

	}

	public function getStructureItems() {
		if (!$this->structureItems) {
			$this->loadStructureItems();
		}
		return $this->structureItems;
	}

	/**
	 * Load items from database
	 */
	public function loadStructureItems() {

		$dbr = wfGetDB( DB_REPLICA );

		$res = $dbr->select(
			'loop_structure_items',
			array(
				'lsi_id',
				'lsi_article',
				'lsi_previous_article',
				'lsi_next_article',
				'lsi_parent_article',
				'lsi_toc_level',
				'lsi_sequence',
				'lsi_toc_number',
				'lsi_toc_text'
			),
			array(
				'lsi_structure' => $this->id
			),
			__METHOD__,
			array(
				'ORDER BY' => 'lsi_sequence ASC'
			)
		);

		foreach ( $res as $row ) {

			if ($row->lsi_toc_level == 0) {
				$this->mainPage = $row->lsi_article;
			}

			$loopStructureItem = new LoopStructureItem();
			$loopStructureItem->id = $row->lsi_id;
			$loopStructureItem->article = $row->lsi_article;
			$loopStructureItem->previousArticle = $row->lsi_previous_article;
			$loopStructureItem->nextArticle = $row->lsi_next_article;
			$loopStructureItem->parentArticle = $row->lsi_parent_article;
			$loopStructureItem->tocLevel = $row->lsi_toc_level;
			$loopStructureItem->sequence = $row->lsi_sequence;
			$loopStructureItem->tocNumber = $row->lsi_toc_number;
			$loopStructureItem->tocText = $row->lsi_toc_text;

			$this->structureItems[] = $loopStructureItem;

		}
	}

	/**
	 * Check for dublicate entries
	 */
	public function checkDublicates() {
		$articlesInStructure = [];
		foreach ( $this->structureItems as $structureItem ) {
			$articlesInStructure[] = $structureItem->article;
		}
		if ( count ( array_unique( $articlesInStructure ) ) < count ( $articlesInStructure ) ) {
			return false; # dublicate entry found
		} else {
			return true;
		}
	}

	/**
	 * Save items to database
	 */
	public function saveItems() {
		foreach( $this->structureItems as $structureItem ) {
			$structureItem->addToDatabase();
		}
		
		LoopObject::updateStructurePageTouched();
	}

	/**
	 * Delete all items from database
	 */
	public function deleteItems() {

		LoopObject::updateStructurePageTouched(); # update page_touched on structure pages. 

		$dbw = wfGetDB( DB_MASTER );
		$dbw->delete(
			'loop_structure_items',
			'*',
			__METHOD__
		);

		if( isset( $this->structureItems )) {
			unset( $this->structureItems );
		}

		return true;

	}


	public function lastChanged() {
		$dbr = wfGetDB( DB_REPLICA );
		$last_touched  =  $dbr->selectField(
			array(
					'loop_structure_items',
					'page'
			),
			'max( page_touched )',
			array(
					0 => "page_id = lsi_article",
					1 => "lsi_structure = '".$this->getId()."'"
			),
			__METHOD__
			);
		if( ! empty( $last_touched )) {
			return $last_touched;
		} else {
			return false;
		}


	}


	/**
	 * Get the mainpage
	 * @return Int
	 */
	function getMainpage() {
		return $this->mainPage;
	}

	/**
	 * Get the structure title
	 * @return string structure title
	 */
	function getTitle() {
		$lsTitle = '';
		if ($this->getMainpage()) {
			$lsTitle = Title::newFromID($this->getMainpage())->getText();
		}
		return $lsTitle;
	}

	/**
	* Check structure- and glossary pages for objects
 	*/
	public function hasObjects( $type ) {
		$objects = LoopObjectIndex::getObjectsOfType( $type );
		$structureItems = $this->getStructureItems();

		foreach ( $structureItems as $item ) {
			if ( isset ( $objects[$item->article] ) ) {
				return true;
			}
		}

		$glossaryItems = LoopGlossary::getGlossaryPages( "idArray" );
		foreach ( $glossaryItems as $item ) {
			if ( isset ( $objects[$item] ) ) {
				return true;
			}
		}

		return false;
	}
}

/**
 *  Class representing a single page of a LoopStructure
 */
class LoopStructureItem {

	public $id; // id of the structure item
	public $structure = 0; // id of the corresponding structure
	public $article; // article id of the page
	public $previousArticle; // article id from the previous page
	public $nextArticle; // article id from the next page
	public $parentArticle; // article id from the parent page
	public $tocLevel; // Level within the corresponding structure
	public $sequence; // Sequential number within the corresponding structure
	public $tocNumber; // string rrepresentation of the chapter number
	public $tocText; // page title

	/**
	 * Add structure item to the database
	 * @return bool true
	 */
	function addToDatabase() {

		if ($this->article!=0) {

			$dbw = wfGetDB( DB_MASTER );
			$this->id = $dbw->nextSequenceValue( 'LoopStructureItem_id_seq' );
			$tmpTocText = Title::newFromText( $this->tocText ); # Save TOC text as MW does it, possibly first letter uppercase 
			$dbw->insert(
				'loop_structure_items',
				array(
					'lsi_id' => $this->id,
					'lsi_article' => $this->article,
					'lsi_previous_article' => $this->previousArticle,
					'lsi_next_article' => $this->nextArticle,
					'lsi_parent_article' => $this->parentArticle,
					'lsi_toc_level' => $this->tocLevel,
					'lsi_sequence' => $this->sequence,
					'lsi_toc_number' => $this->tocNumber,
					'lsi_toc_text' => $tmpTocText->mTextform 
				),
				__METHOD__
			);
			$this->id = $dbw->insertId();
		}

		return true;

	}

	/**
	 * Get item for given article and structure from database
	 *
	 * @param int $articleId
	 */
	public static function newFromIds( $article ) {

		$dbr = wfGetDB( DB_REPLICA );
		$res = $dbr->select(
			'loop_structure_items',
			array(
				'lsi_id',
				'lsi_article',
				'lsi_previous_article',
				'lsi_next_article',
				'lsi_parent_article',
				'lsi_toc_level',
				'lsi_sequence',
				'lsi_toc_number',
				'lsi_toc_text'
			),
			array(
				'lsi_article' => $article
			),
			__METHOD__,
			array(
				'ORDER BY' => 'lsi_sequence ASC'
			)
		);

		if( $row = $res->fetchObject() ) {

			$loopStructureItem = new LoopStructureItem();
			$loopStructureItem->id = $row->lsi_id;
			$loopStructureItem->article = $row->lsi_article;
			$loopStructureItem->previousArticle = $row->lsi_previous_article;
			$loopStructureItem->nextArticle = $row->lsi_next_article;
			$loopStructureItem->parentArticle = $row->lsi_parent_article;
			$loopStructureItem->tocLevel = $row->lsi_toc_level;
			$loopStructureItem->sequence = $row->lsi_sequence;
			$loopStructureItem->tocNumber = $row->lsi_toc_number;
			$loopStructureItem->tocText = $row->lsi_toc_text;

			return $loopStructureItem;

		} else {

			return false;

		}

	}	
	
	/**
	* Get item for given title and structure from database
	*
	* @param string $title
	*/
	public static function newFromText( $title ) {

		$dbr = wfGetDB( DB_REPLICA );
		$res = $dbr->select(
			'loop_structure_items',
			array(
				'lsi_id',
				'lsi_article',
				'lsi_previous_article',
				'lsi_next_article',
				'lsi_parent_article',
				'lsi_toc_level',
				'lsi_sequence',
				'lsi_toc_number',
				'lsi_toc_text'
			),
			array(
				'lsi_toc_text' => $title
			),
			__METHOD__,
			array(
				'ORDER BY' => 'lsi_sequence ASC'
			)
		);

		if( $row = $res->fetchObject() ) {

			$loopStructureItem = new LoopStructureItem();
			$loopStructureItem->id = $row->lsi_id;
			$loopStructureItem->article = $row->lsi_article;
			$loopStructureItem->previousArticle = $row->lsi_previous_article;
			$loopStructureItem->nextArticle = $row->lsi_next_article;
			$loopStructureItem->parentArticle = $row->lsi_parent_article;
			$loopStructureItem->tocLevel = $row->lsi_toc_level;
			$loopStructureItem->sequence = $row->lsi_sequence;
			$loopStructureItem->tocNumber = $row->lsi_toc_number;
			$loopStructureItem->tocText = $row->lsi_toc_text;

			return $loopStructureItem;

		} else {

			return false;

		}

	}


	/**
	 * Get item for given article and structure from database
	 *
	 * @param int $articleId
	 * @param int $structure
	 */
	public static function newFromToctext( $toctext ) {

		$dbr = wfGetDB( DB_REPLICA );
		$res = $dbr->select(
				'loop_structure_items',
				array(
						'lsi_id',
						'lsi_article',
						'lsi_previous_article',
						'lsi_next_article',
						'lsi_parent_article',
						'lsi_toc_level',
						'lsi_sequence',
						'lsi_toc_number',
						'lsi_toc_text'
				),
				array(
						'lsi_toc_text' => $toctext
				),
				__METHOD__,
				array(
						'ORDER BY' => 'lsi_sequence ASC'
				)
		);

		if( $row = $res->fetchObject() ) {

			$loopStructureItem = new LoopStructureItem();
			$loopStructureItem->id = $row->lsi_id;
			$loopStructureItem->article = $row->lsi_article;
			$loopStructureItem->previousArticle = $row->lsi_previous_article;
			$loopStructureItem->nextArticle = $row->lsi_next_article;
			$loopStructureItem->parentArticle = $row->lsi_parent_article;
			$loopStructureItem->tocLevel = $row->lsi_toc_level;
			$loopStructureItem->sequence = $row->lsi_sequence;
			$loopStructureItem->tocNumber = $row->lsi_toc_number;
			$loopStructureItem->tocText = $row->lsi_toc_text;

			return $loopStructureItem;

		} else {

			return false;

		}

	}

	public function getPreviousChapterItem () {

		$dbr = wfGetDB( DB_REPLICA );
		$prev_id =  $dbr->selectField(
			'loop_structure_items',
			'lsi_article',
			array(
				0 => "lsi_sequence < '".$this->sequence."'",
				1 => "lsi_structure = '".$this->structure."'",
				2 => "lsi_toc_level = 1"
			),
			__METHOD__,
			array(
				'ORDER BY' => 'lsi_sequence DESC'
			)
		);

		if( ! empty( $prev_id )) {
			return LoopStructureItem::newFromIds( $prev_id );
		} else {
			return false;
		}

	}

	public function getNextChapterItem () {

		$dbr = wfGetDB( DB_REPLICA );
		$next_id = $dbr->selectField(
			'loop_structure_items',
			'lsi_article',
			array(
				0 => "lsi_sequence > '".$this->sequence."'",
				1 => "lsi_toc_level = 1"
			),
			__METHOD__,
			array(
				'ORDER BY' => 'lsi_sequence ASC'
			)
		);

		if( ! empty( $next_id ) ) {
			return LoopStructureItem::newFromIds( $next_id );
		} else {
			return false;
		}

	}

	public function getPreviousItem () {

		if( isset( $this->previousArticle ) ) {
			return LoopStructureItem::newFromIds( $this->previousArticle );
		} else {
			return false;
		}

	}

	public function getNextItem () {

		if( isset( $this->nextArticle ) ) {
			return LoopStructureItem::newFromIds( $this->nextArticle );
		} else {
			return false;
		}

	}

	public function getParentItem () {

		if( isset( $this->parentArticle ) ) {
			return LoopStructureItem::newFromIds( $this->parentArticle );
		} else {
			return false;
		}

	}

	public function getBreadcrumb ( $max_len = 100 ) {

		global $wgLoopPageNumbering;

	    $linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
	    $linkRenderer->setForceArticlePath(true);
		
		if( $wgLoopPageNumbering ) {
			$pageNumber = $this->tocNumber . ' ';
		} else {
			$pageNumber = '';
		}

		//preventing home page occouring on breadcrumb nav
		$breadcrumb = (empty($this->tocNumber)) ? '' : '<li class="active">' . $pageNumber . ' ' . $this->tocText .'</li>';

		$len = strlen( $this->tocNumber ) + strlen( $this->tocText ) + 1;
		$level = $this->tocLevel;

		$items = array();
		$item = $this->getParentItem();
		$toc_number_len = 0;

		while( $item !== false ) {
			$items[] = $item;
			$toc_number_len = $toc_number_len + strlen ( $item->tocNumber ) + 1;
			$item = $item->getParentItem();
		}

		$max_text_len = $max_len - $len - $toc_number_len;

		if ( $level == 0 ) {
			$level = 1;
		}

		$max_item_text_len = floor( $max_text_len / $level );

		foreach( $items as $item ) {
			
			// if home page -> skip
			if(empty($item->tocNumber)) continue;

			if( strlen( $item->tocText ) > $max_item_text_len) {
				$link_text = mb_substr( $item->tocText, 0, ( $max_item_text_len - 2 ) ) . '..';
			} else {
				$link_text = $item->tocText;
			}
			
			$title = Title::newFromID( $item->article );

			if( $wgLoopPageNumbering ) {
				$pageNumber = $item->tocNumber . ' ';
			} else {
				$pageNumber = '';
			}

			$link = $linkRenderer->makeLink( $title, new HtmlArmor( $pageNumber . $link_text ) );
			
			$breadcrumb = '<li>' . $link .'</li>' . $breadcrumb;

		}

		$breadcrumb = '<ol class="breadcrumb" id="breadcrumb">' . $breadcrumb . '</ol>';
		return $breadcrumb;

	}

	public function lastChanged() {

		$dbr = wfGetDB( DB_REPLICA );
		$last_touched  =  $dbr->selectField(
			array(
				'loop_structure_items',
				'page'
			),
			'max( page_touched )',
			array(
				0 => "page_id = lsi_article",
				1 => "lsi_article = '".$this->article."'"
			),
			__METHOD__
		);

		if( ! empty( $last_touched )) {
			return $last_touched;
		} else {
			return false;
		}

	}

	public function getArticle () {
		return $this->article;
	}

	public function getId () {
		return $this->id;
	}

	public function getTocLevel () {
		return $this->tocLevel;
	}

	public function getTocText () {
		return $this->tocText;
	}

	public function getTocNumber () {
		return $this->tocNumber;
	}


	public function getDirectChildItems () {

		$childs = array();

		$dbr = wfGetDB( DB_REPLICA );
		$res = $dbr->select(
				'loop_structure_items',
				array(
						'lsi_id',
						'lsi_structure',
						'lsi_article',
						'lsi_previous_article',
						'lsi_next_article',
						'lsi_parent_article',
						'lsi_toc_level',
						'lsi_sequence',
						'lsi_toc_number',
						'lsi_toc_text'
				),
				array(
						'lsi_parent_article' => $this->article,
						'lsi_structure' => $this->structure
				),
				__METHOD__,
				array(
						'ORDER BY' => 'lsi_sequence ASC'
				)
				);

		while ($row = $res->fetchObject()) {

			$loopstructureItem = new LoopStructureItem();
			$loopstructureItem->id = $row->lsi_id;
			$loopstructureItem->structure = $row->lsi_structure;
			$loopstructureItem->article = $row->lsi_article;
			$loopstructureItem->previousArticle = $row->lsi_previous_article;
			$loopstructureItem->nextArticle = $row->lsi_next_article;
			$loopstructureItem->parentArticle = $row->lsi_parent_article;
			$loopstructureItem->tocLevel = $row->lsi_toc_level;
			$loopstructureItem->sequence = $row->lsi_sequence;
			$loopstructureItem->tocNumber = $row->lsi_toc_number;
			$loopstructureItem->tocText = $row->lsi_toc_text;

			$childs[] = $loopstructureItem;
		}

		return $childs;
	}

}

class SpecialLoopStructure extends SpecialPage {

	public function __construct() {
		parent::__construct( 'LoopStructure' );
	}

	public function execute( $sub ) {

		$out = $this->getOutput();
		$request = $this->getRequest();
		$user = $this->getUser();
		Loop::handleLoopRequest( $out, $request, $user ); #handle editmode

		$this->setHeaders();
		$out->setPageTitle( $this->msg( 'loopstructure-specialpage-title' ) );
		$loopEditMode = $this->getSkin()->getUser()->getOption( 'LoopEditMode', false, true );
		$loopRenderMode = $this->getSkin()->getUser()->getOption( 'LoopRenderMode' );
		
		$html = self::renderLoopStructureSpecialPage( $loopEditMode, $loopRenderMode, $user );
		$out->addHtml( $html );

	}
	
	public static function renderLoopStructureSpecialPage( $loopEditMode = false, $loopRenderMode = 'default', $user = null ) {
	    $html = '';
	    $loopStructure = new LoopStructure();
	    $loopStructure->loadStructureItems();
	    
	    $html .= Html::openElement(
	        'h1',
	        array(
	            'id' => 'title' //'id' => 'loopstructure-h1'
	        )
	        )
	        . wfMessage( 'loopstructure-specialpage-title' )->parse();
	    
	    if ( $user ) {
    	    if( ! $user->isAnon() && $user->isAllowed( 'loop-toc-edit' ) && $loopRenderMode == 'default' && $loopEditMode ) {
    	        
    	        # show link to the edit page if user is permitted
    	        
    	        $html .= Html::rawElement(
    	                'a',
    	                array(
    	                    'href' => Title::newFromText( 'Special:LoopStructureEdit' )->getFullURL(),
    	                    'id' => 'editpagelink',
    	                    'class' => 'ml-2'
    	                ),
    	                '<i class="ic ic-edit"></i>'
    	                );
    	    }
	    }
	    
	    $html .= Html::closeElement(
	        'h1'
	        )
	        . Html::rawElement(
	            'div',
	            array(),
	            $loopStructure->render()
	            );
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


/**
 *  Special page representing the table of contents
 */

class SpecialLoopStructureEdit extends SpecialPage {

	public function __construct() {
		parent::__construct( 'LoopStructureEdit' );
	}

	public function execute( $sub ) {

		global $wgSecretKey;

		$out = $this->getOutput();
		$request = $this->getRequest();
		$user = $this->getUser();
		Loop::handleLoopRequest( $out, $request, $user ); #handle editmode

		$this->setHeaders();
		$out->setPageTitle( $this->msg( 'loopstructure-edit-specialpage-title' ) );

		$tabindex = 0;

        # headline output
        $out->addHtml(
            Html::rawElement(
                'h1',
                array(
                    'id' => 'loopstructure-h1'
                ),
                $this->msg( 'loopstructure-edit-specialpage-title' )->parse()
            )
        );

		$loopStructure = new LoopStructure();
		$loopStructure->loadStructureItems();
		$currentStructureAsWikiText = $loopStructure->renderAsWikiText();

        $request = $this->getRequest();
        $saltedToken = $user->getEditToken( $wgSecretKey, $request );
		$newStructureContent = $request->getText( 'loopstructure-content' );
		$requestToken = $request->getText( 't' );

		$userIsPermitted = (! $user->isAnon() && $user->isAllowed( 'loop-toc-edit' ));
		$success = null;
		$error = false;
		$feedbackMessageClass = 'success';

		if( ! empty( $newStructureContent ) && ! empty( $requestToken )) {
			if( $userIsPermitted ) {
				if( $user->matchEditToken( $requestToken, $wgSecretKey, $request )) {

					# the content was changend
					# force toc rendering for tocs with three or fewer headings.
					$newStructureContent = '__FORCETOC__' . PHP_EOL . $newStructureContent;

					# use local parser to get a default parsed result
					$localParser = new Parser();
					$tmpTitle = Title::newFromText( 'NO TITLE' );
                    $parserOutput = $localParser->parse( $newStructureContent, $tmpTitle, new ParserOptions() );

					if( is_object( $parserOutput )) {

						$parsedStructure = $parserOutput->mText;

						if( ! empty( $parsedStructure )) {

							$tmpLoopStructure = new LoopStructure();
							$parseResult = $tmpLoopStructure->setStructureItemsFromWikiText( $parsedStructure, $user );
							$noDublicatesInStructure = $tmpLoopStructure->checkDublicates();

							if ( ! $noDublicatesInStructure ) {
								$error = $this->msg( 'loopstructure-save-dublicates-error' )->parse();
								$feedbackMessageClass = 'danger';
							} else {

								if( $parseResult !== false ) {

									$newStructureContentParsedWikiText = $tmpLoopStructure->renderAsWikiText();
	
									# if new parsed structure is different to the new one save it
									if( $currentStructureAsWikiText != $newStructureContentParsedWikiText ) {
	
										$loopStructure->deleteItems();
										$loopStructure->setStructureItemsFromWikiText( $parsedStructure, $user );
										//dd($loopStructure);
										$loopStructure->saveItems();
										$currentStructureAsWikiText = $loopStructure->renderAsWikiText();
	
										# save success output
										$out->addHtml(
											Html::rawElement(
												'div',
												array(
													'name' => 'loopstructure-content',
													'class' => 'alert alert-'.$feedbackMessageClass
												),
												$this->msg( 'loopstructure-save-success' )->parse()
											)
										);
										$success = true;
									} else {
										$error = $this->msg( 'loopstructure-save-equal-error' )->parse();
										$feedbackMessageClass = 'warning';
									}
	
								} else {
									$error = $this->msg( 'loopstructure-save-parse-error' )->parse();
									$feedbackMessageClass = 'danger';
								}
							}

						} else {
							$error = $this->msg( 'loopstructure-save-parsed-structure-error' )->parse();
                            $feedbackMessageClass = 'danger';
						}

					} else {
						$error = $this->msg( 'loopstructure-save-parse-error' )->parse();
                        $feedbackMessageClass = 'danger';
					}

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
			if ( !empty ($newStructureContent) && ! $success ) {
				$displayedStructure = substr( $newStructureContent, 13 ); # remove __FORCE_TOC__
			} else {
				$displayedStructure = $currentStructureAsWikiText;
			}
	        $out->addHTML(
	            Html::openElement(
	                'form',
	                array(
	                    'class' => 'mw-editform mt-3 mb-3',
	                    'id' => 'loopstructure-form',
	                    'method' => 'post',
	                    'enctype' => 'multipart/form-data'
	                )
	            )
	            . Html::rawElement(
	                'textarea',
	                array(
	                    'name' => 'loopstructure-content',
	                    'id' => 'loopstructure-textarea',
	                    'tabindex' => ++$tabindex,
	                    'class' => 'd-block mt-3',
	                ),
	                $displayedStructure
	            )
	            . Html::rawElement(
	                'input',
	                array(
	                    'type' => 'hidden',
	                    'name' => 't',
	                    'id' => 'loopstructure-token',
	                    'value' => $saltedToken
	                )
	            )
	            . Html::rawElement(
	                'input',
	                array(
	                    'type' => 'submit',
	                    'tabindex' => ++$tabindex,
	                    'class' => 'mw-htmlform-submit mw-ui-button mw-ui-primary mw-ui-progressive mt-2',
	                    'id' => 'loopstructure-submit',
	                    'value' => $this->msg( 'submit' )->parse()
	                )
	            ) . Html::closeElement(
	                'form'
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
        			$currentStructureAsWikiText
        		)
        	);

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
