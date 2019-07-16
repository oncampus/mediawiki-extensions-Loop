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

    public $key;
    public $literatureType;
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
    public $title;
    public $youtubeLink;
    public $type;
    public $url;
    public $volume;
    public $year;

	public function __construct() {

		$this->literatureTypes = array(
			"article" => array(
				"required" => array( "author", "title", "journal", "year" ),
				"optional" => array( "volume", "number", "pages", "month", "note", "url" )
			),
			"book" => array(
				"required" => array( "author", "editor", "title", "publisher", "year" ),
				"optional" => array( "volume", "number", "series", "address", "edition", "month", "note", "isbn", "url" )
			),
			"booklet" => array(
				"required" => array( "title" ),
				"optional" => array( "author", "howpublished", "address", "month", "year", "note", "url" )
			),
			"conference" => array(
				"required" => array( "author", "title", "booktitle", "year" ),
				"optional" => array( "editor", "volume", "number", "series", "pages", "address", "month", "organization", "publisher", "note", "url" )
			),
			"inbook" => array(
				"required" => array( "author", "editor", "title", "chapter", "pages", "publisher", "year" ),
				"optional" => array( "volume", "number", "series", "type", "address", "edition", "month", "note", "url" )
			),
			"incollection" => array(
				"required" => array( "author", "title", "booktitle", "publisher", "year" ),
				"optional" => array( "editor", "volume", "number", "series", "type", "chapter", "pages", "address", "edition", "month", "note", "url" )
			),
			"inproceedings" => array(
				"required" => array( "author", "title", "booktitle", "year" ),
				"optional" => array( "editor", "volume", "number", "series", "pages", "address", "month", "organization", "publisher", "note", "url" )
			),
			"manual" => array(
				"required" => array( "address", "title", "year" ),
				"optional" => array( "author", "organization", "edition", "month", "note", "url" )
			),
			"mastersthesis" => array(
				"required" => array( "author", "title", "school", "year" ),
				"optional" => array( "type", "address", "month", "note", "url" )
			),
			"misc" => array(
				"required" => array(),
				"optional" => array( "author", "title", "howpublished", "month", "year", "note", "url" )
			),
			"phdthesis" => array(
				"required" => array( "author", "title", "school", "year" ),
				"optional" => array( "type", "address", "month", "note", "url" )
			),
			"proceedings" => array(
				"required" => array( "title", "year" ),
				"optional" => array( "editor", "volume", "number", "series", "address", "month", "organization", "publisher", "note", "url" )
			),
			"techreport" => array(
				"required" => array( "author", "title", "institution", "year" ),
				"optional" => array( "type", "note", "number", "address", "month", "url" )
			),
			"unpublished" => array(
				"required" => array( "author", "title", "note" ),
				"optional" => array( "month", "year", "url" )
			)
		);
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
		dd("addtodatabase");

        $this->dbkeys = array(
            'lit_key' => $this->key,
            'lit_literatureType' => $this->literatureType,
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
            'lit_title' => $this->title,
            'lit_type' => $this->type,
            'lit_url' => $this->url,
            'lit_volume' => $this->volume,
            'lit_year' => $this->year
        );
        
        $dbw = wfGetDB( DB_MASTER );

		#$dbw->delete(
		#	'loop_settings',
		#	'lit_structure = 0', # TODO Structure support
		#	__METHOD__
        #);
        
        foreach ( $this->dbkeys as $dbk => $val ) {
            $dbw->insert(
                'loop_literature_items',
                array(
                    $dbk => $val
                )
            );
        }

        return true;
	}

    /**
     * Puts request content into object
     *
     * @param Request $request 
     */

    public function getLiteratureFromRequest ( $request ) {

		$key = $request->getText( 'key' );
		$itemType = $request->getText( 'key' );
		
		if ( ! empty ( $key ) && ! empty ( $itemType ) ) {

			$exists = self::loadLiteratureItem( $key );
			if ( ! $exists ) {
				$this->key = $key;

				
				if ( array_key_exists( $itemType, $this->literatureTypes ) ) {

					dd("hi");

				}




			}
		}

		return true;
	}
	
    /**
     * Loads literature item from DB
     */
    public static function loadLiteratureItem( $key ) {

        $dbr = wfGetDB( DB_REPLICA );
        /*
        $res = $dbr->select(
            'loop_literature_items',
            array(
                'lset_structure',
                'lset_property',
                'lset_value',
            ),
            array(
                 'lset_key = "' . $key .'"' # TODO Structure support
            ),
            __METHOD__
        );

        foreach ( $res as $row ) {
            $data[$row->lset_property] = $row->lset_value;
        }

        global $wgOut, $wgDefaultUserOptions, $wgImprintLink, $wgPrivacyLink, $wgOncampusLink, $wgLoopObjectNumbering, $wgLoopNumberingType, $wgLanguageCode;

        $this->oncampusLink = $wgOncampusLink;
        $this->languageCode = $wgLanguageCode;
        $this->skinStyle = $wgOut->getUser()->getOption( 'LoopSkinStyle', $wgDefaultUserOptions['LoopSkinStyle'], true );
        $this->imprintLink = $wgImprintLink;
        $this->privacyLink = $wgPrivacyLink;
        $this->numberingObjects = $wgLoopObjectNumbering;
        $this->numberingType = $wgLoopNumberingType;
        
        if ( isset( $row->lset_structure ) ) {
            $this->imprintLink = $data['lset_imprintlink'];
            $this->privacyLink = $data['lset_privacylink'];
            $this->oncampusLink = $data['lset_oncampuslink'];
            $this->rightsText = $data['lset_rightstext'];
            $this->rightsType = $data['lset_rightstype'];
            $this->rightsUrl = $data['lset_rightsurl'];
            $this->rightsIcon = $data['lset_rightsicon'];
            $this->customLogo = $data['lset_customlogo'];
            $this->customLogoFileName = $data['lset_customlogofilename'];
            $this->customLogoFilePath = $data['lset_customlogofilepath'];
            $this->languageCode = $data['lset_languagecode'];
            $this->extraFooter = $data['lset_extrafooter'];
            $this->skinStyle = $data['lset_skinstyle'];
            $this->facebookIcon = $data['lset_facebookicon'];
            $this->facebookLink = $data['lset_facebooklink'];
            $this->twitterIcon = $data['lset_twittericon'];
            $this->twitterLink = $data['lset_twitterlink'];
            $this->youtubeIcon = $data['lset_youtubeicon'];
            $this->youtubeLink = $data['lset_youtubelink'];
            $this->githubIcon = $data['lset_githubicon'];
            $this->githubLink = $data['lset_githublink'];
            $this->instagramIcon = $data['lset_instagramicon'];
            $this->instagramLink = $data['lset_instagramlink'];
            $this->numberingObjects = $data['lset_numberingobjects'];
            $this->numberingType = $data['lset_numberingtype'];
        }
        */
        return false;
        
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
		return true;
	}
	static function renderLoopLiterature( $input, array $args, Parser $parser, PPFrame $frame ) {
		return true;
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
		$out->setPageTitle(wfMessage('loopliterature'));
		$html = '';

		if ( $user->isAllowed('loop-edit-literature') ) {
			$html .= $linkRenderer->makeLink(
				new TitleValue( NS_SPECIAL, 'LoopLiteratureEdit' ),
				new HtmlArmor( $this->getSkin()->msg( "loopliteratureedit" ) ),
				array("class"=>"aToc")
				
			);


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

		if ( $user->isAllowed('loop-edit-literature') ) {

			$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
			$out = $this->getOutput();
			$request = $this->getRequest();

			$saltedToken = $user->getEditToken( $wgSecretKey, $request );
			$requestToken = $request->getText( 't' );

			$out->setPageTitle(wfMessage('loopliteratureedit'));
			$out->addModules( 'ext.loop-literature-edit.js' );

			if ( ! empty( $requestToken ) ) {

				if ( $user->matchEditToken( $requestToken, $wgSecretKey, $request ) ) {

					$loopLiterature = new LoopLiterature;
					$loopLiterature->getLiteratureFromRequest( $request );
					$loopLiterature->addToDatabase();
				}

			}

			$html = '';

			$typesOfEntries = array( "article", "book", "booklet", "conference", "inbook", "incollection", "inproceedings", "manual", "mastersthesis", "misc", "phdthesis", "proceedings", "techreport", "unpublished");
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
				'note' => array ( 'max-length' => 255 ), 
				'number' => array ( 'type' => 'number', 'max-length' => 255 ), 
				'organization' => array ( 'max-length' => 255 ), 
				'pages' => array ( 'max-length' => 255 ), 
				'publisher' => array ( 'max-length' => 255 ), 
				'school' => array ( 'max-length' => 255 ), 
				'series' => array ( 'max-length' => 255 ), 
				'title' => array ( 'max-length' => 255 ), 
				'type' => array ( 'max-length' => 255 ), 
				'url' => array ( 'type' => 'url', 'max-length' => 255 ), 
				'volume' => array ( 'max-length' => 255 ), 
				'month' => array ( 'max-length' => 255 ), 
				'year' => array ('type' => 'number', 'length' => 4 )
				);

			$html .= '<form id="literature-entry">';
			$html .= '<div class="form-group">';

			$html .= '<div class="form-row">';
			$html .= '<label for="entryType" class="font-weight-bold">'.wfMessage('loopliterature-label-entrytype')->text().'</label>';
			$html .= '<select id="entryType" class="form-control form-control-lg">';
			$i = 0;
			foreach( $typesOfEntries as $option ) {
				$selected = ( $i == 0 ) ? "selected" : "";
				$i++;
				$html .= '<option '.$selected.' value="'.$option.'">';
				$html .= wfMessage('loopliterature-entry-'.$option)->text();
				$html .= '</option>';
			}
			$html .= '</select>';
			$html .= '</div>';

			$articleData = array(
				"required" => array( "author", "title", "journal", "year" ),
				"optional" => array( "volume", "number", "pages", "month", "note", "url" )
			);

			$requiredObjects = '<div class="literature-field col-12 mb-3"><label for="key">'.wfMessage('loopliterature-label-key')->text().'</label>';
			$requiredObjects .= '<input  class="form-control" id="key" name="key" max-length="255" required/></div>';
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
				$fieldHtml .= '<label for="'.$field.'">'.wfMessage('loopliterature-label-'.$field)->text().'</label>';

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
			$html .= '<div class="form-row border pb-3 mt-3" id="required-row"><h4 class="w-100 pt-2 pl-2">'.wfMessage('loopliterature-label-required')->text().'</h4>'.$requiredObjects.'</div>';
			$html .= '<div class="form-row border pb-3 mt-3" id="optional-row"><h4 class="w-100 pt-2 pl-2">'.wfMessage('loopliterature-label-optional')->text().'</h4>'.$optionalObjects.'</div>';
			$html .= '<div class="form-row" id="disabled-row">'.$otherObjects.'</div>';

			$html .= '</div>';

			$html .= '<input type="hidden" name="t" id="loopliterature-token" value="' . $saltedToken . '"></input>';
			$html .= '<input type="submit" class="mw-htmlform-submit mw-ui-button mw-ui-primary mw-ui-progressive mt-2 d-block" id="loopsettings-submit" value="' . $this->msg( 'submit' ) . '"></input>';
			

			$html .= '</form>';

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
		$out->setPageTitle(wfMessage('loopliteratureimport'));

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

