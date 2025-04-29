<?php
/**
 * @description Adds Literature support for LOOP - <cite> and <loop_literature> tags. Replaces BiblioPlus
 * @ingroup Extensions
 * @author Dennis Krohn @krohnden <dennis.krohn@th-luebeck.de>
 */
if ( !defined( 'MEDIAWIKI' ) ) die ( "This file cannot be run standalone.\n" );

use MediaWiki\MediaWikiServices;
use MediaWiki\Session\CsrfTokenSet;

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
				#"required" => array( "author", "editor", "itemTitle", "publisher", "year" ),
				#"optional" => array( "volume", "number", "series", "address", "edition", "month", "note", "isbn", "url", "doi" )
				# TODO fix: author OR editor
				"required" => array( "author", "itemTitle", "publisher", "year" ),
				"optional" => array( "editor", "volume", "number", "series", "address", "edition", "month", "note", "isbn", "url", "doi" )
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
			    "optional" => array( "editor", "volume", "number", "series", "pages", "address", "month", "organization", "publisher", "note", "url", "doi" )
			),
			"manual" => array(
				"required" => array( "address", "itemTitle", "year" ),
			    "optional" => array( "author", "organization", "edition", "month", "note", "url", "doi" )
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
			    "optional" => array( "editor", "volume", "number", "series", "address", "month", "organization", "publisher", "note", "url", "doi" )
			),
			"techreport" => array(
				"required" => array( "author", "itemTitle", "institution", "year" ),
			    "optional" => array( "type", "note", "number", "address", "month", "url", "doi" )
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

		$dbw = wfGetDB( DB_PRIMARY );

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
		SpecialPurgeCache::purge();
        return true;
	}


	// deletes all literature references of a page
    public static function removeFromDatabase ( $key ) {
		$dbr = wfGetDB( DB_PRIMARY );
		$dbr->delete(
			'loop_literature_items',
			'lit_itemkey = "' . $key .'"',
			__METHOD__
		);

		SpecialPurgeCache::purge();
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
					$keyType = ( $itemType == "LOOP1" ) ? $itemType : "";
					$valid = self::checkDataValidity( "itemKey".$keyType, $key );

					if ( $valid ) {
						#$key = str_replace( "", "", $key );

						#$validKey = self::checkDataValidity( "itemKey", $key );
						#if ( $validKey ) {

						$this->itemKey = $key;

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
											$this->errors[] = wfMessage( "loopliterature-error-invalidentry", wfMessage("loopliterature-label-". $field) );
										}
									} elseif ( $required == "required" )  {
										$this->errors[] = wfMessage( "loopliterature-error-missingrequired", wfMessage("loopliterature-label-". $field) );
									}
								}
							}
						} else {
							$this->errors[] = wfMessage( "loopliterature-error-unknowntype", $itemType );
						}
					} else {
						$this->errors[] = wfMessage( "loopliterature-error-invalidkey", $key );
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
		return $this;
	}

	public static function checkDataValidity( $key, $val ) {

		switch ( $key ) {
			case "itemKey":
				$keyLength = strlen($val);
				preg_match( "/([A-Za-z0-9-+.&_]{1,})/", $val, $ret );
				if ( isset( $ret[0] ) ) {
					$validLength = strlen($ret[0]);
					if ( $keyLength == $validLength ) {
						return true;
					} else {
						return false;
					}
				} else {
					return false;
				}

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
	 * @param Array $data = null
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

		if ( strpos( $key, "\n" ) !== false ) { # causes fatal errors
			return false;
		}

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

	# returns whether to show literature in TOC or not
	public static function getShowLiterature() {

		global $wgOut;
		$user = $wgOut->getUser();
		$userOptionsLookup = MediaWikiServices::getInstance()->getUserOptionsLookup();
		$editMode = $userOptionsLookup->getOption( $user, 'LoopEditMode', false, true );
		$literatureItems = self::getAllItems();

		if ( $literatureItems ) {
			return true;
		} elseif ( $editMode ) {
			return "empty";
		} else {
			return false;
		}
	}

	static function renderCite( $input, array $args, Parser $parser, PPFrame $frame ) {
		global $wgLoopLiteratureCiteType;
		$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
		$linkRenderer->setForceArticlePath(true); #required for readable links
		$html = '';
		$loopLiterature = new LoopLiterature();
		$loopLiteratureItem = $loopLiterature->loadLiteratureItem( $input );


		if ( ! $loopLiteratureItem ) {

			$e = new LoopException( wfMessage( 'loopliterature-error-keyunknown', $input )->text() );
			$parser->addTrackingCategory( 'loop-tracking-category-error' );

			return $e;
		} else {
			if ( $wgLoopLiteratureCiteType == "harvard" ) {
				$text = str_replace( "+" , " " , $input );
				$html = $linkRenderer->makeLink(
					new TitleValue( NS_SPECIAL, 'LoopLiterature' ),
					new HtmlArmor( $text ),
					array(
						"title" => str_replace( "+" , " " , $input ),
						"data-target" => "lit-".$input, # target id will be added in hook
						"class" => "literature-link"
					)
				);
				if ( isset( $args["page"] ) ) {
					$html .= ", " . wfMessage("loopliterature-text-pages", 1)->text() . " " . $args["page"];
				} elseif ( isset( $args["pages"] ) ) {
					$html .= ", " . wfMessage("loopliterature-text-pages", 2)->text() . " " . $args["pages"];
				}
			} elseif ( $wgLoopLiteratureCiteType == "vancouver" ) {
				$refId = '';
				$loopStructure = new LoopStructure();
				$loopStructure->loadStructureItems();
				$allReferences = LoopLiteratureReference::getAllItems( $loopStructure );
				$articleId = $parser->getTitle()->mArticleID;
				if ( isset( $args["id"] ) ) {
					$refId = $args["id"];
					if ( isset( $allReferences[$articleId][$refId]["objectnumber"] ) ) {
						$objectNumber = $allReferences[$articleId][$refId]["objectnumber"];
					}

				}
				$articleId = $parser->getTitle()->getArticleID();
				if ( isset ( $allReferences[$articleId] ) && isset( $allReferences[$articleId][$refId] ) ) {
					if ( $refId != $allReferences[$articleId][$refId]["refId"] || $articleId != $allReferences[$articleId][$refId]["articleId"] || $input != $allReferences[$articleId][$refId]["itemKey"] ) {
						$otherTitle = Title::newFromId( $allReferences[$articleId][$refId]["articleId"] );
						$e = new LoopException( wfMessage( 'loopliterature-error-dublicate-id', $refId, $otherTitle->getText(), $allReferences[$articleId][$refId]["itemKey"] )->text() );
						$parser->addTrackingCategory( 'loop-tracking-category-error' );
						$html .= $e;
						$objectNumber = '';
					}
				}
				if ( !empty ( $objectNumber ) ) {
					$text = "<sup>". $objectNumber."</sup>";
				} else {
					$text = str_replace( "+" , " " , $input ); # if there is no object number (as in pages that are not in structure), render normally
				}


				$html .= $linkRenderer->makeLink(
					new TitleValue( NS_SPECIAL, 'LoopLiterature' ),
					new HtmlArmor( $text ),
					array(
						"title" => str_replace( "+" , " " , $input ),
					    "data-target" => "lit-".$input, # target id will be added in hook
					    "class" => "literature-link"
					)
				);
			}
		}

		return $html;
	}

	# renders loop_literature. if only input is set, the output is rendered as xml.
	public static function renderLoopLiterature( $input, array $args = null, Parser $parser = null, PPFrame $frame = null ) {

		global $wgLoopLiteratureCiteType;

		if ( $parser == null ) {
			$parserFactory = MediaWikiServices::getInstance()->getParserFactory();
			$parser = $parserFactory->create();
		}

		$matches = array();
		$parser->extractTagsAndParams ( array( "literature" ), $input, $matches );

		foreach ( $matches as $key ) {
			if ( !empty( $key[1] ) ) {
				$keys[] = rtrim( $key[1], " " );
			}
		}

		$return = ( isset($args) ) ? '<div class="loop-literature mb-1 ml-4">' : '';
		if ( !empty ( $keys ) ) {
			$htmlElements = array();
			$allItems = LoopLiterature::getAllItems();
			foreach ( $keys as $key ) {
				if ( isset ( $allItems[$key] ) ) {
					if ( $allItems[$key]->author ) {
						$orderkey = ucfirst($allItems[$key]->author);

					} elseif ( $allItems[$key]->itemTitle ) {
						$orderkey = ucfirst($allItems[$key]->itemTitle);
					}
					$literatureItem = $allItems[$key];
					$htmlElements[$orderkey] = ( isset($args) ) ? '<p class="literature-entry mb-2" id="'. $key.'">' : '<paragraph>';
					$type = ( isset($args) ) ? 'html' : 'xml';
					if ( $args == null ) {
					    $ref = false;
					} else {
					    $ref = null;
					}
					$htmlElements[$orderkey] .= LoopLiterature::renderLiteratureElement( $literatureItem, $ref, $type, "loop_literature" );
					$htmlElements[$orderkey] .= ( isset($args) ) ? '</p>' : '</paragraph>';

					if ( $wgLoopLiteratureCiteType == "harvard" ) {
						unset($allItems[$key]);
					}
				} elseif ( isset( $parser ) ) {
					$htmlElements[$key] = new LoopException( wfMessage( 'loopliterature-error-keyunknown', $key )->text() );
					$parser->addTrackingCategory( 'loop-tracking-category-error' );

				}
			}
			if ( $wgLoopLiteratureCiteType == 'harvard') {
				ksort( $htmlElements, SORT_STRING );
			}
			foreach ( $htmlElements as $element ) {
				$return .= $element;
			}
		}
		$return .= ( isset($args) ) ? '</div>' : '';
		return $return;
	}

	/**
	 * Custom hook called after stabilization changes of pages in FlaggableWikiPage->updateStableVersion()
	 * @param Title $title
	 * @param Content $content
	 */
	public static function onAfterStabilizeChange ( $title, $content, $userId ) {

	    $latestRevId = $title->getLatestRevID();
	    $wikiPage = MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle($title);
	    $fwp = new FlaggableWikiPage ( $title );

	    if ( isset($fwp) ) {
	        $stableRevId = $fwp->getStable();

	        if ( $latestRevId == $stableRevId || $stableRevId == null ) {
				$contentText = ContentHandler::getContentText( $content );
	            self::handleLoopLiteratureReferences( $wikiPage, $title, $contentText );
	        }
	    }
	    return true;
	}
	/**
	 * Custom hook called after stabilization changes of pages in FlaggableWikiPage->clearStableVersion()
	 * @param Title $title
	 */
	public static function onAfterClearStable( $title ) {
	    $wikiPage = MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle($title);
	    self::handleLoopLiteratureReferences( $wikiPage, $title );
	    return true;
	}

	/**
	 * When deleting a page, remove all Reference entries from DB.
	 * Attached to ArticleDeleteComplete hook.
	 */
	public static function onArticleDeleteComplete( &$article, User &$user, $reason, $id, $content, LogEntry $logEntry, $archivedRevisionCount ) {

	    LoopLiteratureReference::removeAllPageItemsFromDb ( $id );

	    return true;
	}

	/**
	 * Adds literature references to db. Called by onLinksUpdateConstructed and onAfterStabilizeChange (custom Hook)
	 * @param WikiPage $wikiPage
	 * @param Title $title
	 * @param String $contentText
	 */
	public static function handleLoopLiteratureReferences( &$wikiPage, $title, $contentText = null ) {

		$content = $wikiPage->getContent();
		if ($contentText == null) {
			$contentText = ContentHandler::getContentText( $content );
		}

		if ( $title->getNamespace() == NS_MAIN || $title->getNamespace() == NS_GLOSSARY ) {

			$parserFactory = MediaWikiServices::getInstance()->getParserFactory();
			$parser = $parserFactory->create();
			#$loopLiteratureReference = new LoopLiteratureReference();
			$fwp = new FlaggableWikiPage ( $title );
			$stableRevId = $fwp->getStable();
			$latestRevId = $title->getLatestRevID();
			$stable = false;
			if ( $stableRevId == $latestRevId ) {
				$stable = true;
				# on edit, delete all objects of that page from db.
				LoopLiteratureReference::removeAllPageItemsFromDb ( $title->getArticleID() );
			}

			# check if cite is in page content
			$has_reference = false;
			if ( substr_count ( $contentText, 'cite' ) >= 1 ) {
				$has_reference = true;
			}
			if ( $has_reference ) {
				$references = array();
				$object_tags = array ();
				$forbiddenTags = array( 'nowiki', 'code', '!--', 'syntaxhighlight', 'source' ); # don't save ids when in here
				$extractTags = array_merge( array('cite'), $forbiddenTags );
				$parser->extractTagsAndParams( $extractTags, $contentText, $object_tags );
				$newContentText = $contentText;
				$loopStructure = new LoopStructure();
				$loopStructure->loadStructureItems();

				$items = 0;
				foreach ( $object_tags as $object ) {
					if ( ! in_array( strtolower($object[0]), $forbiddenTags ) ) { #exclude loop-tags that are in code or nowiki tags
						$valid = true;
						$tmpLoopLiteratureReference = new LoopLiteratureReference();
						$items++;
						$tmpLoopLiteratureReference->nthItem = $items;

						$tmpLoopLiteratureReference->pageId = $title->getArticleID();
						$tmpLoopLiteratureReference->itemKey = $object[1];

						$tmpLoopLiteratureReference->handleLiteratureOccurrences( $loopStructure, $title );

						if ( isset( $object[2]["id"] ) ) {
							if ( $tmpLoopLiteratureReference->checkDublicates( $object[2]["id"] ) ) {
								$tmpLoopLiteratureReference->refId = $object[2]["id"];
							} else {
								# dublicate id!
								$valid = false;
								$items--;
							}
						}
						if ( $valid && $stable ) {
							$tmpLoopLiteratureReference->addToDatabase();
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

	 // returns all literature items from table
	 public static function getAllItems ( $returnType = null ) {

		$items = array();

        //$dbr = wfGetDB( DB_REPLICA );
		$dbProvider = MediaWikiServices::getInstance()->getConnectionProvider();
		$dbr = $dbProvider->getReplicaDatabase();

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
        return $items;
	}

	/**
	 * @param LoopLiterature $li entry to render
	 * @param Array $ref data about the reference
	 * @param String $type 'html' or 'xml'
	 * @param Mixed $tag 'loop_literature' or false for adding edit links
	 */
	public static function renderLiteratureElement( $li, $ref = null, $type = 'html', $tag = false, $allReferences = null, $linkablePageReferences = null ) {

		global $wgOut, $wgLoopLiteratureCiteType;

		$user = $wgOut->getUser();
		$userOptionsLookup = MediaWikiServices::getInstance()->getUserOptionsLookup();
		$editMode = $userOptionsLookup->getOption( $user, 'LoopEditMode', false, true );
		$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
		$linkRenderer->setForceArticlePath(true); #required for readable links

		$return = '';
		if ($type == "html") {
		    $italic = "<i>";
		    $italicEnd = "</i>";
		} elseif ($type == "xml") { #xml
		    $italic = "";#"<italics>";
		    $italicEnd = "";#"</italics>";
		} else {
		    $italic = "";
		    $italicEnd = "";
		}

		if ( $wgLoopLiteratureCiteType == 'vancouver' && !empty ( $ref["objectnumber"] ) ) {
		    if ( $type == 'html' ) {
		        $return .= "<span class='literature-vancouver-number'>". $ref["objectnumber"].". </span>";
		    } else {
		        $return .= $ref["objectnumber"].". ";
		    }
		}
		# Author/''Title''. (editor). (year). Series. ''Title'' (Type)(Volume). Publisher/Institution/school
		if ( !empty( trim( $li->author ) ) && $li->itemType != "LOOP1" ) {
			if ( $type == 'xml' ) {
				$return .=  $li->author;
			} else {
				$return .= $li->author." ";
			}
		} elseif ( !empty( trim( $li->itemTitle ) ) ) {
			if ( $li->itemType == "LOOP1" ) {
				if ( $type == 'xml' ) {
					$return .=  $li->itemTitle;
				} else {
					$return .= $li->itemTitle." ";
				}
			} else {
				$return .= $italic. $li->itemTitle. $italicEnd . ". ";
			}
		}

		if ( !empty( trim( $li->editor ) ) ) { # short, only (Hrsg.). if different from author, it will be mentioned later
			if ( $li->author == $li->editor ) {
				$return .= "(".wfMessage("loopliterature-text-publisher")->text()."). ";
			}
		}

		if ( !empty( trim( $li->year ) ) ) {
			$monthText = "";
			if ( $li->month ) {
				$monthText = $li->month . " ";
			}
			if ( $li->itemType == "unpublished" ) {
				$return .= "(". $monthText. $li->year.", ".wfMessage("loopliterature-text-unpublished")->text()."). ";
			} else {
				$return .= "(". $monthText. $li->year."). ";
			}
		} elseif ( $li->itemType == "unpublished" ) {
			$return .= "(".wfMessage("loopliterature-text-unpublished")->text()."). ";
		} elseif ( $li->itemType != "LOOP1" ) { #legacy loop 1
			$return .= "(".wfMessage("loopliterature-text-noyear")->text()."). ";
		}

		if ( !empty( trim( $li->chapter ) ) ) {
			$return .= $li->chapter.". ";
		}
		if ( !empty( trim( $li->editor ) ) ) {
			if ( $li->author != $li->editor ) {
				$return .= wfMessage("loopliterature-text-inpublisher", $li->editor)->text() . ", ";
			}
		}

		if ( !empty( trim( $li->author ) ) && !empty( trim( $li->itemTitle ) ) ) {
			if ( $li->itemType == "article" ) {
				$return .= $li->itemTitle;
			} else {
				$return .= $italic. $li->itemTitle. $italicEnd;
			}
			if ( !empty( trim( $li->volume ) ) || !empty( trim( $li->publisher ) ) || !empty( trim( $li->type ) ) || !empty( trim( $li->edition ) ) || !empty( trim( $li->pages ) ) || !empty( trim( $li->howpublished ) ) || !empty( trim( $li->series ) ) || !empty( trim($li->url) ) ) {
				$return .= ". ";
			} else {
				$return .= " ";
			}
		}

		if ( !empty( trim( $li->booktitle ) ) ) {
			$return .= $li->booktitle . ". ";
		}

		if ( !empty( trim( $li->pages ) ) ) {
			if ( ! strpos( $li->pages , ',' ) && ! strpos( $li->pages , '-' )  && ! strpos( $li->pages , ' ' ) ) {
				$plural = 2;
			} else {
				$plural = 1;
			}
			$return .= "(". wfMessage("loopliterature-text-pages", $plural)->text() . " " . $li->pages."). ";
		}


		if ( !empty( trim( $li->journal ) ) ) {
			$return .= $li->journal. ". ";
		}

		if ( !empty( trim( $li->series ) ) ) {
			$return .= "(". $li->series."). ";
		}
		if ( !empty( trim( $li->type ) ) && trim( $li->type ) != $li->itemType ) {
			$return .= "(". $li->type."). ";
		}
		if ( !empty( trim( $li->volume ) ) ) {
			$return .= "(". $li->volume."). ";
		}
		if ( !empty( trim( $li->edition ) ) ) {
			$return .= "(". $li->edition."). ";
		}
		if (!empty( trim(  $li->howpublished ) ) ) {
			$return .= "(". $li->howpublished."). ";
		}
		if ( !empty( trim( $li->number ) ) ) {
			$return .= "(". $li->number."). ";
		}

		if ( !empty( trim( $li->publisher ) ) ) {
			$return .= $li->publisher.". ";
		}

		if ( !empty( trim( $li->institution ) ) ) {
			$return .= $li->institution.". ";
		}
		if ( !empty( trim( $li->school ) ) ) {
			$return .= $li->school.". ";
		}
		if ( !empty( trim( $li->isbn ) ) ) {
			$return .= "ISBN: " . $li->isbn.". ";
		}
		if ( !empty( trim( $li->doi ) ) ) {
			$return .= "DOI: " . $li->doi.". ";
		}
		if (!empty(trim($li->url))) {
			if ($type == 'html') {
				$return .= wfMessage("loopliterature-text-url")->text() . " <a href=" . $li->url . ">" . $li->url . "</a>. ";
			} else {
				$return .= wfMessage("loopliterature-text-url")->text() . " " . $li->url . ". ";
			}
		}
		if ( !empty( trim( $li->address ) ) ) {
			$return .= $li->address.". ";
		}

		if ( $li->note && $editMode && $li->itemType != "LOOP1" && $type == 'html' ) {
			$return .= '<span class="literature-itemkey font-italic text-black-50">'.wfMessage("loopliterature-text-note")->text() . ": " . $li->note.'. </span>';
		}

		if ( $li->itemType == "LOOP1" && $li->note ) {
			$return .= $li->note . " ";
		}

		if ( ( $editMode && $type == 'html' && ! $tag ) ) {
			$return .= '<span class="literature-itemkey font-italic text-black-50" title="'.wfMessage("loopliterature-label-key")->text().'">'. $li->itemKey.' </span>';
		}

		if ( !empty( $linkablePageReferences ) && !empty( $ref ) && ! $tag && $type == 'html' ) {
			$return .= '<span class="dropdown ml-1 cursor-pointer" title="'.wfMessage( "loopliterature-label-deleteentry" )->text().'">';
			$return .= '<span class="dropdown-toggle d-inline accent-color literature-ref-dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></span>';
			$return .= '<span class="dropdown-menu">';

			$linkedTitles = array();
			foreach ( $linkablePageReferences as $articleId => $pageRefs ) {
				foreach ( $pageRefs as $pageRef ) {
					if ( $pageRef[ "itemKey" ] == $li->itemKey ) {
						$title = Title::newFromId( $articleId );
						if ( ! in_array( $title->mArticleID, $linkedTitles ) ) {
							$linkedTitles[] = $title->mArticleID;
							$return .= $linkRenderer->makelink(
								$title,
								new HtmlArmor( $title->getText() ),
								array( 'title' => $title->getText(), "class" => "dropdown-item literature-refs" ),
								array()
							);
						}
					}
				}
			}
			$return .= '</span></span> ';
		}
		if ( ( $editMode && $type == 'html' && ! $tag ) ) {
			$permissionManager = MediaWikiServices::getInstance()->getPermissionManager();
			if ( $permissionManager->userHasRight( $user, 'loop-edit-literature') ) {
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

	public $itemKey;
	public $pageId;
	public $refId;
	public $nthItem;
	public $firstItemGlobal;

	/**
	 * Add literature reference item to the database
	 * @return bool true
	 */
	public function addToDatabase() {
		if ( $this->refId === null ) {
			return false;
		}
		$dbw = wfGetDB( DB_PRIMARY );

        $dbw->insert(
            'loop_literature_references',
            array(
                'llr_itemkey' => $this->itemKey,
                'llr_pageid' => $this->pageId,
                'llr_refid' => $this->refId,
                'llr_nthitem' => $this->nthItem,
                'llr_firstitemglobal' => $this->firstItemGlobal
            ),
            __METHOD__
		);
        $this->id = $dbw->insertId();
        # SpecialPurgeCache::purge();

        return true;

	}

	// deletes all literature references of a page
    public static function removeAllPageItemsFromDb ( $article ) {
		$dbr = wfGetDB( DB_PRIMARY );
		$dbr->delete(
			'loop_literature_references',
			'llr_pageid = ' . $article,
			__METHOD__
		);

        return true;
	}

	public static function getItemData( $refId, $object = false ) {

        $dbr = wfGetDB( DB_REPLICA );
		$res = $dbr->select(
			'loop_literature_references',
			array(
                'llr_itemkey',
                'llr_pageid',
                'llr_refid',
                'llr_nthitem',
                'llr_firstitemglobal'
			),
			array(
				'llr_refid = "' . $refId .'"'
			),
			__METHOD__
		);

		foreach( $res as $row ) {

			if ( $object ) {
				$return = new LoopLiteratureReference();
				$return->pageId = $row->llr_pageid;
				$return->itemKey = $row->llr_itemkey;
				$return->refId = $row->llr_refid;
				$return->itemKey = $row->llr_itemkey;
				$return->nthItem = $row->llr_nthitem;
				$return->firstItemGlobal = boolval($row->llr_firstitemglobal);
			} else {
				$return = array(
					'refId' => $row->llr_refid,
					'articleId' => $row->llr_pageid,
					'itemKey' => $row->llr_itemkey,
					'nthItem' => $row->llr_nthitem,
					'firstItemGlobal' => boolval($row->llr_firstitemglobal),
				);
			}

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

	public function handleLiteratureOccurrences ( LoopStructure $loopStructure, Title $title ) {
		$this->firstItemGlobal = null;
		$itemKey = $this->itemKey;
		$articleId = $this->pageId;
		$structureItems = $loopStructure->structureItems;
		$lsi = LoopStructureItem::newFromIds ( $articleId );
		$glossaryPages = LoopGlossary::getGlossaryPages( "idArray" );

		$pageSequence = array();
		foreach ( $structureItems as $item ) {
			$pageSequence[$item->sequence] = $item->article;
		}
		$structureLength = sizeOf( $structureItems);
		$i = 1;
		foreach ( $glossaryPages as $glossaryPage ) {
			$pageSequence[ $structureLength + $i ] = $glossaryPage;
			$i++;
		}
		if ( array_search ( $this->pageId, $pageSequence ) === false ) {
			$this->firstItemGlobal = false; # this reference is not in structure or glossary.
		}
		if ( $this->firstItemGlobal === null ) {

			$dbr = wfGetDB( DB_REPLICA );
			$res = $dbr->select(
				'loop_literature_references',
				array(
					'llr_itemkey',
					'llr_pageid',
					'llr_refid',
					'llr_nthitem',
					'llr_firstitemglobal'
				),
				array(
					'llr_itemkey = "' . $itemKey .'"'
				),
				__METHOD__
			);
			$objects = array();
			foreach( $res as $row ) {
				# this is not necessarily the first occurrence of that key. will investigate further later

				$objects[$row->llr_pageid][$row->llr_nthitem] = array(
					'refId' => $row->llr_refid,
					'firstItemGlobal' => boolval( $row->llr_firstitemglobal )
				);
			}
			if ( empty ( $objects ) ) {
				if  ( $lsi || $title->getNamespace() == NS_GLOSSARY )  {
					# this is the *very* first occurrence. return true!
					$this->firstItemGlobal = true;
				} else {
					# it's the first occurrence but not in structure or glossary.
					$this->firstItemGlobal = false;
				}
			} else {
				# this key has been referenced already. it must be determined which one occurs first.
				if  ( array_search ( $this->pageId, $pageSequence ) !== false ) {
					$seqVal = array_search ( $this->pageId, $pageSequence );
				} else {
					$this->firstItemGlobal = false; # this reference is not in structure or glossary.
				}

				$keySequence = array( $seqVal => array( $this->nthItem => array( "refId" => null, "firstItemGlobal" => null ) ) );
				foreach ( $objects as $article => $object ) {
					foreach ( $object as $n => $data ) { # n is sequence position
						$tmpLsi = LoopStructureItem::newFromIds ( $article );
						if ( array_search ( $article, $pageSequence ) != false ) { # page is in structure!
							$keySequence[ array_search ( $article, $pageSequence ) ][ $n ] = $data;
						}
					}
				}
				ksort($keySequence);
				$changedObjects = array();
				$firstKey = array_key_first($keySequence);
				foreach ( $keySequence as $seq => $arr ) {
					foreach ( $arr as $nth => $itemdata ) {
						$val = ( $seq == $firstKey ) ? true : false; # if it's the first key in structure
						if ( $itemdata["firstItemGlobal"] != $val )
							if ( isset ( $itemdata["refId"] ) ) {
								$item = self::getItemData( $itemdata["refId"], true );
								$dbw = wfGetDB( DB_PRIMARY );
								$dbw->delete(
									'loop_literature_references',
									'llr_refid = "' . $itemdata["refId"] .'"',
									__METHOD__
								);
								$item->firstItemGlobal = $val;
								if ( $item->firstItemGlobal === null ) {
									$item->firstItemGlobal = false;
								}
								$item->addToDatabase();
							} else {
								$this->firstItemGlobal = $val;
							}
						}
					}
				}
			} else {
				$this->firstItemGlobal = false;
			}
			if ( $this->firstItemGlobal === null ) {
				$this->firstItemGlobal = false;
			}

		return;
	}

    // returns structure literature items with numberings in the table
    public static function getAllItems ( $loopStructure, $vancouver = false ) {

        global $wgLoopLiteratureCiteType;
        $term = $vancouver ? array('llr_firstitemglobal = 1') : array();

		//$dbr = wfGetDB( DB_REPLICA );
		$dbProvider = MediaWikiServices::getInstance()->getConnectionProvider();
		$dbr = $dbProvider->getReplicaDatabase();

        $res = $dbr->select(
            'loop_literature_references',
            array(
                'llr_itemkey',
                'llr_pageid',
                'llr_refid',
                'llr_nthitem',
                'llr_firstitemglobal'
            ),
            $term,
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

            $objects[$row->llr_pageid][$row->llr_refid] = array(
                'refId' => $row->llr_refid,
                'articleId' => $row->llr_pageid,
                'itemKey' => $row->llr_itemkey,
                'nthItem' => $row->llr_nthitem,
                'firstReference' => boolval( $row->llr_firstitemglobal )
            );

		}

		if ( $wgLoopLiteratureCiteType == "vancouver" ) {

			$currentNumber = 0;
			$numberArray = array();

			# count objects in page sequence: first all objects in structure, then in glossary (alphabetical)
			#dd($pageSequence, $numberArray);
			foreach ( $pageSequence as $seq => $articleId ) {
				if ( isset( $objects[$articleId] ) ) {
					#dd($numberArray);
					foreach ( $objects[$articleId] as $obj ) {
						if ( $obj["firstReference"] ) {
							$currentNumber++;
							$numberArray[$obj["itemKey"]] = $currentNumber;
							$objects[$articleId][$obj["refId"]]["objectnumber"] = $currentNumber;
						} elseif ( isset ( $numberArray[$obj["itemKey"]] ) ) {
							$objects[$articleId][$obj["refId"]]["objectnumber"] = $numberArray[$obj["itemKey"]];
						}
					}
				}
			}
			# add numbers to counted objects on pages that are NOT in structure or glossary.
			foreach ( $objects as $page => $refs ) {
					foreach ( $refs as $ref => $data ) {
					if ( !isset ( $data["objectnumber"] ) && array_key_exists( $data["itemKey"], $numberArray ) ) {
						$objects[$page][$ref]["objectnumber"] = $numberArray[$data["itemKey"]];
						# only if a key exists on a structure/glossary page, the number is added to the reference.
					}
				}
			}
		}
        return $objects;
    }

}

class SpecialLoopLiterature extends SpecialPage {

	public function __construct() {
		parent::__construct( 'LoopLiterature' );
	}

	public function execute( $sub ) {
		global $wgLoopLiteratureCiteType;

		$out = $this->getOutput();
		$request = $this->getRequest();
		$user = $this->getUser();
		Loop::handleLoopRequest( $out, $request, $user ); #handle editmode

		$userOptionsLookup = MediaWikiServices::getInstance()->getUserOptionsLookup();
		$editMode = $userOptionsLookup->getOption( $user, 'LoopEditMode', false, true );

		$out->setPageTitle($this->msg('loopliterature'));
		$deleteKey = $request->getText( 'delete' );

		$html = self::renderLoopLiteratureSpecialPage( $deleteKey, $editMode, $user );
		$out->addHTML( $html );

    }
    public static function renderLoopLiteratureSpecialPage( $deleteKey = null, $editMode = false, $user = null ) {

        $html = '';
        $html .= '<h1>';
        $html .= wfMessage( 'loopliterature' )->text();

        if ( $user ) {
            if ( $user->isAllowed('loop-edit-literature') && $editMode ) {
                $linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
                $linkRenderer->setForceArticlePath(true); #required for readable links
                $html .= $linkRenderer->makeLink(
                    new TitleValue( NS_SPECIAL, 'LoopLiteratureEdit' ),
                    new HtmlArmor( '<div class="btn btn-sm mw-ui-button mw-ui-primary mw-ui-progressive float-right mt-1 ml-1">' . wfMessage( "loopliterature-label-addentry" ) . "</div>" ),
                    array()
                    );
                $html .= $linkRenderer->makeLink(
                    new TitleValue( NS_SPECIAL, 'LoopLiteratureImport' ),
                    new HtmlArmor( '<div class="btn btn-sm mw-ui-button mw-ui-primary mw-ui-progressive float-right mt-1 ml-1">' . wfMessage( "loopliterature-label-import" ) . "</div>" ),
                    array()
                    );
            }
        }

        $html .= '</h1>';

        if ( $deleteKey ) {
            LoopLiterature::removeFromDatabase( $deleteKey );
            $html .= '<div class="alert alert-success">' . wfMessage( "loopliterature-alert-deleted", $deleteKey ) . '</div>';
        }
        $html .= '<div class="bibliography ml-4">';
        $html .= self::renderBibliography( "html", $editMode );
        $html .= '</div>';
        return $html;
    }

    public static function renderBibliography( $type = 'html', $editMode = false ) {

        global $wgLoopLiteratureCiteType;
        $loopStructure = new LoopStructure();
        $loopStructure->loadStructureItems();
		$strict = true; #must be activated in vancouver. in harvard it will reduce the shown pages in the upper half to those in structure.
        $allReferences = LoopLiteratureReference::getAllItems( $loopStructure, $strict );
        $allReferences_links = LoopLiteratureReference::getAllItems( $loopStructure );
        $allItems = LoopLiterature::getAllItems();
        $allItemsCopy = $allItems;
		$return = '';
		$elements = array();
        foreach ( $allReferences as $pageId => $pageReferences ) {
            foreach ( $pageReferences as $refId => $referenceData) {
                if ( isset ( $allItems[$referenceData["itemKey"]] ) ) {
                    if ( $wgLoopLiteratureCiteType == 'harvard') {
                        if ( $allItems[$referenceData["itemKey"]]->author ) {
                            $orderkey = ucfirst($allItems[$referenceData["itemKey"]]->author);

                        } elseif ( $allItems[$referenceData["itemKey"]]->itemTitle ) {
                            $orderkey = ucfirst($allItems[$referenceData["itemKey"]]->itemTitle);
                        }
                    } else {
                        $orderkey = $referenceData["objectnumber"];
					}
                    $literatureItem = $allItems[$referenceData["itemKey"]];
                    if ( isset( $allItemsCopy[$referenceData["itemKey"]] ) ) {
                        unset( $allItemsCopy[$referenceData["itemKey"]] );
					}
					if ( $type == "html" ) {
						$tmpElement = '<p class="literature-entry" id="lit-'. $referenceData["itemKey"].'">';
						$tmpElement .= LoopLiterature::renderLiteratureElement( $literatureItem, $referenceData, $type, false, $allReferences, $allReferences_links );
						$tmpElement .= '</p>';
					} else {
						$tmpElement = '<paragraph>';
						$tmpElement .= htmlspecialchars( LoopLiterature::renderLiteratureElement( $literatureItem, $referenceData, "xml" ) );
						$tmpElement .= '</paragraph>';
					}
					$elements[$orderkey][$referenceData["itemKey"]] = $tmpElement;
                }
            }
		}
		ksort( $elements, SORT_STRING );

        foreach ( $elements as $order ) {
			foreach ( $order as $element ) {
				$return .= $element;
			}
		}
        if ( ! empty( $allItemsCopy ) ) {

			if ( !empty( $elements ) ) {

				if ( $type == "html" ) {
					$return .= "<hr class='mr-4'/>";
					$return .= "<p class='font-weight-bold' id='literature-unreferenced'>".wfMessage( "loopliterature-text-notreferenced" )."</p>";
				} else {
					$return .= "<paragraph><bold>".wfMessage( "loopliterature-text-notreferenced" )."</bold></paragraph>";
				}
			}
			$elements = array();
            foreach ( $allItemsCopy as $item ) {
                if ( $item->author ) {
                    $orderkey = ucfirst($item->author);
                } elseif ( $item->itemTitle ) {
					$orderkey = ucfirst($item->itemTitle);
                } else {
					$orderkey = "";
				}

				if ( $type == "html" ) {
					$tmpElement = '<p class="literature-entry">';
					$tmpElement .= LoopLiterature::renderLiteratureElement( $item, array() );
					$tmpElement .= '</p>';
				} else {
					$tmpElement = '<paragraph>';# id="a'. $referenceData["refId"].'">';
					$tmpElement .= htmlspecialchars( LoopLiterature::renderLiteratureElement( $item, array(), 'xml' ));
					$tmpElement .= '</paragraph>';
				}
				$elements[$orderkey][$item->itemKey] = $tmpElement;
			}
            ksort( $elements, SORT_STRING );
			foreach ( $elements as $order ) {
				foreach ( $order as $element ) {
					$return .= $element;
				}
			}
		}
        return $return;
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

		$out = $this->getOutput();
		$request = $this->getRequest();
		$user = $this->getUser();
		$csrfTokenSet = new CsrfTokenSet($request);
		Loop::handleLoopRequest( $out, $request, $user ); #handle editmode
		$permissionManager = MediaWikiServices::getInstance()->getPermissionManager();
		if ( $permissionManager->userHasRight( $user,'loop-edit-literature') ) {

			$html = '';
			$html .= '<h1>';
			$html .= wfMessage( "loopliterature-label-addentry" )->text();
			$html .= '</h1>';

			$html .= wfMessage( "loopliterature-import-hint" );
			$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
			$linkRenderer->setForceArticlePath(true); #required for readable links
			$request = $this->getRequest();
			$editKey = $request->getText( 'edit' );
			http_build_query(array());
			if ( $editKey ) {
				$editLiteratureItem = new LoopLiterature;
				$editLiteratureItem->loadLiteratureItem( $editKey );
				if (!isset ($editLiteratureItem->itemKey)) {
					$editKey = '';
				} else {
					$html .= '<script>var editValues = new Array;';
					$html .= 'editValues = {';
					$itemArray = get_object_vars($editLiteratureItem);
					foreach ( $itemArray as $key => $val ) {
						if ( $key != "literatureTypes" && $key != "errors" && isset($val) ) {
							$val = str_replace('"', "&quot;", $val);
							$html .= ''. $key.': "'. $val.'",';
						}
					}
					$html .= '}</script>';
				}
			} else {
				$html .= '<script>var editValues = new Array;</script>';
			}

			$saltedToken = $csrfTokenSet->getToken( $request->getSessionId()->__tostring() );
			$requestToken = $request->getText( 't' );

			$out->setPageTitle($this->msg('loopliteratureedit')->text());
			$out->addModules( 'loop.special.literature-edit.js' );

			if ( ! empty( $requestToken ) ) {

				if ( $csrfTokenSet->matchToken( $requestToken, $request->getSessionId()->__tostring() ) ) {

					$loopLiterature = new LoopLiterature;
					$loopLiterature->getLiteratureFromRequest( $request );
					if ( empty ( $loopLiterature->errors ) ) {

						$loopLiterature->addToDatabase();

						$html .= '<div class="alert alert-success" role="alert">' . $this->msg("loopliterature-alert-saved", $loopLiterature->itemKey ) . '</div>';

					} else {
						$errorMsgs = $this->msg("loopliterature-error-notsaved")."<br>";
						foreach( $loopLiterature->errors as $error ) {

							$errorMsgs .= $error . '<br>';

						}
						$html .= '<div class="alert alert-danger" role="alert" id="literature-error">' . $errorMsgs . '</div>';

					}
				}
			}
			$existingKeys = LoopLiterature::getAllLiteratureItems( array("itemKey" => true ) );
			$html .= "<script>\n";
			$keyString = '';
			foreach ( $existingKeys as $key ) {
				$keyString .= '"'. $key.'", ';
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


			$html .= '<form class="needs-validation mw-editform mt-3 mb-3" id="literature-entry-form"  enctype="multipart/form-data"  method="post">';
			$html .= '<div class="form-group">';

			$html .= '<div class="form-row">';
			$html .= '<label for="itemType" class="font-weight-bold">'. $this->msg('loopliterature-label-itemType')->text().'</label>';
			$html .= '<select id="itemType" name="itemType" class="form-control form-control-lg">';

			foreach( $typesOfLiterature as $option ) {

			    if ( $editKey && empty( $requestToken ) ) {
					if ( $editLiteratureItem->itemType == "LOOP1" ) {
						$selected = ( $option == "misc" ) ? "selected" : "";
					} else {
						$selected = ( $option == $editLiteratureItem->itemType ) ? "selected" : "";
					}
				} else {
					$selected = ( $option == "book" ) ? "selected" : "";
				}
				$html .= '<option '. $selected.' value="'. $option.'">';
				$html .= $this->msg('loopliterature-entry-'. $option)->text();
				$html .= '</option>';
			}
			$html .= '</select>';
			$html .= '</div>';

			$loopLiterature = new LoopLiterature;
			if ( $editKey && empty( $requestToken ) ) {
				if ( $editLiteratureItem->itemType == "LOOP1" ) {
					$presetData = $loopLiterature->literatureTypes["misc"];
				} else {
					$presetData = $loopLiterature->literatureTypes[$editLiteratureItem->itemType];
				}
			} else {
				$presetData = $loopLiterature->literatureTypes["book"];
			}
			#dd($presetData,  $loopLiterature);
			$requiredObjects  = '<div class="literature-field col-12 mb-3"><label for="itemKey">'. $this->msg('loopliterature-label-key')->text().'</label>';
			$requiredObjects .= '<input class="form-control" id="itemKey" name="itemKey" max-length="255" required pattern="[A-Za-z0-9-+.&_]{1,}"/>';
			$requiredObjects .= '<div class="invalid-feedback" id="keymsg">'. $this->msg("loopliterature-error-keyalreadyexists")->text().'</div>';
			$requiredObjects .= '<div class="invalid-feedback" id="keymsg2">'. $this->msg("loopliterature-error-invalidkey", " ").'</div></div>';
			$requiredObjects .= '<div class="col-12 mb-1' . ( $editKey ? '"' :  ' d-none"' ). '>';
			$requiredObjects .= '<input class="mr-2" type="checkbox" id="overwrite" name="overwrite" value="true"' . ( $editKey ? " required checked" : " disabled" ) . '/>';
			$requiredObjects .= '<label for="overwrite">'. $this->msg("loopliterature-label-overwrite")->text().'</label></div>';

			$optionalObjects = '';
			$otherObjects = '';

			foreach( $fieldData as $field => $arr ) {
				$required = ( in_array( $field, $presetData["required"] ) ) ? "required" : "";
				$optional = ( in_array( $field, $presetData["optional"] ) ) ? true : false;
				$disabled = ( $optional || $required == "required" )  ? "" : "disabled";
				$visibility = ( empty($disabled) )  ? "" : " d-none";

				$fieldHtml = '';
				$fieldHtml .= '<div class="literature-field col-6'. $visibility.'">';
				$fieldHtml .= '<label for="'. $field.'">'. $this->msg('loopliterature-label-'. $field)->text().'</label>';

				$attributes = '';
				foreach ( $arr as $key => $val ) {
					if ( gettype ( $val ) == "string" ) {
						$attributes .= $key . '=\'' . $val . '\' ';
					}
				}
				$type = ( isset( $arr["type"] ) ) ? $arr["type"] : "text";
				$fieldHtml .= '<input  class="form-control" id="'. $field.'" name="'. $field.'" ' . $attributes . ' '. $required.' '. $disabled.'/>';
				$fieldHtml .= '</div>';

				if ( $required == "required" ) {
					$requiredObjects .= $fieldHtml;
				} elseif ( $optional ) {
					$optionalObjects .= $fieldHtml;
				} else {
					$otherObjects .= $fieldHtml;
				}
			}
			$html .= '<div class="form-row border pb-3 mt-3" id="required-row"><h4 class="w-100 pt-2 pl-2">'. $this->msg('loopliterature-label-required')->text().'</h4>'. $requiredObjects.'</div>';
			$html .= '<div class="form-row border pb-3 mt-3" id="optional-row"><h4 class="w-100 pt-2 pl-2">'. $this->msg('loopliterature-label-optional')->text().'</h4>'. $optionalObjects.'</div>';
			$html .= '<div class="form-row" id="disabled-row">'. $otherObjects.'</div>';

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
		global $wgSecretKey;

		$out = $this->getOutput();
		$request = $this->getRequest();
		$csrfTokenSet = new CsrfTokenSet($request);
		$user = $this->getUser();
		Loop::handleLoopRequest( $out, $request, $user ); #handle editmode

        $saltedToken = $csrfTokenSet->getToken( $request->getSessionId()->__tostring() );
		$out->setPageTitle($this->msg('loopliteratureimport'));
        $html = '<h1>';
		$html .= wfMessage( 'loopliteratureimport' )->text();
		$html .= '</h1>';
		$permissionManager = MediaWikiServices::getInstance()->getPermissionManager();
		if ( $permissionManager->userHasRight( $user,'loop-edit-literature') ) {

			$contentToImport = $request->getText( 'loopliterature-import-input' );

			$messages = '';

			if ( !empty ( $contentToImport ) ) {
				$handled = self::handleImportRequest( $contentToImport );

				if ( empty( $handled["errors"] ) ) { #no errors!
					$messageType = "success";
					if ( ! empty( $handled["success"] ) ) { # all success!
						$messages .= wfMessage( "loopliterature-import-success" )->text() . "<br>";
						$messageType = "success";
						foreach ( $handled["success"] as $msg ) {
							$messages .= "- <b>" . $msg . '</b><br>';
						}
					} else { # nothing found! no error, no success
						$messages = wfMessage( "loopliterature-import-no-bibtex" )->text();
						$messageType = "warning";
					}
				} else {
					$messageType = "warning";
					if ( empty( $handled["success"] ) ) {
						$messageType = "danger";
					}
					$messages .= wfMessage( "loopliterature-import-fail" )->text() . "<br>";
					foreach ( $handled["errors"] as $msg ) {
						$messages .= "- " . $msg . '<br>';
					}

				}
				$html .= '<div class="alert alert-'.$messageType.'" role="alert">' . $messages . '</div>';

				#dd($contentToImport, $request);
			}
		    $linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
		    $linkRenderer->setForceArticlePath(true); #required for readable links
			$out = $this->getOutput();

			$html .= wfMessage( "loopliterature-import-description")->text();

	        $html .= Html::openElement(
	                'form',
	                array(
	                    'class' => 'mw-editform mt-3 mb-3',
	                    'id' => 'loopliterature-import-form',
	                    'method' => 'post',
	                    'enctype' => 'multipart/form-data'
	                )
	            )
	            . Html::rawElement(
	                'textarea',
	                array(
	                    'name' => 'loopliterature-import-input',
	                    'id' => 'loopliterature-import-textarea',
	                    'rows' => 15,
	                    'class' => 'd-block mt-3 w-100',
	                ),
	                $contentToImport
	            )
	            . Html::rawElement(
	                'input',
	                array(
	                    'type' => 'hidden',
	                    'name' => 't',
	                    'id' => 'loopliterature-import-token',
	                    'value' => $saltedToken
	                )
	            )
	            . Html::rawElement(
	                'input',
	                array(
	                    'type' => 'submit',
	                    'class' => 'mw-htmlform-submit mw-ui-button mw-ui-primary mw-ui-progressive mt-2',
	                    'id' => 'loopliterature-import-submit',
	                    'value' => $this->msg( 'submit' )->parse()
	                )
	            ) . Html::closeElement(
	                'form'
				);


		} else {
			$html .= '<div class="alert alert-warning" role="alert">' . $this->msg( 'specialpage-no-permission' ) . '</div>';
		}
		$out->addHTML( $html );
    }

	private static function handleImportRequest ( $input ) {
		global $IP;
		$scriptPath = "$IP/extensions/Loop/vendor";

		exec('which pandoc', $output, $pandocExists); # if pandoc is not installed, don't use pandoc services

		require "$scriptPath/renanbr/bibtex-parser/src/Exception/ExceptionInterface.php";
		require "$scriptPath/renanbr/bibtex-parser/src/Exception/ParserException.php";
		require "$scriptPath/renanbr/bibtex-parser/src/Exception/ProcessorException.php";
		require "$scriptPath/renanbr/bibtex-parser/src/Parser.php";
		require "$scriptPath/renanbr/bibtex-parser/src/ListenerInterface.php";
		require "$scriptPath/renanbr/bibtex-parser/src/Listener.php";
		require "$scriptPath/renanbr/bibtex-parser/src/Processor/TagSearchTrait.php";
		require "$scriptPath/renanbr/bibtex-parser/src/Processor/TagCoverageTrait.php";

		$bibtexParser = new RenanBr\BibTexParser\Parser();
		$bibtexListener = new RenanBr\BibTexParser\Listener();
		if ( $pandocExists === 0 ) {
		    require "$scriptPath/ryakad/pandoc-php/src/Pandoc/Pandoc.php";
		    require "$scriptPath/ryakad/pandoc-php/src/Pandoc/PandocException.php";
		    require "$scriptPath/renanbr/bibtex-parser/src/Processor/LatexToUnicodeProcessor.php";
		    $bibtexListener->addProcessor(new RenanBr\BibTexParser\Processor\LatexToUnicodeProcessor());
		}
		$bibtexParser->addListener($bibtexListener);
		$errors = array();
		$success = array();

		try {
			$bibtexParser->parseString($input);
			$entries = $bibtexListener->export();
			foreach ( $entries as $entry ) {
				$tmpLiterature = new LoopLiterature();
				preg_match('/(@\s*)([a-zA-Z]+)/', $entry["_original"], $type);
				if ( isset ( $entry["type"] ) ) {

    				if ( array_key_exists( strtolower( $entry["type"] ), $tmpLiterature->literatureTypes ) ) {

    					$tmpLiterature->itemType = strtolower( $entry["type"] );

    					foreach ( $entry as $key => $val ) {
							$tmpKey = strtolower( $key );
							switch ( $tmpKey ) {
								case "citation-key":
									$tmpLiterature->itemKey = $val;
									break;
								case "type":
									if ( ! array_key_exists( $val, $tmpLiterature->literatureTypes ) ) {
										$tmpLiterature->type = strtolower( $val );
									}
									break;
								case "title":
									$tmpLiterature->itemTitle = $val;
									break;
								default:
									$tmpLiterature->$tmpKey = $val;
									break;
    						}
    					}
    					if ( isset ( $tmpLiterature->itemKey ) ) {
    						$existingKey = new LoopLiterature();
    						$exists = $existingKey->loadLiteratureItem( $tmpLiterature->itemKey );
    						if ( ! $exists ) {
    							$tmpLiterature->addToDatabase();
    							$success[] = $tmpLiterature->itemKey;
    						} else {
    							$errors[] = wfMessage( "loopliterature-error-dublicatekey", $tmpLiterature->itemKey );
    						}

    					}
    				}
				}
			}
			// https://github.com/renanbr/bibtex-parser
		} catch (RenanBr\BibTexParser\Exception\ParserException $exception) {
			// The BibTeX isn't valid
			$errors[] = $exception;
		} catch (RenanBr\BibTexParser\Exception\ProcessorException $exception) {
			// Listener's processors aren't able to handle data found
			$errors[] = $exception;
		} catch (RenanBr\BibTexParser\Exception\ExceptionInterface $exception) {
			// Alternatively, you can use this exception to catch all of them at once
			$errors[] = $exception;
		}
		return array( "success" => $success, "errors" => $errors );
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

class SpecialLoopLiteratureExport extends SpecialPage {

	public function __construct() {
		parent::__construct( 'LoopLiteratureExport' );
	}

	public function execute( $sub ) {
		$out = $this->getOutput();
		$request = $this->getRequest();
		$user = $this->getUser();
		Loop::handleLoopRequest( $out, $request, $user ); #handle editmode

		$out->setPageTitle($this->msg('loopliteratureexport'));
        $html = '<h1>';
		$html .= wfMessage( 'loopliteratureexport' )->text();
		$html .= '</h1>';
		$permissionManager = MediaWikiServices::getInstance()->getPermissionManager();
		if ( $permissionManager->userHasRight( $user,'loop-edit-literature') ) {

		    $linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
		    $linkRenderer->setForceArticlePath(true); #required for readable links
			$out = $this->getOutput();

			$allItems = LoopLiterature::getAllItems();
			$export = '';
			foreach( $allItems as $key => $item ) {
				$type = $item->itemType == "LOOP1" ? "misc" : $item->itemType;

				$keyValidity = LoopLiterature::checkDataValidity( "itemKey", $item->itemKey );
				if ( $keyValidity ) {
					$export .= "@$type{ $item->itemKey,\n";
					$item->title = $item->itemTitle;
					unset($item->id);
					unset($item->literatureTypes);
					unset($item->errors);
					unset($item->itemType);
					unset($item->itemKey);
					unset($item->itemTitle);
					foreach ( $item as $key => $val) {
						if ( !empty($val) ) {
							$export .= "\t" . $key . " = " . '"' . $val . '",' . "\n";
						}
						#dd($key);
					}
					$export .= "}\n";
				} else {
					$errors[] = $item->itemKey;
				}


			}
			if ( !empty ( $errors ) ) {
				$html .= '<div class="alert alert-warning" role="alert">' . $this->msg( "loopliterature-error-invalid-export" )."<br>";
				foreach ( $errors as $error ) {
					$html .= "<b>".$error."</b><br>";
				}
				$html .= '</div>';
			}
			$html .= Html::rawElement(
				'textarea',
				array(
					'name' => 'loopliterature-export',
					'id' => 'loopliterature-export-textarea',
					'rows' => "25",
					'class' => 'd-block mt-3 w-100',
				),
				$export
			);

		} else {
			$html .= '<div class="alert alert-warning" role="alert">' . $this->msg( 'specialpage-no-permission' ) . '</div>';
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

