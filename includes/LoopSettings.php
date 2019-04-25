<?php

class LoopSettings {

    public $id;
    public $timeStamp;
    public $imprintLink;
    public $privacyLink;
    public $oncampusLink;
    public $rightsText;
    public $rightsType;
    public $rightsUrl;
    public $rightsIcon;
    public $customLogo;
    public $customLogoFileName;
    public $customLogoFilePath;
    public $languageCode;
    public $extraFooter;
    public $skinStyle;
    public $facebookIcon;
    public $facebookLink;
    public $twitterIcon;
    public $twitterLink;
    public $youtubeIcon;
    public $youtubeLink;
    public $githubIcon;
    public $githubLink;
    public $instagramIcon;
    public $instagramLink;
    public $numberingFigures;
    public $numberingFormulas;
    public $numberingListings;
    public $numberingMedia;
    public $numberingTables;
    public $numberingTasks;
    public $numberingType;

    /**
     * Add settings to the database
     * @return bool true
     */
    function addToDatabase() {
        
        $dbw = wfGetDB( DB_MASTER );
            
        $dbw->insert(
            'loop_settings',
            array(
                'lset_imprintlink' => $this->imprintLink,
                'lset_privacylink' => $this->privacyLink,
                'lset_oncampuslink' => $this->oncampusLink,
                'lset_rightstext' => $this->rightsText,
                'lset_rightstype' => $this->rightsType,
                'lset_rightsurl' => $this->rightsUrl,
                'lset_rightsicon' => $this->rightsIcon,
                'lset_customlogo' => $this->customLogo,
                'lset_customlogofilename' => $this->customLogoFileName,
                'lset_customlogofilepath' => $this->customLogoFilePath,
                'lset_languagecode' => $this->languageCode,
                'lset_extrafooter' => $this->extraFooter,
                'lset_skinstyle' => $this->skinStyle,
                'lset_facebookicon' => $this->facebookIcon,
                'lset_facebooklink' => $this->facebookLink,
                'lset_twittericon' => $this->twitterIcon,
                'lset_twitterlink' => $this->twitterLink,
                'lset_youtubeicon' => $this->youtubeIcon,
                'lset_youtubelink' => $this->youtubeLink,
                'lset_githubicon' => $this->githubIcon,
                'lset_githublink' => $this->githubLink,
                'lset_instagramicon' => $this->instagramIcon,
                'lset_instagramlink' => $this->instagramLink,
                'lset_numberingfigures' => $this->numberingFigures,
                'lset_numberingformulas' => $this->numberingFormulas,
                'lset_numberinglistings' => $this->numberingListings,
                'lset_numberingmedia' => $this->numberingMedia,
                'lset_numberingtables' => $this->numberingTables,
                'lset_numberingtasks' => $this->numberingTasks,
                'lset_numberingtype' => $this->numberingType
            )
        );
        
        return true;
        
    }

    /**
     * Loads settings from DB
     */
    public function loadSettings() {

        $dbr = wfGetDB( DB_REPLICA );
        $res = $dbr->select(
            'loop_settings',
            array(
                'lset_id',
                'lset_timestamp',
                'lset_imprintlink',
                'lset_privacylink',
                'lset_oncampuslink',
                'lset_rightstext',
                'lset_rightstype',
                'lset_rightsurl',
                'lset_rightsicon',
                'lset_customlogo',
                'lset_customlogofilename',
                'lset_customlogofilepath',
                'lset_languagecode',
                'lset_extrafooter',
                'lset_skinstyle',
                'lset_facebookicon',
                'lset_facebooklink',
                'lset_twittericon',
                'lset_twitterlink',
                'lset_youtubeicon',
                'lset_youtubelink',
                'lset_githubicon',
                'lset_githublink',
                'lset_instagramicon',
                'lset_instagramlink',
                'lset_numberingfigures',
                'lset_numberingformulas',
                'lset_numberinglistings',
                'lset_numberingmedia',
                'lset_numberingtables',
                'lset_numberingtasks',
                'lset_numberingtype'
            ),
            array(),
            __METHOD__,
            array(
                'ORDER BY' => 'lset_id DESC LIMIT 1'
            )
        );

        $row = $res->fetchObject();
        if ( isset($row->lset_id) ) {
            if( $row ) {

                $this->id = $row->lset_id;
                $this->timeStamp = $row->lset_timestamp;
                $this->imprintLink = $row->lset_imprintlink;
                $this->privacyLink = $row->lset_privacylink;
                $this->oncampusLink = $row->lset_oncampuslink;
                $this->rightsText = $row->lset_rightstext;
                $this->rightsType = $row->lset_rightstype;
                $this->rightsUrl = $row->lset_rightsurl;
                $this->rightsIcon = $row->lset_rightsicon;
                $this->customLogo = $row->lset_customlogo;
                $this->customLogoFileName = $row->lset_customlogofilename;
                $this->customLogoFilePath = $row->lset_customlogofilepath;
                $this->languageCode = $row->lset_languagecode;
                $this->extraFooter = $row->lset_extrafooter;
                $this->skinStyle = $row->lset_skinstyle;
                $this->facebookIcon = $row->lset_facebookicon;
                $this->facebookLink = $row->lset_facebooklink;
                $this->twitterIcon = $row->lset_twittericon;
                $this->twitterLink = $row->lset_twitterlink;
                $this->youtubeIcon = $row->lset_youtubeicon;
                $this->youtubeLink = $row->lset_youtubelink;
                $this->githubIcon = $row->lset_githubicon;
                $this->githubLink = $row->lset_githublink;
                $this->instagramIcon = $row->lset_instagramicon;
                $this->instagramLink = $row->lset_instagramlink;
                $this->numberingFigures = $row->lset_numberingfigures;
                $this->numberingFormulas = $row->lset_numberingformulas;
                $this->numberingListings = $row->lset_numberinglistings;
                $this->numberingMedia = $row->lset_numberingmedia;
                $this->numberingTables = $row->lset_numberingtables;
                $this->numberingTasks = $row->lset_numberingtasks;
                $this->numberingType = $row->lset_numberingtype;
                
                return true;
            } else {
                    
                return false;
                    
            }
        } else { // fetch data from global variables
            global $wgOut, $wgDefaultUserOptions, $wgImprintLink, $wgPrivacyLink, $wgOncampusLink, $wgLoopFigureNumbering,
                $wgLoopFormulaNumbering, $wgLoopListingNumbering, $wgLoopMediaNumbering, $wgLoopTableNumbering, 
                $wgLoopTaskNumbering, $wgLoopNumberingType;

            $this->oncampusLink = $wgOncampusLink;
            $this->skinStyle = $wgOut->getUser()->getOption( 'LoopSkinStyle', $wgDefaultUserOptions['LoopSkinStyle'], true );
            $this->imprintLink = $wgImprintLink;
            $this->privacyLink = $wgPrivacyLink;
            $this->numberingFigures = $wgLoopFigureNumbering;
            $this->numberingFormulas = $wgLoopFormulaNumbering;
            $this->numberingListings = $wgLoopListingNumbering;
            $this->numberingMedia = $wgLoopMediaNumbering;
            $this->numberingTables = $wgLoopTableNumbering;
            $this->numberingTasks = $wgLoopTaskNumbering;
            $this->numberingType = $wgLoopNumberingType;
            
            return true;
        }

    }
	
	/**
	 * Parse custom content
	 *
	 * @param String $input Content to parse
	 * @return String
	 */
	function parse( $input ) {
		
		$localParser = new Parser();
		$tmpTitle = Title::newFromText( 'NO TITLE' );
	    $parserOutput = $localParser->parse( $input, $tmpTitle, new ParserOptions() );
	    return $parserOutput->mText;
		
	}

    /**
     * Puts request content into array
     *
     * @param Request $request 
     * @return Array $newLoopSettings
     */

    public function getLoopSettingsFromRequest ( $request ) {
        
        global $wgSocialIcons, $wgSkinStyles, $wgAvailableLicenses, $wgSupportedLoopLanguages, $wgLegalTitleChars;
        $this->errors = array();
        
        $this->rightsText = $request->getText( 'rights-text' ); # no validation required
        
        $socialArray = array(
            'Facebook' => array(),
            'Twitter' => array(),
            'Youtube' => array(),
            'Github' => array( ),
            'Instagram' => array()
        );

        foreach( $wgSocialIcons as $socialIcons => $socialIcon ) {
            
            if ( empty( $request->getText( 'footer-' . $socialIcons . '-icon' ) ) || $request->getText( 'footer-' . $socialIcons . '-icon' ) == $socialIcons ) {
            $socialArray[$socialIcons]['icon'] = $request->getText( 'footer-' . $socialIcons . '-icon' );
                
                if ( ! empty( $request->getText( 'footer-' . $socialIcons . '-icon' ) && filter_var( $request->getText( 'footer-' . $socialIcons . '-url' ), FILTER_VALIDATE_URL ) ) ) {
                $socialArray[$socialIcons]['link'] = $request->getText( 'footer-' . $socialIcons . '-url' );
                } else {
                $socialArray[$socialIcons]['link'] = '';
                }
            } else {
                array_push( $this->errors, wfMessage( 'loopsettings-error' )  . ': ' . $socialIcons );
            }	
        }

        $this->facebookIcon = $socialArray['Facebook']['icon'];
        $this->facebookLink = $socialArray['Facebook']['link'];
        $this->twitterIcon = $socialArray['Twitter']['icon'];
        $this->twitterLink = $socialArray['Twitter']['link'];
        $this->youtubeIcon = $socialArray['Youtube']['icon'];
        $this->youtubeLink = $socialArray['Youtube']['link'];
        $this->githubIcon = $socialArray['Github']['icon'];
        $this->githubLink = $socialArray['Github']['link'];
        $this->instagramIcon = $socialArray['Instagram']['icon'];
        $this->instagramLink = $socialArray['Instagram']['link'];
        
        $regExLoopLink = '/(['.$wgLegalTitleChars.']{1,})/';
        
        if ( filter_var( $request->getText( 'privacy-link' ), FILTER_VALIDATE_URL ) || preg_match( $regExLoopLink, $request->getText( 'privacy-link' ) ) ) {
            $this->privacyLink = $request->getText( 'privacy-link' );
        } else {
            array_push( $this->errors, wfMessage( 'loopsettings-error' )  . ': ' . wfMessage( 'loopsettings-privacy-label' ) );
        }
        
        if ( filter_var( $request->getText( 'imprint-link' ), FILTER_VALIDATE_URL ) || preg_match( $regExLoopLink, $request->getText( 'imprint-link' ) ) ) {
            $this->imprintLink = $request->getText( 'imprint-link' );
        } else {
            array_push( $this->errors, wfMessage( 'loopsettings-error' )  . ': ' . wfMessage( 'loopsettings-imprint-label' ) );
        }
        
        if ( empty ( $request->getText( 'oncampus-link' ) ) || $request->getText( 'oncampus-link' ) == 'showOncampusLink' ) {
            $this->oncampusLink = $request->getText( 'oncampus-link' );
        } else {
            array_push( $this->errors, wfMessage( 'loopsettings-error' )  . ': ' . wfMessage( 'loopsettings-oncampus-label' ) );
        }
        
        if ( empty ( $request->getText( 'rights-type' ) ) || isset ( $wgAvailableLicenses[$request->getText( 'rights-type' )] ) ) {
            $this->rightsType = $request->getText( 'rights-type' );
            $this->rightsIcon = $wgAvailableLicenses[$this->rightsType]['icon'];
            $this->rightsUrl = $wgAvailableLicenses[$this->rightsType]['url'];
        } else {
            array_push( $this->errors, wfMessage( 'loopsettings-error' )  . ': ' . wfMessage( 'loopsettings-use-cc-label' ) );
        }
        
        if ( in_array( $request->getText( 'skin-style' ), $wgSkinStyles ) ) {
            $this->skinStyle = $request->getText( 'skin-style' );
        } else {
            array_push( $this->errors, wfMessage( 'loopsettings-error' )  . ': ' . wfMessage( 'loopsettings-skin-style-label' ) );
        }
        
        if ( in_array( $request->getText( 'language-select' ), $wgSupportedLoopLanguages ) ) {
            $this->languageCode = $request->getText( 'language-select' );
        } else {
            array_push( $this->errors, wfMessage( 'loopsettings-error' )  . ': ' . wfMessage( 'loopsettings-language-label' ) );
        }
        
        if ( empty ( $request->getText( 'extra-footer-active' ) ) || $request->getText( 'extra-footer-active' ) == 'useExtraFooter' ) {
                $this->extraFooter = $request->getText( 'extra-footer-active' );
        } else {
            array_push( $this->errors, wfMessage( 'loopsettings-error' )  . ': ' . wfMessage( 'loopsettings-extra-footer-label' ) );
        }
        
        if ( empty( $request->getText( 'logo-use-custom' ) ) ) {
            $this->customLogo = "";
            $this->customLogoFileName = "";
            $this->customLogoFilePath = "";
        } else if ( $request->getText( 'logo-use-custom' ) == 'useCustomLogo' ) {
            $this->customLogo = 'useCustomLogo';
            $this->customLogoFileName = $request->getText( 'custom-logo-filename' );
            $tmpParsedResult = $this->parse( '{{filepath:' . $request->getText( 'custom-logo-filename' ) . '}}' );
            preg_match( '/href="(.*)"/', $tmpParsedResult, $tmpOutputArray);
            if ( isset ( $tmpOutputArray[1] ) ) {
                $this->customLogoFilePath = $tmpOutputArray[1];
            } else {
                $this->customLogo = "";
                $this->customLogoFileName = "";
                $this->customLogoFilePath = "";
                array_push( $this->errors, wfMessage( 'loopsettings-error-customlogo-notfound' ) );
            }
        } else {
            $this->customLogo = "";
            $this->customLogoFileName = "";
            $this->customLogoFilePath = "";
            array_push( $this->errors, wfMessage( 'loopsettings-error' )  . ': ' . wfMessage( 'loopsettings-customlogo-label' ) );
        }
        
        # Numbering figures
        if ( $request->getText( 'numbering-figures' ) == 'numberingFigures' ) { 
            $this->numberingFigures = true;
            LoopObject::removeStructureCache();
        } elseif ( empty ( $request->getText( 'numbering-figures' ) ) ) {
            $this->numberingFigures = "false";
            LoopObject::removeStructureCache();
        } else {
            array_push( $this->errors, wfMessage( 'loopsettings-error' )  . ': ' . wfMessage( 'loopsettings-numbering-figures-label' ) );
        }

        # Numbering listings
        if ( $request->getText( 'numbering-listings' ) == 'numberingListings' ) { 
            $this->numberingListings = true;
            LoopObject::removeStructureCache();
        } elseif ( empty ( $request->getText( 'numbering-listings' ) ) ) {
            $this->numberingListings = "false";
            LoopObject::removeStructureCache();
        } else {
            array_push( $this->errors, wfMessage( 'loopsettings-error' )  . ': ' . wfMessage( 'loopsettings-numbering-listings-label' ) );
        }
                
        # Numbering media
        if ( $request->getText( 'numbering-media' ) == 'numberingMedia' ) { 
            $this->numberingMedia = true;
            LoopObject::removeStructureCache();
        } elseif ( empty ( $request->getText( 'numbering-media' ) ) ) {
            $this->numberingMedia = "false";
            LoopObject::removeStructureCache();
        } else {
            array_push( $this->errors, wfMessage( 'loopsettings-error' )  . ': ' . wfMessage( 'loopsettings-numbering-media-label' ) );
        }
        
        # Numbering tables
        if ( $request->getText( 'numbering-tables' ) == 'numberingTables' ) { 
            $this->numberingTables = true;
            LoopObject::removeStructureCache();
        } elseif ( empty ( $request->getText( 'numbering-tables' ) ) ) {
            $this->numberingTables = "false";
            LoopObject::removeStructureCache();
        } else {
            array_push( $this->errors, wfMessage( 'loopsettings-error' )  . ': ' . wfMessage( 'loopsettings-numbering-tables-label' ) );
        }
        
        # Numbering formulas
        if ( $request->getText( 'numbering-formulas' ) == 'numberingFormulas' ) { 
            $this->numberingFormulas = true;
            LoopObject::removeStructureCache();
        } elseif ( empty ( $request->getText( 'numbering-formulas' ) ) ) {
            $this->numberingFormulas = "false";
            LoopObject::removeStructureCache();
        } else {
            array_push( $this->errors, wfMessage( 'loopsettings-error' )  . ': ' . wfMessage( 'loopsettings-numbering-formulas-label' ) );
        }
        
        # Numbering tasks
        if ( $request->getText( 'numbering-tasks' ) == 'numberingTasks' ) { 
            $this->numberingTasks = true;
            LoopObject::removeStructureCache();
        } elseif ( empty ( $request->getText( 'numbering-tasks' ) ) ) {
            $this->numberingTasks = "false";
            LoopObject::removeStructureCache();
        } else {
            array_push( $this->errors, wfMessage( 'loopsettings-error' )  . ': ' . wfMessage( 'loopsettings-numbering-tasks-label' ) );
        }

        # Numbering type
        if ( ! empty ( $request->getText( 'numbering-type' ) ) ) { 
            if ( $request->getText( 'numbering-type' ) == "ongoing" ) { 
                $this->numberingType = "ongoing";
                LoopObject::removeStructureCache();
            } elseif ( $request->getText( 'numbering-type' ) == "chapter" ) { 
                $this->numberingType = "chapter";
                LoopObject::removeStructureCache();
            } else {
                array_push( $this->errors, wfMessage( 'loopsettings-error' )  . ': ' . wfMessage( 'loopsettings-numbering-type-label' ) );
            }
        } else {
            array_push( $this->errors, wfMessage( 'loopsettings-error' )  . ': ' . wfMessage( 'loopsettings-numbering-type-label' ) );
        }

        $this->addToDatabase();
        return true;

    }
}