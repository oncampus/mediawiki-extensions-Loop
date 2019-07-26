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
    public $itemKey;
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
    public $itemTitle;
    public $type;
    public $url;
    public $volume;
    public $year;
    public $doi;
    public $errors;

	public function __construct() {

		$this->literatureTypes = array(
			"article" => array(
				"required" => array( "author", "itemTitle", "journal", "year" ),
				"optional" => array( "volume", "number", "pages", "month", "note", "url", "doi" )
			),
			"book" => array(
				"required" => array( "author", "editor", "itemTitle", "publisher", "year" ),
				"optional" => array( "volume", "number", "series", "address", "edition", "month", "note", "isbn", "url", "doi" )
			),
			"booklet" => array(
				"required" => array( "itemTitle" ),
				"optional" => array( "author", "howpublished", "address", "month", "year", "note", "url", "doi" )
			),
			"conference" => array(
				"required" => array( "author", "itemTitle", "booktitle", "year" ),
				"optional" => array( "editor", "volume", "number", "series", "pages", "address", "month", "organization", "publisher", "note", "url" )
			),
			"inbook" => array(
				"required" => array( "author", "editor", "itemTitle", "chapter", "pages", "publisher", "year" ),
				"optional" => array( "volume", "number", "series", "type", "address", "edition", "month", "note", "url", "doi" )
			),
			"incollection" => array(
				"required" => array( "author", "itemTitle", "booktitle", "publisher", "year" ),
				"optional" => array( "editor", "volume", "number", "series", "type", "chapter", "pages", "address", "edition", "month", "note", "url", "doi" )
			),
			"inproceedings" => array(
				"required" => array( "author", "itemTitle", "booktitle", "year" ),
				"optional" => array( "editor", "volume", "number", "series", "pages", "address", "month", "organization", "publisher", "note", "url" )
			),
			"manual" => array(
				"required" => array( "address", "itemTitle", "year" ),
				"optional" => array( "author", "organization", "edition", "month", "note", "url" )
			),
			"mastersthesis" => array(
				"required" => array( "author", "itemTitle", "school", "year" ),
				"optional" => array( "type", "address", "month", "note", "url" )
			),
			"misc" => array(
				"required" => array(),
				"optional" => array( "author", "itemTitle", "howpublished", "month", "year", "note", "url", "doi" )
			),
			"phdthesis" => array(
				"required" => array( "author", "itemTitle", "school", "year" ),
				"optional" => array( "type", "address", "month", "note", "url" )
			),
			"proceedings" => array(
				"required" => array( "itemTitle", "year" ),
				"optional" => array( "editor", "volume", "number", "series", "address", "month", "organization", "publisher", "note", "url" )
			),
			"techreport" => array(
				"required" => array( "author", "itemTitle", "institution", "year" ),
				"optional" => array( "type", "note", "number", "address", "month", "url" )
			),
			"unpublished" => array(
				"required" => array( "author", "itemTitle", "note" ),
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

		$dbw = wfGetDB( DB_MASTER );
		
		$dbw->insert(
			'loop_literature_items',
				array(
				'lit_itemkey' => $this->itemKey,
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
				'lit_title' => $this->itemTitle,
				'lit_type' => $this->type,
				'lit_url' => $this->url,
				'lit_volume' => $this->volume,
				'lit_year' => $this->year,
				'lit_doi' => $this->doi
			)
		);
		

        return true;
	}

	
	// deletes all literature references of a page
    public static function removeFromDatabase ( $key ) {
		$dbr = wfGetDB( DB_MASTER );
		$dbr->delete(
			'loop_literature_items',
			'lit_itemkey = "' . $key .'"',
			__METHOD__
		);

        return true;
	}

    /**
     * Puts request content into object
     *
     * @param Request $request 
     */

    public function getLiteratureFromRequest ( $request ) {

		$key = $request->getText( 'itemKey' );
		$itemType = $request->getText( 'itemType' );
		
		if ( ! empty ( $key ) ) {
			
		 	if ( ! empty ( $itemType ) ) {
				$loopLiterature = new LoopLiterature();
				$loopLiteratureItem = $loopLiterature->loadLiteratureItem( $key );
				if ( ! $loopLiteratureItem || $request->getText( 'overwrite' ) == true ) {
					if ( $request->getText( 'overwrite' ) == true ) {
						self::removeFromDatabase ( $key );
					}
					$this->itemKey = $key;

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
											case "itemTitle":
												$this->itemTitle = $value;
												break;
											case "edition":
												$this->edition = intval($value);
												break;
											case "number":
											$this->number = intval($value);
											break;
											case "url":
												$this->url = $value;
												break;

											default:
												$this->$field = $value;	
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
				
			case "url":
				if ( filter_var( $val, FILTER_VALIDATE_URL ) && ! strpos($val, '<script>') ) {
					return true;
				} else { return false; }
				
			case "note":
				if ( ! strpos($val, '<script>') ) {
					return true;
				} else { return false; }
				
			default:
				if ( strlen( $val ) <= 255 && ! strpos($val, '<script>') ) {
					return true;
				} else { return false; }
				
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
				'lit_year',
				'lit_doi'
            ),
            array(),
            __METHOD__
        );

		$return = array();

        foreach ( $res as $row ) {
			
			if ( isset ( $data["itemKey"] ) ) {
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
				$return[$row->lit_itemkey]["itemTitle"] = $row->lit_title;
				$return[$row->lit_itemkey]["type"] = $row->lit_type;
				$return[$row->lit_itemkey]["url"] = $row->lit_url;
				$return[$row->lit_itemkey]["volume"] = $row->lit_volume;
				$return[$row->lit_itemkey]["year"] = $row->lit_year;
				$return[$row->lit_itemkey]["doi"] = $row->lit_doi;
					
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
				'lit_year',
				'lit_doi'
            ),
            array(
                 'lit_itemkey = "' . $key .'"' 
            ),
            __METHOD__
        );

		$item = false;
        foreach ( $res as $row ) {
			$item = true;
			
			$this->itemKey = $row->lit_itemkey;
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
			$this->itemTitle = $row->lit_title;
			$this->type = $row->lit_type;
			$this->url = $row->lit_url;
			$this->volume = $row->lit_volume;
			$this->year = $row->lit_year;
			$this->doi = $row->lit_doi;

		}
		if ( ! $item ) {
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
		global $wgLoopLiteratureCiteType;
		$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();

		$loopLiterature = new LoopLiterature();
		$loopLiteratureItem = $loopLiterature->loadLiteratureItem( $input );
		
		
		if ( ! $loopLiteratureItem ) {
			
			$e = new LoopException( wfMessage( 'loopliterature-error-keyunknown', $input )->text() );
			$parser->addTrackingCategory( 'loop-tracking-category-error' );
			
			return $e;
		} else {
			#dd();
			/* displays data from entry
			if ( isset( $loopLiterature->author ) ) {
				$text .= $loopLiterature->author;
			} elseif ( isset( $loopLiterature->itemTitle )) {
				$text .= $loopLiterature->itemTitle;
			}
			if ( isset( $loopLiterature->year ) ) {
				$text .= " " . $loopLiterature->year;
			} */
			if ( $wgLoopLiteratureCiteType == "harvard" ) {
				$text = str_replace( "+" , " " , $input );
				$html = $linkRenderer->makeLink( 
					new TitleValue( NS_SPECIAL, 'LoopLiterature' ), 
					new HtmlArmor( $text ),
					array( 
						"title" => str_replace( "+" , " " , $input ),
						"data-target" => $input # target id will be added in hook
					) 
				);
				if ( isset( $args["page"] ) ) {
					$html .= ", " . wfMessage("loopliterature-text-pages", 1)->text() . " " . $args["page"];
				} elseif ( isset( $args["pages"] ) ) {
					$html .= ", " . wfMessage("loopliterature-text-pages", 2)->text() . " " . $args["pages"];
				} 
			} elseif ( $wgLoopLiteratureCiteType == "vancouver" ) {
						
				$loopStructure = new LoopStructure();
				$loopStructure->loadStructureItems();
				$allReferences = LoopLiteratureReference::getAllItems( $loopStructure );
				$refId = $args["id"];
				$articleId = $parser->mTitle->mArticleID;
				$objectNumber = $allReferences[$articleId][$refId]["objectnumber"];
				#$reference = LoopLiteratureReference::getItemData( $args["id"] );
				#dd( $allReferences[$articleId][$refId]["objectnumber"] );
				
				$text = "<sup>".$objectNumber."</sup>";

				$html = $linkRenderer->makeLink( 
					new TitleValue( NS_SPECIAL, 'LoopLiterature' ), 
					new HtmlArmor( $text ),
					array( 
						"title" => str_replace( "+" , " " , $input ),
						"data-target" => $refId # target id will be added in hook
					) 
				);
			}
		}

		return $html;
	}

	static function renderLoopLiterature( $input, array $args, Parser $parser, PPFrame $frame ) {

		global $wgLoopLiteratureCiteType; 

		$lines = str_replace( "\n", " ", $input );
		$lines = str_replace( "\t", " ", $lines );
		$keys = array();
		if ( ! empty ( $lines ) ) {
			$words = explode ( " ", $lines );
			foreach( $words as $word ) {
				$keywords = explode ( "#", $word );
				foreach ( $keywords as $key ) {
					if ( !empty( $key ) ) {
						$keys[] = $key;
					}
				}
			}
		}
		$html = '<div class="loop-literature mb-1 ml-4">';
		if ( !empty ( $keys ) ) {
			$htmlElements = array();
			#$allReferences = LoopLiteratureReference::getAllItems( $loopStructure );
			$allItems = LoopLiterature::getAllItems();
	
			foreach ( $keys as $key ) {
				if ( isset ( $allItems[$key] ) ) {
					if ( $allItems[$key]->author ) {
						$orderkey = ucfirst($allItems[$key]->author);
	
					} elseif ( $allItems[$key]->itemTitle ) {
						$orderkey = ucfirst($allItems[$key]->itemTitle);
					}
					$htmlElements[$orderkey] = '<p class="literature-entry mb-2" id="'.$key.'">';

					$literatureItem = $allItems[$key];
					$htmlElements[$orderkey] .= LoopLiterature::renderLiteratureElement($literatureItem);
					#$literatureItem["author"];
					if ( $wgLoopLiteratureCiteType == "harvard" ) {
						unset($allItems[$key]);
					}
					$htmlElements[$orderkey] .= '</p>';
				} else {
					$htmlElements[$key] = new LoopException( wfMessage( 'loopliterature-error-keyunknown', $key )->text() );
					$parser->addTrackingCategory( 'loop-tracking-category-error' );
			
				}
			}
			if ( $wgLoopLiteratureCiteType == 'harvard') {
				ksort( $htmlElements, SORT_STRING );
			}
			foreach ( $htmlElements as $element ) {
				$html .= $element;
			}
		}
		$html .= '</div>';
		return $html;
	}
#todo other hooks!
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
						$tmpLoopLiteratureReference->itemKey = $object[1];
						
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

	 // returns all literature items from table
	 public static function getAllItems ( $returnType = null ) {
		
		$items = array();

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
				'lit_year',
				'lit_doi'
			),
			array(),
            __METHOD__,
            array('ORDER BY' => 'lit_author ASC')
        );

        foreach ( $res as $row ) {
		
			if ( $returnType == "array" ) {
				$items[$row->lit_itemkey] = array(
					"itemType" => $row->lit_itemtype,
					"address" => $row->lit_address,
					"author" => $row->lit_author,
					"booktitle" => $row->lit_booktitle,
					"chapter" => $row->lit_chapter,
					"edition" => $row->lit_edition,
					"editor" => $row->lit_editor,
					"howpublished" => $row->lit_howpublished,
					"institution" => $row->lit_institution,
					"isbn" => $row->lit_isbn,
					"journal" => $row->lit_journal,
					"month" => $row->lit_month,
					"note" => $row->lit_note,
					"number" => $row->lit_number,
					"organization" => $row->lit_organization,
					"pages" => $row->lit_pages,
					"publisher" => $row->lit_publisher,
					"school" => $row->lit_school,
					"series" => $row->lit_series,
					"itemTitle" => $row->lit_title,
					"type" => $row->lit_type,
					"url" => $row->lit_url,
					"volume" => $row->lit_volume,
					"year" => $row->lit_year,
					"doi" => $row->lit_doi
				);
			} else {
				
				$tmpLiterature = new LoopLiterature;
				$tmpLiterature->itemKey = $row->lit_itemkey;
				$tmpLiterature->itemType = $row->lit_itemtype;
				$tmpLiterature->address = $row->lit_address;
				$tmpLiterature->author = $row->lit_author;
				$tmpLiterature->booktitle = $row->lit_booktitle;
				$tmpLiterature->chapter = $row->lit_chapter;
				$tmpLiterature->edition = $row->lit_edition;
				$tmpLiterature->editor = $row->lit_editor;
				$tmpLiterature->howpublished = $row->lit_howpublished;
				$tmpLiterature->institution = $row->lit_institution;
				$tmpLiterature->isbn = $row->lit_isbn;
				$tmpLiterature->journal = $row->lit_journal;
				$tmpLiterature->month = $row->lit_month;
				$tmpLiterature->note = $row->lit_note;
				$tmpLiterature->number = $row->lit_number;
				$tmpLiterature->organization = $row->lit_organization;
				$tmpLiterature->pages = $row->lit_pages;
				$tmpLiterature->publisher = $row->lit_publisher;
				$tmpLiterature->school = $row->lit_school;
				$tmpLiterature->series = $row->lit_series;
				$tmpLiterature->itemTitle = $row->lit_title;
				$tmpLiterature->type = $row->lit_type;
				$tmpLiterature->url = $row->lit_url;
				$tmpLiterature->volume = $row->lit_volume;
				$tmpLiterature->year = $row->lit_year;
				$tmpLiterature->doi = $row->lit_doi;
				
				$items[$row->lit_itemkey] = $tmpLiterature;
			}
		}
		#dd($items);
        return $items;
	}
	
	/**
	 * @param LoopLiterature $li entry to render
	 * @param Array $ref data about the reference
	 */
	public static function renderLiteratureElement( $li, $ref = null ) {

		global $wgOut, $wgLoopLiteratureCiteType;

		$user = $wgOut->getUser();
		$editMode = $user->getOption( 'LoopEditMode', false, true );
		$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();

		$return = '';

		if ( $wgLoopLiteratureCiteType == 'vancouver' && $ref ) {
			$return .= "<span class='literature-vancouver-number'>".$ref["objectnumber"].". </span>";
			#dd($li, $ref);
		}
			# Author/''Title''. (editor). (year). Series. ''Title'' (Type)(Volume). Publisher/Institution/school
		if ( $li->author ) {
			$return .= $li->author.". ";
		} elseif ( $li->itemTitle ) {
			if ( $li->itemType == "LOOP1" ) {
				$return .= $li->itemTitle.". ";
			} else {
				$return .= "<i>".$li->itemTitle."</i>. ";
			}
		}

		if ( $li->editor ) { # short, only (Hrsg.). if different from author, it will be mentioned later
			if ( $li->author == $li->editor ) {
				$return .= "(".wfMessage("loopliterature-text-publisher")->text()."). ";
			}
		} 

		if ( $li->year ) {
			$monthText = "";
			if ( $li->month ) {
				$monthText = $li->month . " ";
			}
			if ( $li->itemType == "unpublished" ) {
				$return .= "(".$monthText.$li->year.", ".wfMessage("loopliterature-text-unpublished")->text()."). ";
			} else {
				$return .= "(".$monthText.$li->year."). ";
			}
		} elseif ( $li->itemType == "unpublished" ) {
			$return .= "(".wfMessage("loopliterature-text-unpublished")->text()."). ";
		} elseif ( $li->itemType != "LOOP1" ) { #legacy loop 1
			$return .= "(".wfMessage("loopliterature-text-noyear")->text()."). ";
		}
		
		if ( $li->chapter ) {
			$return .= $li->chapter.". ";
		} 
		if ( $li->editor ) {
			if ( $li->author != $li->editor ) {
				$return .= wfMessage("loopliterature-text-inpublisher", $li->editor)->text() . ", ";
			}
		} 

		if ( $li->author && $li->itemTitle ) {
			if ( $li->itemType == "article" ) {
				$return .= $li->itemTitle;
			} else {
				$return .= "<i>".$li->itemTitle."</i>";
			}
			if ( ! $li->volume || ! $li->type || ! $li->edtion || ! $li->pages || ! $li->howpublished || ! $li->series ) {
				$return .= ". ";
			} else {
				$return .= " ";
			}
		}

		if ( $li->booktitle ) {
			$return .= $li->booktitle . ". ";
		}

		if ( $li->pages ) {
			if ( ! strpos( $li->pages , ',' ) && ! strpos( $li->pages , '-' )  && ! strpos( $li->pages , ' ' ) ) {
				$plural = 2;
			} else {
				$plural = 1;
			}
			$return .= "(". wfMessage("loopliterature-text-pages", $plural)->text() .$li->pages."). ";
		} 

		if ( $li->journal ) {
			$return .= $li->journal. ". ";
		} 
		if ( $li->series ) {
			$return .= "(".$li->series."). ";
		} 
		if ( $li->type ) {
			$return .= "(".$li->type."). ";
		} 
		if ( $li->volume ) {
			$return .= "(".$li->volume."). ";
		} 
		if ( $li->edition ) {
			$return .= "(".$li->edition."). ";
		} 
		if ( $li->howpublished ) {
			$return .= "(".$li->howpublished."). ";
		} 
		if ( $li->number ) {
			$return .= "(".$li->number."). ";
		} 
		

		if ( $li->publisher ) {
			$return .= $li->publisher.". ";
		} elseif ( $li->journal ) {
			$return .= "<i>".$li->journal.".</i> ";
		}

		if ( $li->institution ) {
			$return .= $li->institution.". ";
		} 
		if ( $li->school ) {
			$return .= $li->school.". ";
		} 
		if ( $li->isbn ) {
			$return .= "ISBN: " . $li->isbn.". ";
		} 
		if ( $li->doi ) {
			$return .= "DOI: " . $li->doi.". ";
		} 
		if ( $li->url ) {
			$return .= wfMessage("loopliterature-text-url")->text() . " " . $li->url.". ";
		} 
		if ( $li->address ) {
			$return .= $li->address.". ";
		} 

		if ( $li->note && $editMode && $li->itemType != "LOOP1" ) {
			$return .= '<span class="literature-itemkey font-italic text-black-50">'.wfMessage("loopliterature-text-note")->text() . ": " . $li->note.'. </span>';
		} 

		if ( $li->itemType == "LOOP1" && $li->note ) {
			$return .= $li->note . " ";
		}

		#}

		if ( $editMode ) {
			$return .= '<span class="literature-itemkey font-italic text-black-50" title="'.wfMessage("loopliterature-label-key")->text().'">'.$li->itemKey.' </span>';

			if ( $user->isAllowed('loop-edit-literature') ) {
				$return .= $linkRenderer->makelink( 
					new TitleValue( 
						NS_SPECIAL, 
						'LoopLiteratureEdit' 
					), 
					new HtmlArmor( '<span class="ic ic-edit"></span>' ), 
					array( 'title' => wfMessage( "loopliteratureedit" )->text(), "class" => "icon-link" ),
					array( 'edit' => $li->itemKey )
				);
				$return .= '<span class="dropright ml-1 cursor-pointer" title="'.wfMessage( "loopliterature-label-deleteentry" )->text().'">';
				$return .= '<span class="ic ic-delete dropdown-toggle d-inline accent-color literature-dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></span>';
				$return .= '<span class="dropdown-menu">';	
				$return .= $linkRenderer->makelink( 
					new TitleValue( 
						NS_SPECIAL, 
						'LoopLiterature' 
					), 
					new HtmlArmor( wfMessage( "loopliterature-warning-delete" )->text() ), 
					array( 'title' => wfMessage( "loopliterature-warning-delete" )->text(), "class" => "dropdown-item text-danger literature-delete" ),
					array( 'delete' => $li->itemKey )
				);
				$return .= '</span></span>';
			}
		}

		return $return;
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
                'llr_itemkey' => $this->itemKey,
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
                'itemKey' => $row->llr_itemkey,
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
					'itemKey' => $row->llr_itemkey,
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
        
            $objects[$row->llr_pageid][$row->llr_refid] = array(
                'refId' => $row->llr_refid,
                'articleId' => $row->llr_pageid,
                'itemKey' => $row->llr_itemkey,
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
       $glossaryItems = LoopGlossary::getGlossaryPages();
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
            
			$objects = array();
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
                    if ( $tmpId[0] != $articleId ) {
                        if ( array_key_exists( $tmpId[0], $page ) ) {
                            $return[$objectType] += sizeof($page[$tmpId[0]]);
                        }  
                    }
                }
            }
        }
        return $return;
    }
}

class SpecialLoopLiterature extends SpecialPage {

	public function __construct() {
		parent::__construct( 'LoopLiterature' );
	}

	public function execute( $sub ) {
		global $wgLoopLiteratureCiteType;

		$user = $this->getUser();
		$editMode = $user->getOption( 'LoopEditMode', false, true );
		$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
		$out = $this->getOutput();
		$out->setPageTitle($this->msg('loopliterature'));
		$request = $this->getRequest();
		$html = '';

		$deleteKey = $request->getText( 'delete' );
		if ( $deleteKey ) {
			LoopLiterature::removeFromDatabase($deleteKey);
			$html .= '<div class="alert alert-success">'.$this->msg("loopliterature-alert-deleted", $deleteKey).'</div>';
		}

		if ( $user->isAllowed('loop-edit-literature') &&  $editMode ) {
			$html .= $linkRenderer->makeLink(
				new TitleValue( NS_SPECIAL, 'LoopLiteratureEdit' ),
				new HtmlArmor( '<div class="btn btn-sm mw-ui-button mw-ui-primary mw-ui-progressive">' . $this->msg( "loopliterature-label-addentry" ) . "</div>" ),
				array()
				
			);
		}

		$html .= '<div class="bibliography ml-4">';
		


		$loopStructure = new LoopStructure();
		$loopStructure->loadStructureItems();

		$allReferences = LoopLiteratureReference::getAllItems( $loopStructure );
		$allItems = LoopLiterature::getAllItems();
		
		$htmlElements = array();
		foreach ( $allReferences as $pageId => $pageReferences ) {
			#dd();
			foreach ( $pageReferences as $refId => $referenceData) {
				#$orderkey = ( $allItems[$referenceData["itemKey"]]->author ) ? $allItems[$referenceData["itemKey"]]->author : $allItems[$referenceData["itemKey"]]->itemTitle;
				if ( isset ( $allItems[$referenceData["itemKey"]] ) ) {
					if ( $wgLoopLiteratureCiteType == 'harvard') {
						if ( $allItems[$referenceData["itemKey"]]->author ) {
							$orderkey = ucfirst($allItems[$referenceData["itemKey"]]->author);
		
						} elseif ( $allItems[$referenceData["itemKey"]]->itemTitle ) {
							$orderkey = ucfirst($allItems[$referenceData["itemKey"]]->itemTitle);
						}
					} else {
						$orderkey = ucfirst($referenceData["objectnumber"]);
					}
					$htmlElements[$orderkey] = '<p class="literature-entry" id="'.$referenceData["itemKey"].'">';

					$literatureItem = $allItems[$referenceData["itemKey"]];
					$htmlElements[$orderkey] .= LoopLiterature::renderLiteratureElement($literatureItem, $referenceData);
					#$literatureItem["author"];
					#if ( $wgLoopLiteratureCiteType == "harvard" ) {
					#	unset($allItems[$referenceData["itemKey"]]);
					#} else {
					$referencedItems[] = $referenceData["itemKey"];
					#}
					$htmlElements[$orderkey] .= '</p>';
				}
			}
		}
		ksort( $htmlElements, SORT_STRING );
		foreach ( $htmlElements as $element ) {
			$html .= $element;
		}
		#dd($allItems);
		if ( $editMode && ! empty ( $allItems ) ) {
			$htmlElements = array();
			$html .= "<hr class='mr-4'/>";
			$html .= "<p class='font-weight-bold' id='literature-unreferenced'>".$this->msg( "loopliterature-text-notreferenced" ).":</p>";

			foreach ( $allItems as $item ) {
				if ( ! in_array( $item->itemKey, $referencedItems ) ) {
					#dd( $item->itemKey, $referencedItems );
					if ( $item->author ) {
						$orderkey = ucfirst($item->author);
					} elseif ( $item->itemTitle ) {
						$orderkey = ucfirst($item->itemTitle);
					}
	
					$htmlElements[$orderkey] = '<p class="literature-entry">';
					$htmlElements[$orderkey] .= LoopLiterature::renderLiteratureElement($item);
					$htmlElements[$orderkey] .= '</p>';
				} else {
					
				#dd( $item->itemKey, $referencedItems );
				}
					
			}
			ksort( $htmlElements, SORT_STRING );
			foreach ( $htmlElements as $element ) {
				$html .= $element;
			}
		}
		$html .= '</div>';
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

			$html = '';

			$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
			$request = $this->getRequest();
			$editKey = $request->getText( 'edit' );
			http_build_query(array());
			if ( $editKey ) {
				$editLiteratureItem = new LoopLiterature;
				$editLiteratureItem->loadLiteratureItem( $editKey );
				$html .= '<script>var editValues = new Array;';
				$html .= 'editValues = {';
				$itemArray = get_object_vars($editLiteratureItem);
				foreach ( $itemArray as $key => $val ) {
					if ( $key != "literatureTypes" && $key != "errors" && isset($val) ) {
						$val = str_replace('"', "&quot;", $val);
						$html .= ''.$key.': "'.$val.'",';
					}
				}
				#dd($html);
				$html .= '}</script>';
			} else {
				$html .= '<script>var editValues = new Array;</script>';
			}

			$saltedToken = $user->getEditToken( $wgSecretKey, $request );
			$requestToken = $request->getText( 't' );

			$out->setPageTitle($this->msg('loopliteratureedit')->text());
			$out->addModules( 'loop.special.literature-edit.js' );

			
			if ( ! empty( $requestToken ) ) {

				if ( $user->matchEditToken( $requestToken, $wgSecretKey, $request ) ) {

					$loopLiterature = new LoopLiterature;
					$loopLiterature->getLiteratureFromRequest( $request );
					#dd("hiiii", $loopLiterature);
					if ( empty ( $loopLiterature->errors ) ) {
						#dd("no errors",  $loopLiterature);
						$loopLiterature->addToDatabase();

						$html .= '<div class="alert alert-success" role="alert">' . $this->msg("loopliterature-alert-saved", $loopLiterature->itemKey ) . '</div>';

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
			$existingKeys = LoopLiterature::getAllLiteratureItems( array("itemKey" => true ) );
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
				'chapter' => array ( 'max-length' => 255 ), 
				'edition' => array ( 'type' => 'number', ), 
				'editor' => array ( 'max-length' => 255 ), 
				'doi' => array ( 'max-length' => 255 ), 
				'howpublished' => array ( 'max-length' => 255 ), 
				'institution' => array ( 'max-length' => 255 ), 
				'isbn' => array ( 'min-length' => 10, 'max-length' => 17 ), 
				'journal' => array ( 'max-length' => 255 ), 
				'note' => array ( ), 
				'number' => array ( 'type' => 'number', 'max-length' => 255 ), 
				'organization' => array ( 'max-length' => 255 ), 
				'pages' => array ( 'max-length' => 255 ), 
				'publisher' => array ( 'max-length' => 255 ), 
				'school' => array ( 'max-length' => 255 ), 
				'series' => array ( 'max-length' => 255 ), 
				'itemTitle' => array ( 'max-length' => 255 ), 
				'type' => array ( 'max-length' => 255 ), 
				'url' => array ( 'type' => 'url', 'max-length' => 255 ), 
				'volume' => array ( 'max-length' => 255 ), 
				'month' => array ( 'max-length' => 255 ), 
				'year' => array ( 'max-length' => 255 )
				);


			$html .= '<form id="literature-entry">';
			$html .= '<div class="form-group">';

			$html .= '<div class="form-row">';
			$html .= '<label for="itemType" class="font-weight-bold">'.$this->msg('loopliterature-label-itemType')->text().'</label>';
			$html .= '<select id="itemType" name="itemType" class="form-control form-control-lg">';
			
			foreach( $typesOfLiterature as $option ) {
				
				if ( $editKey ) {
					if ( $editLiteratureItem->itemType == "LOOP1" ) {
						$selected = ( $option == "misc" ) ? "selected" : "";
					} else {
						$selected = ( $option == $editLiteratureItem->itemType ) ? "selected" : "";
					}
				} else {
					$selected = ( $option == "book" ) ? "selected" : ""; 
				}
				$html .= '<option '.$selected.' value="'.$option.'">';
				$html .= $this->msg('loopliterature-entry-'.$option)->text();
				$html .= '</option>';
			}
			$html .= '</select>';
			$html .= '</div>';

			$loopLiterature = new LoopLiterature;
			if ( $editKey ) {
				if ( $editLiteratureItem->itemType == "LOOP1" ) {
					$presetData = $loopLiterature->literatureTypes["misc"];
				} else {
					$presetData = $loopLiterature->literatureTypes[$editLiteratureItem->itemType];
				}
			} else {
				$presetData = $loopLiterature->literatureTypes["book"];
			}
			#dd($presetData);
			#array(
			#	"required" => array( "author", "editor", "itemTitle", "publisher", "year" ),
			#	"optional" => array( "volume", "number", "series", "address", "edition", "month", "note", "isbn", "url" )
			#);

			$requiredObjects = '<div class="literature-field col-12 mb-3"><label for="itemKey">'.$this->msg('loopliterature-label-key')->text().'</label>';
			$requiredObjects .= '<input  class="form-control" id="itemKey" name="itemKey" max-length="255" required/>';
			$requiredObjects .= '<div class="invalid-feedback" id="keymsg">'.$this->msg("loopliterature-error-keyalreadyexists")->text().'</div></div>';
			$requiredObjects .= '<div class="col-12 mb-1' . ( $editKey ? '"' :  ' d-none"' ). '>';
			$requiredObjects .= '<input class="mr-2" type="checkbox" id="overwrite" name="overwrite" value="true"' . ( $editKey ? " required checked" : " disabled" ) . '/>';
			$requiredObjects .= '<label for="overwrite">'.$this->msg("loopliterature-label-overwrite")->text().'</label></div>';

			$optionalObjects = '';
			$otherObjects = '';

			#dd($fieldData);
			foreach( $fieldData as $field => $arr ) {
				$required = ( in_array( $field, $presetData["required"] ) ) ? "required" : "";
				$optional = ( in_array( $field, $presetData["optional"] ) ) ? true : false;
				$disabled = ( $optional || $required == "required" )  ? "" : "disabled";
				$visibility = ( empty($disabled) )  ? "" : " d-none";

				$fieldHtml = '';
				$fieldHtml .= '<div class="literature-field col-6'.$visibility.'">';
				$fieldHtml .= '<label for="'.$field.'">'.$this->msg('loopliterature-label-'.$field)->text().'</label>';

				$attributes = '';
				foreach ( $arr as $key => $val ) {
					if ( gettype ( $val ) == "string" ) {
						$attributes .= $key . '=\'' . $val . '\' ';
					}
					#dd($key, $val, $attributes);
				}
				#dd($attributes);
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

