<?php
/**
 * @author Dennis Krohn @krohnden
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file cannot be run standalone.\n" );
}

use MediaWiki\MediaWikiServices;

class LoopLiterature {

	public $literatureTypes;

    public $id;
    public $key;
    public $itemType;
    public $address;
    public $author;
    public $booktitle;
    public $chapter;
    public $edition;
    public $editor;
    public $howpublished;
    public $institution;
    public $isbn;
    public $journal;
    public $month;
    public $note;
    public $number;
    public $organization;
    public $pages;
    public $publisher;
    public $school;
    public $series;
    public $itemtitle;
    public $type;
    public $url;
    public $volume;
    public $year;
    public $errors;

	public function __construct() {

		$this->literatureTypes = array(
			"article" => array(
				"required" => array( "author", "itemtitle", "journal", "year" ),
				"optional" => array( "volume", "number", "pages", "month", "note", "url" )
			),
			"book" => array(
				"required" => array( "author", "editor", "itemtitle", "publisher", "year" ),
				"optional" => array( "volume", "number", "series", "address", "edition", "month", "note", "isbn", "url" )
			),
			"booklet" => array(
				"required" => array( "itemtitle" ),
				"optional" => array( "author", "howpublished", "address", "month", "year", "note", "url" )
			),
			"conference" => array(
				"required" => array( "author", "itemtitle", "booktitle", "year" ),
				"optional" => array( "editor", "volume", "number", "series", "pages", "address", "month", "organization", "publisher", "note", "url" )
			),
			"inbook" => array(
				"required" => array( "author", "editor", "itemtitle", "chapter", "pages", "publisher", "year" ),
				"optional" => array( "volume", "number", "series", "type", "address", "edition", "month", "note", "url" )
			),
			"incollection" => array(
				"required" => array( "author", "itemtitle", "booktitle", "publisher", "year" ),
				"optional" => array( "editor", "volume", "number", "series", "type", "chapter", "pages", "address", "edition", "month", "note", "url" )
			),
			"inproceedings" => array(
				"required" => array( "author", "itemtitle", "booktitle", "year" ),
				"optional" => array( "editor", "volume", "number", "series", "pages", "address", "month", "organization", "publisher", "note", "url" )
			),
			"manual" => array(
				"required" => array( "address", "itemtitle", "year" ),
				"optional" => array( "author", "organization", "edition", "month", "note", "url" )
			),
			"mastersthesis" => array(
				"required" => array( "author", "itemtitle", "school", "year" ),
				"optional" => array( "type", "address", "month", "note", "url" )
			),
			"misc" => array(
				"required" => array(),
				"optional" => array( "author", "itemtitle", "howpublished", "month", "year", "note", "url" )
			),
			"phdthesis" => array(
				"required" => array( "author", "itemtitle", "school", "year" ),
				"optional" => array( "type", "address", "month", "note", "url" )
			),
			"proceedings" => array(
				"required" => array( "itemtitle", "year" ),
				"optional" => array( "editor", "volume", "number", "series", "address", "month", "organization", "publisher", "note", "url" )
			),
			"techreport" => array(
				"required" => array( "author", "itemtitle", "institution", "year" ),
				"optional" => array( "type", "note", "number", "address", "month", "url" )
			),
			"unpublished" => array(
				"required" => array( "author", "itemtitle", "note" ),
				"optional" => array( "month", "year", "url" )
			)
		);
		$this->errors = array();
		return true;
	}

    public static function onParserSetup( Parser $parser ) {
		$parser->setHook ( 'cite', 'LoopLiterature::renderCite' ); 
		$parser->setHook ( 'loop_literature', 'LoopLiterature::renderLoopLiterature' );
		return true;
	}	
	
    /**
     * Add literature entry to the database
     * @return bool true
     */
    function addToDatabase() {
		#dd("addtodatabase");

		$dbw = wfGetDB( DB_MASTER );
		
		$dbw->insert(
			'loop_literature_items',
				array(
				'lit_itemkey' => $this->key,
				'lit_itemtype' => $this->itemType,
				'lit_address' => $this->address,
				'lit_author' => $this->author,
				'lit_booktitle' => $this->booktitle,
				'lit_chapter' => $this->chapter,
				'lit_edition' => $this->edition,
				'lit_editor' => $this->editor,
				'lit_howpublished' => $this->howpublished,
				'lit_institution' => $this->institution,
				'lit_isbn' => $this->isbn,
				'lit_journal' => $this->journal,
				'lit_month' => $this->month,
				'lit_note' => $this->note,
				'lit_number' => $this->number,
				'lit_organization' => $this->organization,
				'lit_pages' => $this->pages,
				'lit_publisher' => $this->publisher,
				'lit_school' => $this->school,
				'lit_series' => $this->series,
				'lit_title' => $this->itemtitle,
				'lit_type' => $this->type,
				'lit_url' => $this->url,
				'lit_volume' => $this->volume,
				'lit_year' => $this->year
			)
		);
		

        return true;
	}

    /**
     * Puts request content into object
     *
     * @param Request $request 
     */

    public function getLiteratureFromRequest ( $request ) {

		$key = $request->getText( 'key' );
		$itemType = $request->getText( 'itemType' );
		
		if ( ! empty ( $key ) ) {
			
		 	if ( ! empty ( $itemType ) ) {
				$loopLiterature = new LoopLiterature();
				$loopLiteratureItem = $loopLiterature->loadLiteratureItem( $key );
				if ( ! $loopLiteratureItem ) {
					$this->key = $key;

					#dd($itemType, array_key_exists( $itemType, $this->literatureTypes ), $request);
					if ( array_key_exists( strtolower( $itemType ), $this->literatureTypes ) ) {

						$this->itemType = strtolower( $itemType );

						foreach ( $this->literatureTypes[$this->itemType] as $required => $array ) {

							foreach ( $array as $field ) {
								if ( ! empty ( $request->getText( $field ) ) ) {
									$value = $request->getText( $field );
									$valid = self::checkDataValidity( $field, $value );
									if ( $valid ) {
										switch ( $field ) {
											case "address":
												$this->address = $value;
												break;
											case "author":
												$this->author = $value;
												break;
											case "booktitle":
												$this->booktitle = $value;
												break;
											case "chapter":
												$this->chapter = intval($value);
												break;
											case "edition":
												$this->edition = intval($value);
												break;
											case "editor":
												$this->editor = $value;
												break;
											case "howpublished":
												$this->howpublished = $value;
												break;
											case "institution":
												$this->institution = $value;
												break;
											case "isbn":
												$this->isbn = intval($value);
												break;
											case "journal":
												$this->address = $value;
												break;
											case "month":
												$this->month = $value;
												break;
											case "note":
												$this->note = $value;
												break;
											case "number":
												$this->number = intval($value);
												break;
											case "organization":
												$this->organization = $value;
												break;
											case "pages":
												$this->pages = $value;
												break;
											case "publisher":
												$this->publisher = $value;
												break;
											case "school":
												$this->school = $value;
												break;
											case "series":
												$this->series = $value;
												break;
											case "itemtitle":
												$this->itemtitle = $value;
												break;
											case "type":
												$this->type = $value;
												break;
											case "url":
												$this->url = $value;
												break;
											case "volume":
												$this->volume = $value;
												break;
											case "year":
												$this->year = intval($value);
												break;
										}
									} else {
										$this->errors[] = wfMessage( "loopliterature-error-invalidentry", wfMessage("loopliterature-label-".$field) );
									}
								} elseif ( $required == "required" )  {
									$this->errors[] = wfMessage( "loopliterature-error-missingrequired", wfMessage("loopliterature-label-".$field) );
								}
							}
						}
					} else {
						$this->errors[] = wfMessage( "loopliterature-error-unknowntype", $itemType );
					}
				} else {
					$this->errors[] = wfMessage( "loopliterature-error-dublicatekey", $key );
				} 
			} else {
				$this->errors[] = wfMessage( "loopliterature-error-missingrequired",  wfMessage("loopliterature-label-itemType") );
			}
		} else {
			$this->errors[] = wfMessage( "loopliterature-error-missingkey" );
		}
		#dd("hi",$this);
		return $this;
	}
	
	public static function checkDataValidity( $key, $val ) {
		
		switch ( $key ) {
			case "year":
				$int_val = intval($val);
				if ( strlen( $val ) == 4 && is_numeric( $val ) ) {
					return true;
				} else { return false; }
				
			case "chapter":
				$int_val = intval($val);
				if ( is_numeric( $val ) ) {
					return true;
				} else { return false; }
				
			case "edition":
				$int_val = intval($val);
				if ( is_numeric( $val ) ) {
					return true;
				} else { return false; }
				
			case "number":
				$int_val = intval($val);
				if ( is_numeric( $val ) ) {
					return true;
				} else { return false; }
				
			case "isbn":
				$int_val = intval($val);
				if ( is_numeric( $val ) && ( strlen( $val ) == 10 || strlen( $val ) == 13 ) ) {
					return true;
				} else { return false; }
				
			case "note":
				if ( ! strpos($val, '<script>') ) {
					return true;
				} else {
					return false;
				}
				
			default:
				if ( strlen( $val ) <= 255 && ! strpos($val, '<script>') ) {
					return true;
				} else { return false; }
				
		#check for text
		}
	}


    /**
     * Loads all literature items from DB
	 * @param Array $dada = null
	 * 			"keys" -> will only return keys
     */
    public static function getAllLiteratureItems( $data = null ) {

        $dbr = wfGetDB( DB_REPLICA );
        
        $res = $dbr->select(
            'loop_literature_items',
            array(
				'lit_itemkey',
				'lit_itemtype',
				'lit_address',
				'lit_author',
				'lit_booktitle',
				'lit_chapter',
				'lit_edition',
				'lit_editor',
				'lit_howpublished',
				'lit_institution',
				'lit_isbn',
				'lit_journal',
				'lit_month',
				'lit_note',
				'lit_number',
				'lit_organization',
				'lit_pages',
				'lit_publisher',
				'lit_school',
				'lit_series',
				'lit_title',
				'lit_type',
				'lit_url',
				'lit_volume',
				'lit_year'
            ),
            array(),
            __METHOD__
        );

		$return = array();

        foreach ( $res as $row ) {
			
			if ( isset ( $data["key"] ) ) {
				$return[] = $row->lit_itemkey;
			} else {
				$return[$row->lit_itemkey] = array();
				$return[$row->lit_itemkey]["itemtype"] = $row->lit_itemtype;
				$return[$row->lit_itemkey]["address"] = $row->lit_address;
				$return[$row->lit_itemkey]["author"] = $row->lit_author;
				$return[$row->lit_itemkey]["booktitle"] = $row->lit_booktitle;
				$return[$row->lit_itemkey]["chapter"] = $row->lit_chapter;
				$return[$row->lit_itemkey]["edition"] = $row->lit_edition;
				$return[$row->lit_itemkey]["editor"] = $row->lit_editor;
				$return[$row->lit_itemkey]["howpublished"] = $row->lit_howpublished;
				$return[$row->lit_itemkey]["institution"] = $row->lit_institution;
				$return[$row->lit_itemkey]["isbn"] = $row->lit_isbn;
				$return[$row->lit_itemkey]["journal"] = $row->lit_journal;
				$return[$row->lit_itemkey]["month"] = $row->lit_month;
				$return[$row->lit_itemkey]["note"] = $row->lit_note;
				$return[$row->lit_itemkey]["number"] = $row->lit_number;
				$return[$row->lit_itemkey]["organization"] = $row->lit_organization;
				$return[$row->lit_itemkey]["pages"] = $row->lit_pages;
				$return[$row->lit_itemkey]["publisher"] = $row->lit_publisher;
				$return[$row->lit_itemkey]["school"] = $row->lit_school;
				$return[$row->lit_itemkey]["series"] = $row->lit_series;
				$return[$row->lit_itemkey]["itemtitle"] = $row->lit_title;
				$return[$row->lit_itemkey]["type"] = $row->lit_type;
				$return[$row->lit_itemkey]["url"] = $row->lit_url;
				$return[$row->lit_itemkey]["volume"] = $row->lit_volume;
				$return[$row->lit_itemkey]["year"] = $row->lit_year;
					
			}
		}
		return $return;
	}

    /**
     * Loads literature item from DB
     */
    public function loadLiteratureItem( $key ) {

        $dbr = wfGetDB( DB_REPLICA );
        
        $res = $dbr->select(
            'loop_literature_items',
            array(
				'lit_itemkey',
				'lit_itemtype',
				'lit_address',
				'lit_author',
				'lit_booktitle',
				'lit_chapter',
				'lit_edition',
				'lit_editor',
				'lit_howpublished',
				'lit_institution',
				'lit_isbn',
				'lit_journal',
				'lit_month',
				'lit_note',
				'lit_number',
				'lit_organization',
				'lit_pages',
				'lit_publisher',
				'lit_school',
				'lit_series',
				'lit_title',
				'lit_type',
				'lit_url',
				'lit_volume',
				'lit_year'
            ),
            array(
                 'lit_itemkey = "' . $key .'"' 
            ),
            __METHOD__
        );

		$itemExists = false;
        foreach ( $res as $row ) {
			$itemExists = true;
			
			$this->key = $row->lit_itemkey;
			$this->itemType = $row->lit_itemtype;
			$this->address = $row->lit_address;
			$this->author = $row->lit_author;
			$this->booktitle = $row->lit_booktitle;
			$this->chapter = $row->lit_chapter;
			$this->edition = $row->lit_edition;
			$this->editor = $row->lit_editor;
			$this->howpublished = $row->lit_howpublished;
			$this->institution = $row->lit_institution;
			$this->isbn = $row->lit_isbn;
			$this->journal = $row->lit_journal;
			$this->month = $row->lit_month;
			$this->note = $row->lit_note;
			$this->number = $row->lit_number;
			$this->organization = $row->lit_organization;
			$this->pages = $row->lit_pages;
			$this->publisher = $row->lit_publisher;
			$this->school = $row->lit_school;
			$this->series = $row->lit_series;
			$this->itemtitle = $row->lit_title;
			$this->type = $row->lit_type;
			$this->url = $row->lit_url;
			$this->volume = $row->lit_volume;
			$this->year = $row->lit_year;

		}
		if ( ! $itemExists ) {
			return false;
		}
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

		$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();

		$loopLiterature = new LoopLiterature();
		$loopLiteratureItem = $loopLiterature->loadLiteratureItem( $input );
		
		
		if ( ! $loopLiteratureItem ) {
			
			$e = new LoopException( wfMessage( 'loopliterature-error-keyunknown' )->text() );
			$parser->addTrackingCategory( 'loop-tracking-category-error' );
			
			return $e;
		} else {
			$text = '';
			#dd();
			if ( isset( $loopLiterature->author ) ) {
				$text.= $loopLiterature->author;
			} elseif ( isset( $loopLiterature->itemTitle )) {
				$text.= $loopLiterature->itemTitle;
			}
			if ( isset( $loopLiterature->year ) ) {
				$text .= " " . $loopLiterature->year;
			}
			$html = $linkRenderer->makeLink( 
				new TitleValue( NS_SPECIAL, 'LoopLiterature' ), 
				new HtmlArmor( $text ),
				array( "data-target" => $input ) # target id will be added in hook
			);

		}

		return $html;
	}

	static function renderLoopLiterature( $input, array $args, Parser $parser, PPFrame $frame ) {
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
				self::doIndexLoopLiteratureReferences( $wikiPage, $title );
			}
		}

		return true;
	}
	
	/**
	 * Adds literature references to db. Called by onLinksUpdateConstructed and onAfterStabilizeChange (custom Hook)
	 * @param WikiPage $wikiPage
	 * @param Title $title
	 * @param Content $content
	 */
	public static function doIndexLoopLiteratureReferences( &$wikiPage, $title, $content = null, $user = null ) {
		
		if ($content == null) {
			$content = $wikiPage->getContent();
		}
		
		#dd("HALLO1", $title,$content);
		if ( $title->getNamespace() == NS_MAIN || $title->getNamespace() == NS_GLOSSARY ) {
			$loopLiteratureReference = new LoopLiteratureReference();
			LoopLiteratureReference::removeAllPageItemsFromDb ( $title->getArticleID() );
			$contentText = ContentHandler::getContentText( $content );
			$parser = new Parser();

			# check if loop_object in page content
			$has_reference = false;
			if ( substr_count ( $contentText, 'cite' ) >= 1 ) {
				$has_reference = true;
			}
			if ( $has_reference ) {
				$references = array();
				#foreach (self::$mObjectTypes as $objectType) {
				#	$objects[$objectType] = 0;
				#}
				$object_tags = array ();
				$forbiddenTags = array( 'nowiki', 'code', '!--', 'syntaxhighlight', 'source' ); # don't save ids when in here
				$extractTags = array_merge( array('cite'), $forbiddenTags );
				$parser->extractTagsAndParams( $extractTags, $contentText, $object_tags );
				$newContentText = $contentText;
				
				$items = 0;
				foreach ( $object_tags as $object ) {
					if ( ! in_array( strtolower($object[0]), $forbiddenTags ) ) { #exclude loop-tags that are in code or nowiki tags
						$tmpLoopLiteratureReference = new LoopLiteratureReference();
						$items++;
						$tmpLoopLiteratureReference->nthItem = $items;
						$tmpLoopLiteratureReference->pageId = $title->getArticleID();
						$tmpLoopLiteratureReference->key = $object[1];
						
						if ( isset( $object[2]["id"] ) ) {
							if ( $tmpLoopLiteratureReference->checkDublicates( $object[2]["id"] ) ) {
								$tmpLoopLiteratureReference->refId = $object[2]["id"];
							} else {
								# dublicate id must be replaced
								$newRef = uniqid();
								$newContentText = preg_replace('/(id="'.$object[2]["id"].'")/', 'id="'.$newRef.'"'  , $newContentText, 1 );
								$tmpLoopLiteratureReference->refId = $newRef; 
							}
						} else {
							# create new id
							$newRef = uniqid();
							$newContentText = LoopObject::setReferenceId( $newContentText, $newRef, 'cite' ); 
							$tmpLoopLiteratureReference->refId = $newRef; 
						}
						$tmpLoopLiteratureReference->addToDatabase();
						#dd($object, $tmpLoopLiteratureReference);
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

					$summary = '';
					$content = $content->getContentHandler()->unserializeContent( $newContentText );
					$wikiPage->doEditContent ( $content, $summary, EDIT_UPDATE, $stableRev, $user );
				}	
			}
		}
	}

	
	/**
	 * Outputs the given object's numbering
	 * @param String $objectId
	 * @param Array $pageData = [
	 * 					0 => "structure" or "glossary"
	 * 					1 => LoopStructureItem or $articleId
	 * 					2 => LoopStructure or empty
	 * 					]
	 * @param Array $previousObjects
	 * @param Array $objectData
	 */
	public static function getLiteratureNumberingOutput($objectid, Array $pageData, $previousObjects = null, $objectData = null ) {

		#dd($objectid);
		$typeOfPage = $pageData[0];

		if ( $previousObjects == null ) {
			if ( $typeOfPage == "structure" ) {
				$previousObjects = LoopLiteratureReference::getLiteratureNumberingsForPage ( $pageData[1], $pageData[2] ); // $lsi, $loopStructure
			} else {
				$previousObjects = LoopLiteratureReference::getLiteratureNumberingsForGlossaryPage ( $pageData[1] );
			}
		}
		#dd($previousObjects);
		if ( $objectData == null ) {
			$objectData = LoopLiteratureReference::getItemData( $objectid );
		}
		#dd($objectData);
		if ( $objectData["refId"] == $objectid ) {

			$tmpPreviousObjects = 0;
			if ( isset($previousObjects) ) {
				$tmpPreviousObjects = $previousObjects['cite'];
			}
			$prefix = '';
			if ( $typeOfPage == "glossary" ) {
				$prefix = wfMessage("loop-glossary-objectnumber-prefix")->text();
			}
			return $prefix . ( $tmpPreviousObjects + $objectData["nthItem"] );
					
			
		}
	}

}

class LoopLiteratureReference {

	public $key;
	public $pageId;
	public $refId; 
	public $nthItem;

	/**
	 * Add literature reference item to the database
	 * @return bool true
	 */
	public function addToDatabase() {
		$dbw = wfGetDB( DB_MASTER );
		
        $dbw->insert(
            'loop_literature_references',
            array(
                'llr_itemkey' => $this->key,
                'llr_pageid' => $this->pageId,
                'llr_refid' => $this->refId,
                'llr_nthitem' => $this->nthItem
            ),
            __METHOD__
		);
        $this->id = $dbw->insertId();
		
        return true;

	}
	
	// deletes all literature references of a page
    public static function removeAllPageItemsFromDb ( $article ) {
		$dbr = wfGetDB( DB_MASTER );
		$dbr->delete(
			'loop_literature_references',
			'llr_pageid = ' . $article,
			__METHOD__
		);

        return true;
	}

	public static function getItemData( $refId ) { 
        
        $dbr = wfGetDB( DB_REPLICA );
		$res = $dbr->select(
			'loop_literature_references',
			array(
                'llr_itemkey',
                'llr_pageid',
                'llr_refid',
                'llr_nthitem'
			),
			array(
				'llr_refid = "' . $refId .'"'
			),
			__METHOD__
		);
		
		foreach( $res as $row ) {

            $return = array(
                'refId' => $row->llr_refid,
                'articleId' => $row->llr_pageid,
                'key' => $row->llr_itemkey,
                'nthItem' => $row->llr_nthitem,
            );

			return $return;

		}
		# id unknown
		return false;

	}
	
	public function checkDublicates( $refId ) {
		
		$dbr = wfGetDB( DB_REPLICA );
		$res = $dbr->select(
			'loop_literature_references',
			array(
                'llr_refid'
			),
			array(
				'llr_refid = "' . $refId .'"'
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
	
	
    // returns structure literature items with numberings in the table
    public static function getAllItems ( $loopStructure ) {
        
        global $wgLoopLiteratureCiteType;
        
        $dbr = wfGetDB( DB_REPLICA );
        
        $res = $dbr->select(
            'loop_literature_references',
            array(
                'llr_itemkey',
                'llr_pageid',
                'llr_refid',
                'llr_nthitem'
            ),
            array(
            ),
            __METHOD__
            );
        
        $objects = array();
        
        $loopStructureItems = $loopStructure->getStructureItems();
        
        foreach ( $loopStructureItems as $loopStructureItem ) {
            $previousObjects[ $loopStructureItem->article ] = self::getLiteratureNumberingsForPage( $loopStructureItem, $loopStructure );
        }
        
		$glossaryItems = LoopGlossary::getGlossaryPages("idArray");
        foreach ( $glossaryItems as $glossaryItem ) {
            $previousObjects[ $glossaryItem ] = self::getLiteratureNumberingsForGlossaryPage( $glossaryItem );
        }
        #dd($previousObjects);
        foreach( $res as $row ) {
            
            $numberText = '';
            
            if ( $wgLoopLiteratureCiteType == true ) {
                
                $objectData = array(
					'refId' => $row->llr_refid,
					'articleId' => $row->llr_pageid,
					'key' => $row->llr_itemkey,
					'nthItem' => $row->llr_nthitem,
                );
                
                $lsi = LoopStructureItem::newFromIds($row->llr_pageid);
                if ( $lsi ) {
                    $pageData = array( "structure", $lsi, $loopStructure );
                    $numberText = LoopLiterature::getLiteratureNumberingOutput( $row->llr_refid, $pageData, $previousObjects[ $row->llr_pageid ], $objectData );
                } elseif ( isset ( $previousObjects[ $row->llr_pageid ] ) ) {
					$pageData = array( "glossary", $row->llr_pageid );
					$numberText = LoopLiterature::getLiteratureNumberingOutput( $row->llr_refid, $pageData, $previousObjects[ $row->llr_pageid ], $objectData );
                }
            }
        
            $objects[$row->llr_refid] = array(
                'refId' => $row->llr_refid,
                'articleId' => $row->llr_pageid,
                'key' => $row->llr_itemkey,
                'nthItem' => $row->llr_nthitem,
                "objectnumber" => $numberText
            );
        }
        #dd($objects);
        return $objects;
    }
	
	// returns number of literature items in structure before the given structureItem
    public static function getLiteratureNumberingsForPage ( LoopStructureItem $lsi, LoopStructure $loopStructure ) {

		$objects = array('cite');
		$return = array('cite' => 0);
		#foreach (LoopObject::$mObjectTypes as $objectType) {
		#	$objects[$objectType] = array(); 
		#	$return[$objectType] = 0; 
		##}

		$dbr = wfGetDB( DB_REPLICA );

		$res = $dbr->select(
			'loop_literature_references',
			array(
                'llr_itemkey',
                'llr_pageid',
                'llr_refid',
                'llr_nthitem'
			),
			"*",
			__METHOD__
		);
		foreach( $res as $row ) {
			$objects['cite'][$row->llr_pageid] = array();
			$objects['cite'][$row->llr_pageid][] = $row->llr_refid;
		}

		$structureItems = $loopStructure->getStructureItems();
		foreach ( $structureItems as $item ) {
			$tmpId = $item->article;
			if (  $item->sequence < $lsi->sequence  ) {
				foreach( $objects as $objectType => $page ) {
					// dd($objects, $objectType);
					if ( isset( $page[$tmpId] ) ) {
						$return[$objectType] += sizeof($page[$tmpId]);
					}
				}
			}
		}
		
        return $return;
    }
    
	// returns number of objects in glossary pages before current glossary page
    public static function getLiteratureNumberingsForGlossaryPage ( $articleId ) {
       /* $glossaryItems = LoopGlossary::getGlossaryPages();
        $data = array();
        $return = array();
        $pageHasObjects = false;
        if ( !empty ($glossaryItems) ) {
            foreach ( $glossaryItems as $sequence => $item ) {
                $tmpArticleId = $item->getArticleID();
                $data[$sequence] = array( $tmpArticleId );
                if ( $tmpArticleId == $articleId ) {
                    $pageHasObjects = true;
                    break;
                }
            }
        }
        if ( $pageHasObjects ) {
            
			$objects = array('cite');
			$return = array('cite' => 0 ); 
            
            $dbr = wfGetDB( DB_REPLICA );

            $res = $dbr->select(
                'loop_literature_references',
                array(
					'llr_itemkey',
					'llr_pageid',
					'llr_refid',
					'llr_nthitem'
                ),
                "*",
                __METHOD__
            );
            foreach ( $res as $row ) {
                $objects['cite'][$row->llr_pageid][] = $row->llr_refid;
            }

            foreach ( $data as $pos => $tmpId ) {
                foreach( $objects as $objectType => $page ) {
					#dd($objects, $objectType,  $page );
                    if ( $tmpId[0] != $articleId ) {
                        if ( array_key_exists( $tmpId[0], $page ) ) {
                            $return[$objectType] += sizeof($page[$tmpId[0]]);
                        }  
                    }
                }
            }
        }
        return $return;*/
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
		$out->setPageTitle($this->msg('loopliterature'));
		$html = '';

		if ( $user->isAllowed('loop-edit-literature') ) {
			$html .= $linkRenderer->makeLink(
				new TitleValue( NS_SPECIAL, 'LoopLiteratureEdit' ),
				new HtmlArmor( $this->getSkin()->msg( "loopliteratureedit" ) ),
				array("class"=>"aToc")
				
			);
			
			$loopStructure = new LoopStructure();
			$loopStructure->loadStructureItems();

			$allItems = LoopLiteratureReference::getAllItems( $loopStructure );
			dd($allItems);
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

class SpecialLoopLiteratureEdit extends SpecialPage {

	public function __construct() {
		parent::__construct( 'LoopLiteratureEdit' );
	}

	public function execute( $sub ) {

		global $wgSecretKey;
		$user = $this->getUser();
		$out = $this->getOutput();

		if ( $user->isAllowed('loop-edit-literature') ) {

			$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
			$request = $this->getRequest();

			$saltedToken = $user->getEditToken( $wgSecretKey, $request );
			$requestToken = $request->getText( 't' );

			$out->setPageTitle($this->msg('loopliteratureedit')->text());
			$out->addModules( 'ext.loop-literature-edit.js' );

			$html = '';
			
			if ( ! empty( $requestToken ) ) {

				if ( $user->matchEditToken( $requestToken, $wgSecretKey, $request ) ) {

					$loopLiterature = new LoopLiterature;
					$loopLiterature->getLiteratureFromRequest( $request );
					#dd("hiiii", $loopLiterature);
					if ( empty ( $loopLiterature->errors ) ) {
						#dd("no errors",  $loopLiterature);
						$loopLiterature->addToDatabase();

						$html .= '<div class="alert alert-success" role="alert">' . $this->msg("loopliterature-alert-saved", $loopLiterature->key ) . '</div>';

					} else {
						$errorMsgs = $this->msg("loopliterature-error-notsaved")."<br>";
						foreach( $loopLiterature->errors as $error ) { 
							
							$errorMsgs .= $error . '<br>';
							
						}
						$html .= '<div class="alert alert-danger" role="alert" id="literature-error">' . $errorMsgs . '</div>';
						
					}
					#if (  )
				}

			}
			$existingKeys = LoopLiterature::getAllLiteratureItems( array("key" => true ) );
			#dd();
			$html .= "<script>\n";
			$keyString = '';
			foreach ( $existingKeys as $key ) {
				$keyString .= '"'.$key.'", ';
			} 
			$html .= '$existingKeys = [' . $keyString . ']';
			$html .= "</script>";

			$typesOfLiterature = array( "article", "book", "booklet", "conference", "inbook", "incollection", "inproceedings", "manual", "mastersthesis", "misc", "phdthesis", "proceedings", "techreport", "unpublished");
			$fieldData = array (
				'address' => array ( 'max-length' => 255 ), 
				'author' => array ( 'max-length' => 255 ), 
				'booktitle' => array ( 'max-length' => 255 ), 
				'chapter' => array ( 'type' => 'number' ), 
				'edition' => array ( 'type' => 'number', ), 
				'editor' => array ( 'max-length' => 255 ), 
				'howpublished' => array ( 'max-length' => 255 ), 
				'institution' => array ( 'max-length' => 255 ), 
				'isbn' => array ( 'type' => 'number', 'min-length' => 10, 'max-length' => 13 ), 
				'journal' => array ( 'max-length' => 255 ), 
				'note' => array ( ), 
				'number' => array ( 'type' => 'number', 'max-length' => 255 ), 
				'organization' => array ( 'max-length' => 255 ), 
				'pages' => array ( 'max-length' => 255 ), 
				'publisher' => array ( 'max-length' => 255 ), 
				'school' => array ( 'max-length' => 255 ), 
				'series' => array ( 'max-length' => 255 ), 
				'itemtitle' => array ( 'max-length' => 255 ), 
				'type' => array ( 'max-length' => 255 ), 
				'url' => array ( 'type' => 'url', 'max-length' => 255 ), 
				'volume' => array ( 'max-length' => 255 ), 
				'month' => array ( 'max-length' => 255 ), 
				'year' => array ('type' => 'number', 'length' => 4 )
				);


			$html .= '<form id="literature-entry">';
			$html .= '<div class="form-group">';

			$html .= '<div class="form-row">';
			$html .= '<label for="itemType" class="font-weight-bold">'.$this->msg('loopliterature-label-itemType')->text().'</label>';
			$html .= '<select id="itemType" name="itemType" class="form-control form-control-lg">';
			$i = 0;
			foreach( $typesOfLiterature as $option ) {
				$selected = ( $i == 1 ) ? "selected" : ""; #1 => book
				$i++;
				$html .= '<option '.$selected.' value="'.$option.'">';
				$html .= $this->msg('loopliterature-entry-'.$option)->text();
				$html .= '</option>';
			}
			$html .= '</select>';
			$html .= '</div>';

			$articleData = array(
				"required" => array( "author", "editor", "itemtitle", "publisher", "year" ),
				"optional" => array( "volume", "number", "series", "address", "edition", "month", "note", "isbn", "url" )
			);

			$requiredObjects = '<div class="literature-field col-12 mb-3"><label for="key">'.$this->msg('loopliterature-label-key')->text().'</label>';
			$requiredObjects .= '<input  class="form-control" id="key" name="key" max-length="255" required/>';
			$requiredObjects .= '<div class="invalid-feedback" id="keymsg">'.$this->msg("loopliterature-error-keyalreadyexists")->text().'</div></div>';
			$optionalObjects = '';
			$otherObjects = '';

			#dd($fieldData);
			foreach( $fieldData as $field => $arr ) {
				$required = ( in_array( $field, $articleData["required"] ) ) ? "required" : "";
				$optional = ( in_array( $field, $articleData["optional"] ) ) ? true : false;
				$disabled = ( $optional || $required == "required" )  ? "" : "disabled";
				$visibility = ( empty($disabled) )  ? "" : " d-none";

				$fieldHtml = '';
				$fieldHtml .= '<div class="literature-field col-6'.$visibility.'">';
				$fieldHtml .= '<label for="'.$field.'">'.$this->msg('loopliterature-label-'.$field)->text().'</label>';

				$attributes = '';
				foreach ( $arr as $key => $val ) {
					if ( gettype ($val ) == "string" ) {
						$attributes .= $key . '=\'' . $val . '\' ';
					}
					#dd($key, $val, $attributes);
				}
				$type = ( isset( $arr["type"] ) ) ? $arr["type"] : "text";
				#$i++;
				$fieldHtml .= '<input  class="form-control" id="'.$field.'" name="'.$field.'" ' . $attributes . ' '.$required.' '.$disabled.'/>';
				$fieldHtml .= '</div>';

				if ( $required == "required" ) {
					$requiredObjects .= $fieldHtml;
				} elseif ( $optional ) {
					$optionalObjects .= $fieldHtml;
				} else {
					$otherObjects .= $fieldHtml;
				} 
			}
			$html .= '<div class="form-row border pb-3 mt-3" id="required-row"><h4 class="w-100 pt-2 pl-2">'.$this->msg('loopliterature-label-required')->text().'</h4>'.$requiredObjects.'</div>';
			$html .= '<div class="form-row border pb-3 mt-3" id="optional-row"><h4 class="w-100 pt-2 pl-2">'.$this->msg('loopliterature-label-optional')->text().'</h4>'.$optionalObjects.'</div>';
			$html .= '<div class="form-row" id="disabled-row">'.$otherObjects.'</div>';

			$html .= '</div>';

			$html .= '<input type="hidden" name="t" id="loopliterature-token" value="' . $saltedToken . '"></input>';
			$html .= '<input type="submit" class="mw-htmlform-submit mw-ui-button mw-ui-primary mw-ui-progressive mt-2 d-block" id="loopliterature-submit" value="' . $this->msg( 'submit' ) . '"></input>';
			

			$html .= '</form>';

			$out->addHTML( $html );
		} else {
			$html = '<div class="alert alert-warning" role="alert">' . $this->msg( 'specialpage-no-permission' ) . '</div>';
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
		$out = $this->getOutput();
		$out->setPageTitle($this->msg('loopliteratureimport'));

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

