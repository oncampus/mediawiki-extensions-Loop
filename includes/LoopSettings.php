<?php
/**
 * @description Special page for LOOP settings
 * @ingroup Extensions
 * @author Dennis Krohn @krohnden <dennis.krohn@th-luebeck.de>
 */
if ( !defined( 'MEDIAWIKI' ) ) {
    die( "This file cannot be run standalone.\n" );
}

use MediaWiki\MediaWikiServices;

class LoopSettings {

    public $id;
    public $timeStamp;
    public $imprintLink; #wgLoopImprintLink
    public $privacyLink; #wgLoopPrivacyLink
    public $rightsText; #wgRightsText
    public $rightsType; #wgLoopRightsType
    public $rightsUrl; #wgRightsUrl
    public $rightsIcon; #wgRightsIcon
    public $customLogo; #wgLoopCustomLogo["useCustomLogo"]
    public $customLogoFileName; #wgLoopCustomLogo["customFileName"]
    public $customLogoFilePath; #wgLoopCustomLogo["customFilePath"]
    public $extraFooter; #wgLoopExtraFooter
    public $skinStyle; #wgDefaultUserOptions['LoopSkinStyle']
    public $facebookIcon; #wgLoopSocialIcons['Facebook']['icon']
    public $facebookLink; #wgLoopSocialIcons['Facebook']['link']
    public $twitterIcon; #wgLoopSocialIcons['Twitter']['icon']
    public $twitterLink; #wgLoopSocialIcons['Twitter']['link']
    public $youtubeIcon; #wgLoopSocialIcons['Youtube']['icon']
    public $youtubeLink; #wgLoopSocialIcons['Youtube']['link']
    public $githubIcon; #wgLoopSocialIcons['Github']['icon']
    public $githubLink; #wgLoopSocialIcons['Github']['link']
    public $instagramIcon; #wgLoopSocialIcons['Instagram']['icon']
    public $instagramLink; #wgLoopSocialIcons['Instagram']['link']
    public $numberingObjects; #wgLoopObjectNumbering
    public $numberingType; #wgLoopNumberingType
    public $citationStyle; #wgLoopLiteratureCiteType
    public $extraSidebar; #wgLoopExtraSidebar
    public $exportT2s; #GUI only
    public $exportAudio; #GUI only
    public $exportPdf; #GUI only
    public $exportEpub; #GUI only
    public $exportScorm; #GUI only
    public $exportXml; #GUI only
    public $exportHtml; #GUI only
    public $captchaEdit; #wgCaptchaTriggers["edit"]
    public $captchaCreate; #wgCaptchaTriggers["create"]
    public $captchaAddurl; #wgCaptchaTriggers["addurl"]
    public $captchaCreateAccount; #wgCaptchaTriggers["createaccount"]
    public $captchaBadlogin; #wgCaptchaTriggers["badlogin"]

    /**
     * Add settings to the database
     * @return bool true
     */
    function addToDatabase() {
        $this->dbkeys = array(
            'lset_imprintlink' => $this->imprintLink,
            'lset_privacylink' => $this->privacyLink,
            'lset_rightstext' => $this->rightsText,
            'lset_rightstype' => $this->rightsType,
            'lset_rightsurl' => $this->rightsUrl,
            'lset_rightsicon' => $this->rightsIcon,
            'lset_customlogo' => $this->customLogo,
            'lset_customlogofilename' => $this->customLogoFileName,
            'lset_customlogofilepath' => $this->customLogoFilePath,
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
            'lset_numberingobjects' => $this->numberingObjects,
            'lset_numberingtype' => $this->numberingType,
            'lset_citationstyle' => $this->citationStyle,
            'lset_extrasidebar' => $this->extraSidebar,
            'lset_exportt2s' => $this->exportT2s,
            'lset_exportaudio' => $this->exportAudio,
            'lset_exportpdf' => $this->exportPdf,
            'lset_exportxml' => $this->exportXml,
            'lset_exporthtml' => $this->exportHtml,
            'lset_exportepub' => $this->exportEpub,
            'lset_exportscorm' => $this->exportScorm,
            'lset_captchaedit' => $this->captchaEdit,
            'lset_captchacreate' => $this->captchaCreate,
            'lset_captchaddurl' => $this->captchaAddurl,
            'lset_captchacreateaccount' => $this->captchaCreateAccount,
            'lset_captchabadlogin' => $this->captchaBadlogin
        );
        
        $dbw = wfGetDB( DB_MASTER );

		$dbw->delete(
			'loop_settings',
			'lset_structure = 0', # TODO Structure support
			__METHOD__
        );
        
        foreach ( $this->dbkeys as $dbk => $val ) {
            $dbw->insert(
                'loop_settings',
                array(
                    'lset_structure' => 0, # TODO Structure support
                    'lset_property' => $dbk,
                    'lset_value' => $val,
                )
            );
        }

        return true;
        
    }

    /**
     * Loads settings from DB
     */
    public function loadSettings() {
        error_log("loadsettings");
        $dbr = wfGetDB( DB_REPLICA );
        
        $res = $dbr->select(
            'loop_settings',
            array(
                'lset_structure',
                'lset_property',
                'lset_value',
            ),
            array(
                 'lset_structure = "' . 0 .'"' # TODO Structure support
            ),
            __METHOD__
        );

        foreach ( $res as $row ) {
            $data[$row->lset_property] = $row->lset_value;
        }

        global $wgLoopImprintLink, $wgLoopPrivacyLink, $wgRightsText, $wgLoopRightsType, $wgRightsUrl, $wgRightsIcon, 
        $wgLoopCustomLogo, $wgLoopExtraFooter, $wgDefaultUserOptions, $wgLoopSocialIcons, $wgLoopObjectNumbering, 
        $wgLoopNumberingType, $wgLoopLiteratureCiteType, $wgLoopExtraSidebar, $wgCaptchaTriggers;
        
        # take values from presets in extension.json and LocalSettings if there is no DB entry
        $this->imprintLink = $wgLoopImprintLink;
        $this->privacyLink = $wgLoopPrivacyLink;
        $this->rightsText = $wgRightsText;
        $this->rightsType = $wgLoopRightsType;
        $this->rightsUrl = $wgRightsUrl;
        $this->rightsIcon = $wgRightsIcon;
        $this->customLogo = $wgLoopCustomLogo["useCustomLogo"];
        $this->customLogoFileName = $wgLoopCustomLogo["customFileName"];
        $this->customLogoFilePath = $wgLoopCustomLogo["customFilePath"];
        $this->extraFooter = $wgLoopExtraFooter;
        $this->skinStyle = $wgDefaultUserOptions['LoopSkinStyle'];
        $this->facebookIcon = $wgLoopSocialIcons['Facebook']['icon'];
        $this->facebookLink = $wgLoopSocialIcons['Facebook']['link'];
        $this->twitterIcon = $wgLoopSocialIcons['Twitter']['icon'];
        $this->twitterLink = $wgLoopSocialIcons['Twitter']['link'];
        $this->youtubeIcon = $wgLoopSocialIcons['Youtube']['icon'];
        $this->youtubeLink = $wgLoopSocialIcons['Youtube']['link'];
        $this->githubIcon = $wgLoopSocialIcons['Github']['icon'];
        $this->githubLink = $wgLoopSocialIcons['Github']['link'];
        $this->instagramIcon = $wgLoopSocialIcons['Instagram']['icon'];
        $this->instagramLink = $wgLoopSocialIcons['Instagram']['link'];
        $this->numberingObjects = $wgLoopObjectNumbering;
        $this->numberingType = $wgLoopNumberingType;
        $this->citationStyle = $wgLoopLiteratureCiteType;
        $this->extraSidebar = $wgLoopExtraSidebar;
        $this->exportT2s = true; # allow all export options that are given
        $this->exportAudio = true;
        $this->exportPdf = true;
        $this->exportEpub = true;
        $this->exportScorm = true;
        $this->exportXml = true;
        $this->exportHtml = true;
        $this->captchaEdit = $wgCaptchaTriggers["edit"];
        $this->captchaCreate = $wgCaptchaTriggers["create"];
        $this->captchaAddurl = $wgCaptchaTriggers["addurl"];
        $this->captchaCreateAccount = $wgCaptchaTriggers["createaccount"];
        $this->captchaBadlogin = $wgCaptchaTriggers["badlogin"];
        
        if ( isset($row->lset_structure) ) {
            $this->imprintLink = $data['lset_imprintlink'];
            $this->privacyLink = $data['lset_privacylink'];
            $this->rightsText = $data['lset_rightstext'];
            $this->rightsType = $data['lset_rightstype'];
            $this->rightsUrl = $data['lset_rightsurl'];
            $this->rightsIcon = $data['lset_rightsicon'];
            $this->customLogo = boolval($data['lset_customlogo']);#
            $this->customLogoFileName = $data['lset_customlogofilename'];#
            $this->customLogoFilePath = $data['lset_customlogofilepath'];#
            $this->extraFooter = boolval($data['lset_extrafooter']);#
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
            $this->numberingObjects = boolval($data['lset_numberingobjects']);
            $this->numberingType = $data['lset_numberingtype'];
            $this->citationStyle = $data['lset_citationstyle'];
            $this->extraSidebar = boolval($data['lset_extrasidebar']);
            $this->exportT2s = boolval($data['lset_exportt2s']);
            $this->exportAudio = boolval($data['lset_exportaudio']);
            $this->exportPdf = boolval($data['lset_exportpdf']);
            $this->exportEpub = boolval($data['lset_exportepub']);
            $this->exportScorm = boolval($data['lset_exportscorm']);
            $this->exportXml = boolval($data['lset_exportxml']);
            $this->exportHtml = boolval($data['lset_exporthtml']);
            $this->captchaEdit = boolval($data['lset_captchaedit']);
            $this->captchaCreate = boolval($data['lset_captchacreate']);
            $this->captchaAddurl = boolval($data['lset_captchaddurl']);
            $this->captchaCreateAccount = boolval($data['lset_captchacreateaccount']);
            $this->captchaBadlogin = boolval($data['lset_captchabadlogin']);
        }
        
        return true;
        
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
     * @param User $user 
     * @return Bool
     */

    public function getLoopSettingsFromRequest ( $request, $user ) {
        
        global $wgLoopSocialIcons, $wgLoopSkinStyles, $wgAvailableLicenses, $wgLegalTitleChars;
        $this->errors = array();
        $this->rightsText = $request->getText( 'rights-text' ); # no validation required
        
        $socialArray = array(
            'Facebook' => array(),
            'Twitter' => array(),
            'Youtube' => array(),
            'Github' => array( ),
            'Instagram' => array()
        );

        foreach( $wgLoopSocialIcons as $socialIcons => $socialIcon ) {
            
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
        
        if ( empty ( $request->getText( 'rights-type' ) ) || isset ( $wgAvailableLicenses[$request->getText( 'rights-type' )] ) ) {
            $this->rightsType = $request->getText( 'rights-type' );
            $this->rightsIcon = $wgAvailableLicenses[$this->rightsType]['icon'];
            $this->rightsUrl = $wgAvailableLicenses[$this->rightsType]['url'];
        } else {
            array_push( $this->errors, wfMessage( 'loopsettings-error' )  . ': ' . wfMessage( 'loopsettings-use-cc-label' ) );
        }
        
        if ( in_array( $request->getText( 'skin-style' ), $wgLoopSkinStyles ) ) {
            $this->skinStyle = $request->getText( 'skin-style' );
            $user->setOption( 'LoopSkinStyle', $this->skinStyle );
			$user->saveSettings();
        } else {
            array_push( $this->errors, wfMessage( 'loopsettings-error' )  . ': ' . wfMessage( 'loopsettings-skin-style-label' ) );
        }
        
        if ( $request->getText( 'extra-footer-active' ) == 'on' ) {
                $this->extraFooter = true;
        } elseif ( empty( $request->getText( 'extra-footer-active' ) ) ) {
            $this->extraFooter = false;
        } else {
            array_push( $this->errors, wfMessage( 'loopsettings-error' )  . ': ' . wfMessage( 'loopsettings-extra-footer-label' ) );
        }
        
        if ( empty( $request->getText( 'logo-use-custom' ) ) ) {
            $this->customLogo = false;
            $this->customLogoFileName = "";
            $this->customLogoFilePath = "";
        } elseif ( $request->getText( 'logo-use-custom' ) == 'on' ) {
            $this->customLogo = true;
            $this->customLogoFileName = $request->getText( 'custom-logo-filename' );
            $tmpParsedResult = $this->parse( '{{filepath:' . $request->getText( 'custom-logo-filename' ) . '}}' );
            preg_match( '/href="(.*)"/', $tmpParsedResult, $tmpOutputArray);
            if ( isset ( $tmpOutputArray[1] ) ) {
                $this->customLogoFilePath = $tmpOutputArray[1];
            } else {
                $this->customLogo = false;
                $this->customLogoFileName = "";
                $this->customLogoFilePath = "";
                array_push( $this->errors, wfMessage( 'loopsettings-error-customlogo-notfound' ) );
            }
        } else {
            $this->customLogo = false;
            $this->customLogoFileName = "";
            $this->customLogoFilePath = "";
            array_push( $this->errors, wfMessage( 'loopsettings-error' )  . ': ' . wfMessage( 'loopsettings-customlogo-label' ) );
        }
        
        # Numbering objects
        if ( $request->getText( 'numbering-objects' ) == 'numberingObjects' ) { 
            $this->numberingObjects = true;
        } elseif ( empty ( $request->getText( 'numbering-objects' ) ) ) {
            $this->numberingObjects = false;
        } else {
            $this->numberingObjects = false;
            array_push( $this->errors, wfMessage( 'loopsettings-error' )  . ': ' . wfMessage( 'loopsettings-numbering-objects-label' ) );
        }

        # Numbering type
        if ( ! empty ( $request->getText( 'numbering-type' ) ) ) { 
            if ( $request->getText( 'numbering-type' ) == "ongoing" ) { 
                $this->numberingType = "ongoing";
            } elseif ( $request->getText( 'numbering-type' ) == "chapter" ) { 
                $this->numberingType = "chapter";
            } else {
                array_push( $this->errors, wfMessage( 'loopsettings-error' )  . ': ' . wfMessage( 'loopsettings-numbering-type-label' ) );
            }
        } else {
            array_push( $this->errors, wfMessage( 'loopsettings-error' )  . ': ' . wfMessage( 'loopsettings-numbering-type-label' ) );
        }

        # citation style
        if ( ! empty ( $request->getText( 'citation-style' ) ) ) { 
            if ( $request->getText( 'citation-style' ) == "vancouver" ) { 
                $this->citationStyle = "vancouver";
            } else { 
                $this->citationStyle = "harvard";
            } 
        } else {
            array_push( $this->errors, wfMessage( 'loopsettings-error' )  . ': ' . wfMessage( 'loopsettings-citation-style-label' ) );
        }
        
        if ( $request->getText( 'extra-sidebar-active' ) == 'on' ) {
            $this->extraSidebar = true;
        }  elseif ( empty( $request->getText( 'extra-sidebar-active' ) ) ) {
            $this->extraSidebar = false;
        } else {
            array_push( $this->errors, wfMessage( 'loopsettings-error' )  . ': ' . wfMessage( 'loopsettings-extra-sidebar-label' ) );
        }

        
        # Export t2s
        if ( $request->getText( 'export-t2s' ) == 'on' ) { 
            $this->exportT2s = true;
        } elseif ( empty ( $request->getText( 'export-t2s' ) ) ) {
            $this->exportT2s = false;
        } else {
            array_push( $this->errors, wfMessage( 'loopsettings-error' )  . ': ' . wfMessage( 'loopsettings-export-t2s-label' ) );
        }
        # Export audio
        if ( $request->getText( 'export-audio' ) == 'on' ) { 
            $this->exportAudio = true;
        } elseif ( empty ( $request->getText( 'export-audio' ) ) ) {
            $this->exportAudio = false;
        } else {
            array_push( $this->errors, wfMessage( 'loopsettings-error' )  . ': ' . wfMessage( 'loopsettings-export-audio-label' ) );
        }
        # Export pdf
        if ( $request->getText( 'export-pdf' ) == 'on' ) { 
            $this->exportPdf = true;
        } elseif ( empty ( $request->getText( 'export-pdf' ) ) ) {
            $this->exportPdf = false;
        } else {
            array_push( $this->errors, wfMessage( 'loopsettings-error' )  . ': ' . wfMessage( 'loopsettings-export-pdf-label' ) );
        }
        # Export epub
        if ( $request->getText( 'export-epub' ) == 'on' ) { 
            $this->exportEpub = true;
        } elseif ( empty ( $request->getText( 'export-epub' ) ) ) {
            $this->exportEpub = false;
        } else {
            array_push( $this->errors, wfMessage( 'loopsettings-error' )  . ': ' . wfMessage( 'loopsettings-export-epub-label' ) );
        }
        # Export scorm
        if ( $request->getText( 'export-scorm' ) == 'on' ) { 
            $this->exportScorm = true;
        } elseif ( empty ( $request->getText( 'export-scorm' ) ) ) {
            $this->exportScorm = false;
        } else {
            array_push( $this->errors, wfMessage( 'loopsettings-error' )  . ': ' . wfMessage( 'loopsettings-export-scorm-label' ) );
        }
        # Export xml
        if ( $request->getText( 'export-xml' ) == 'on' ) { 
            $this->exportXml = true;
        } elseif ( empty ( $request->getText( 'export-xml' ) ) ) {
            $this->exportXml = false;
        } else {
            array_push( $this->errors, wfMessage( 'loopsettings-error' )  . ': ' . wfMessage( 'loopsettings-export-xml-label' ) );
        }
        # Export html
        if ( $request->getText( 'export-html' ) == 'on' ) { 
            $this->exportHtml = true;
        } elseif ( empty ( $request->getText( 'export-html' ) ) ) {
            $this->exportHtml = false;
        } else {
            array_push( $this->errors, wfMessage( 'loopsettings-error' )  . ': ' . wfMessage( 'loopsettings-export-html-label' ) );
        }
        
        # Captcha edit
        if ( $request->getText( 'captcha-edit' ) == 'on' ) { 
            $this->captchaEdit = true;
        } elseif ( empty ( $request->getText( 'captcha-ecit' ) ) ) {
            $this->captchaEdit = false;
        } else {
            array_push( $this->errors, wfMessage( 'loopsettings-error' )  . ': ' . wfMessage( 'loopsettings-captcha-edit-label' ) );
        }
        # Captcha create
        if ( $request->getText( 'captcha-create' ) == 'on' ) { 
            $this->captchaCreate = true;
        } elseif ( empty ( $request->getText( 'captcha-create' ) ) ) {
            $this->captchaCreate = false;
        } else {
            array_push( $this->errors, wfMessage( 'loopsettings-error' )  . ': ' . wfMessage( 'loopsettings-captcha-create-label' ) );
        }
        # Captcha createaccount
        if ( $request->getText( 'captcha-createaccount' ) == 'on' ) { 
            $this->captchaAddurl = true;
        } elseif ( empty ( $request->getText( 'captcha-createaccount' ) ) ) {
            $this->captchaAddurl = false;
        } else {
            array_push( $this->errors, wfMessage( 'loopsettings-error' )  . ': ' . wfMessage( 'loopsettings-captcha-createaccount-label' ) );
        }
        # Captcha addurl
        if ( $request->getText( 'captcha-addurl' ) == 'on' ) { 
            $this->captchaCreateAccount = true;
        } elseif ( empty ( $request->getText( 'captcha-addurl' ) ) ) {
            $this->captchaCreateAccount = false;
        } else {
            array_push( $this->errors, wfMessage( 'loopsettings-error' )  . ': ' . wfMessage( 'loopsettings-captcha-addurl-label' ) );
        }
        # Captcha badlogin
        if ( $request->getText( 'captcha-badlogin' ) == 'on' ) { 
            $this->captchaBadlogin = true;
        } elseif ( empty ( $request->getText( 'captcha-badlogin' ) ) ) {
            $this->captchaBadlogin = false;
        } else {
            array_push( $this->errors, wfMessage( 'loopsettings-error' )  . ': ' . wfMessage( 'loopsettings-captcha-badlogin-label' ) );
        }
        $this->addToDatabase();
        SpecialPurgeCache::purge();
        return true;

    }
}


class SpecialLoopSettings extends SpecialPage {
	
	function __construct() {
		parent::__construct( 'LoopSettings' );
	}
	function execute( $sub ) {
		
		$out = $this->getOutput();
		$request = $this->getRequest();
		$user = $this->getUser();
		Loop::handleLoopRequest( $out, $request, $user ); #handle editmode

	    $linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
	    $linkRenderer->setForceArticlePath(true);
		$html = '';
		
		if ( $user->isAllowed( 'loop-settings-edit' ) ) {
			
			global $IP, $wgSecretKey, $wgLoopSocialIcons, $wgAvailableLicenses, $wgSpecialPages,
			$wgLoopSkinStyles, $wgLoopEditableSkinStyles, $wgDefaultUserOptions;
				
			$this->setHeaders();
			
 			$out->addModules( 'loop.special.settings.js' );
			$out->setPageTitle( $this->msg( 'loopsettings-specialpage-title' ) );
			
			$requestToken = $request->getText( 't' );
			$uploadButton = $this->msg( 'loopsettings-upload-hint' ) .
				$linkRenderer->makelink( 
					new TitleValue( NS_SPECIAL, 'ListFiles' ), 
                    new HtmlArmor( $this->getSkin()->msg ( 'listfiles' )),
                    array( "class" => "ml-1") 
				) . '<button type="button" class="upload-button mw-htmlform-submit mw-ui-button mt-2 d-block">' . $this->msg( 'upload' ) . '</button>';
			
			$currentLoopSettings = new LoopSettings();
			
			if ( ! empty( $requestToken ) ) {

				if ( $user->matchEditToken( $requestToken, $wgSecretKey, $request ) ) {
				
                    $currentLoopSettings->getLoopSettingsFromRequest( $request, $user );
                    
                    if ( empty ( $currentLoopSettings->errors ) ) {
                        
                        $html .= '<div class="alert alert-success" role="alert">' . $this->msg( 'loopsettings-save-success' ) . '</div>';
                    } else {
                        $errorMsgs = '';
                        foreach( $currentLoopSettings->errors as $error ) { 
                            
                            $errorMsgs .= $error . '<br>';
                            
                        }
                        $html .= '<div class="alert alert-danger" role="alert">' . $errorMsgs.'</div>';
                        
                    }
                    
                } else {
                    $currentLoopSettings->loadSettings();
                }
            } else {
                $currentLoopSettings->loadSettings();
            }
		$saltedToken = $user->getEditToken( $wgSecretKey, $request );
			
			$html .= '<nav>
				<div class="nav nav-tabs" id="nav-tab" role="tablist"> 
					<a class="nav-item nav-link active" id="nav-general-tab" data-toggle="tab" href="#nav-general" role="tab" aria-controls="nav-general" aria-selected="true">' . $this->msg( 'loopsettings-tab-general' )->text() . '</a>
					<a class="nav-item nav-link" id="nav-appearance-tab" data-toggle="tab" href="#nav-appearance" role="tab" aria-controls="nav-appearance" aria-selected="true">' . $this->msg( 'loopsettings-tab-appearance' )->text() . '</a>
                    <a class="nav-item nav-link" id="nav-content-tab" data-toggle="tab" href="#nav-content" role="tab" aria-controls="nav-content" aria-selected="true">' . $this->msg( 'loopsettings-tab-content' )->text() . '</a>
                    <a class="nav-item nav-link" id="nav-tech-tab" data-toggle="tab" href="#nav-tech" role="tab" aria-controls="nav-tech" aria-selected="true">' . $this->msg( 'loopsettings-tab-advanced' )->text() . '</a>
				</div>
			</nav>
			<form class="needs-validation mw-editform mt-3 mb-3" id="loopsettings-form" method="post" novalidate enctype="multipart/form-data">';
			 
			$html .= '<div class="tab-content" id="nav-tabContent">
				<div class="tab-pane fade show active" id="nav-general" role="tabpanel" aria-labelledby="nav-general-tab">';
				
				/** 
				 * GENERAL  TAB 
				 */	
					
					
					### LICENSE BLOCK ###
					$html .= '<h3>' . $this->msg( 'loopsettings-headline-license' ) . '</h3>';
					$html .= '<div class="form-row mb-4">';
					
					# cc-license
					$licenseOptions = '';
					foreach( $wgAvailableLicenses as $license => $option ) { 
						if ( $license == $currentLoopSettings->rightsType ) {
							$selected = 'selected';
						} else {
							$selected = '';
						}
						if ( $license == '' ) {
							$licenseText = $this->msg( 'Htmlform-chosen-placeholder' );
						} else {
							$licenseText = $license;
						}
						
						$licenseOptions .= '<option value="' . $license.'" ' . $selected.'>' . $licenseText.'</option>';
					}

					$html .= "<div class='col-12 col-sm-6'>
							<input type='checkbox' name='license-use-cc' id='license-use-cc' value='licenseUseCC' ". ( ! empty( $currentLoopSettings->rightsType ) ? 'checked' : '' ) .">
							<label for='license-use-cc'>" . $this->msg( 'loopsettings-use-cc-label' ) . "</label>
							<select class='form-control' ". ( empty( $currentLoopSettings->rightsType ) ? 'disabled' : '' ) ." name='rights-type' id='rights-type' selected='". $currentLoopSettings->rightsType ." '>
							" . $licenseOptions . "</select>
						</div>";
					
					# license text
					$html .= 
						'<div class="col-12 col-sm-6">
							<label for="rights-text">' . $this->msg( 'loopsettings-rights-label' ) . '</label>
							<input type="text"' . ' placeholder="'. $this->msg( 'loopsettings-rights-text-placeholder' ) .'" name="rights-text" id="rights-text" class="setting-input form-control" value=' . '"' . $currentLoopSettings->rightsText.'"' . '>
							<div class="invalid-feedback">' . $this->msg( 'loopsettings-rights-text-hint' ) . " ©,:._-!?&/()'</div>" .
						'</div>';
					$html .= '</div>';
                    
					### LINK BLOCK ###
                    $html .= '<h3>' . $this->msg( 'loopsettings-headline-footer-links' ) . '</h3>';
                    
					$html .= '<div class="form-row mb-4">';
					#$html .= '<div class="form-row">';

					# imprint link
					$html .= 
					'<div class="col-12 col-sm-6">
						<label for="imprint-link">' . $this->msg( 'loopsettings-imprint-label' ) . '</label>
						<input type="text" required name="imprint-link" placeholder="URL" id="imprint-link" class="setting-input form-control" value="'. $currentLoopSettings->imprintLink .'">
						<div class="invalid-feedback">' . $this->msg( 'loopsettings-url-imprint-privacy-hint' ) . '</div>
					</div>';
					# privacy link
					$html .= 
					'<div class="col-12 col-sm-6">
						<label for="privacy-link">' . $this->msg( 'loopsettings-privacy-label' ) . '</label>
						<input type="text" required name="privacy-link" placeholder="URL" id="privacy-link" class="setting-input form-control" value="'. $currentLoopSettings->privacyLink .'">
						<div class="invalid-feedback">' . $this->msg( 'loopsettings-url-imprint-privacy-hint' ) . '</div>
					</div>';
					$html .= '</div>';
					
					#footer-social
					$socialArray = array(
						'Facebook' => array( 'icon' => $currentLoopSettings->facebookIcon, 'link' => $currentLoopSettings->facebookLink ),
						'Twitter' => array( 'icon' => $currentLoopSettings->twitterIcon, 'link' => $currentLoopSettings->twitterLink ),
						'Youtube' => array( 'icon' => $currentLoopSettings->youtubeIcon, 'link' => $currentLoopSettings->youtubeLink ),
						'Github' => array( 'icon' => $currentLoopSettings->githubIcon, 'link' => $currentLoopSettings->githubLink ), 
						'Instagram' => array( 'icon' => $currentLoopSettings->instagramIcon, 'link' => $currentLoopSettings->instagramLink )
                    );
                    $i = 1;
					foreach( $wgLoopSocialIcons as $socialIcons => $socialIcon ) {
                        if ( $i % 2 != 0 ) {
                            $html .= '<div class="form-row">';
                        }
						$html .= '
                        <div class="col-6">
							<input type="checkbox" name="footer-'. $socialIcons .'-icon" id="footer-'. $socialIcons .'-icon" value="'. $socialIcons .'" '. ( ! empty ( $socialArray[$socialIcons]['icon']) ? 'checked' : '' ) .'>
								
							<label for="footer-' . $socialIcons .'-icon"><span class="ic ic-social-' . strtolower( $socialIcons ) . '"></span>
							'. $socialIcons . ' ' . $this->msg( 'loopsettings-link-icon-label' ) . '</label>
							
							<div class="input-group mb-3">
								<input type="url" ' . ( empty( $socialArray[$socialIcons]['icon'] ) ? 'disabled' : '' ) . ' name="footer-'. $socialIcons .'-url" placeholder="https://www.'. strtolower( $socialIcons) .'.com/" id="footer-'. $socialIcons .'-url" class="setting-input form-control" value="'. $socialArray[$socialIcons]['link'] .'">
								<div class="invalid-feedback" id="feedback-'. $socialIcons .'">' . $this->msg( 'loopsettings-url-hint' ) . '</div>
                            </div>
                        </div>';
                        if ( $i % 2 == 0 || $i == count( $wgLoopSocialIcons ) ) {
                            $html .= '</div>';
                        } 
                        $i++;
                    } 
                    
                    ### EXPORT BLOCK ###
                    $showExportOptions = false;
					$exporthtml = '<h3>' . $this->msg( 'loopsettings-headline-export' ) . '</h3>'; 
					$exporthtml .= '<div class="form-row mb-4">';
					$exporthtml .= '<div class="col-12">';
                    
                    $dummyLoopSettings = new LoopSettings(); # check further requirements of export types without interfering LoopSettings
                    $dummyLoopSettings->exportT2s = true;
                    $dummyLoopSettings->exportAudio = true;
                    $dummyLoopSettings->exportPdf = true;
                    $dummyLoopSettings->exportEpub = true;
                    $dummyLoopSettings->exportScorm = true;
                    $dummyLoopSettings->exportXml = true;
                    $dummyLoopSettings->exportHtml = true;

                    if ( LoopExportPageMp3::isAvailable( $dummyLoopSettings ) ) {
                        $exporthtml .= '<div><input type="checkbox" name="export-t2s" id="export-t2s" class="setting-input mr-1" ' . ( $currentLoopSettings->exportT2s ? 'checked' : '' ) .'>';
                        $exporthtml .= '<label for="export-t2s"><span class="mr-1 ic ic-audio"></span>' . $this->msg( 'loopsettings-export-t2s-label' ) . '</label></div>';
                        $showExportOptions = true;
                    }
                    if ( LoopExportPdf::isAvailable( $dummyLoopSettings ) ) {
                        $exporthtml .= '<div><input type="checkbox" name="export-pdf" id="export-pdf" class="setting-input mr-1" ' . ( $currentLoopSettings->exportPdf ? 'checked' : '' ) .'>';
                        $exporthtml .= '<label for="export-pdf"><span class="mr-1 ic ic-file-pdf"></span>' . $this->msg( 'loopsettings-export-pdf-label' ) . '</label></div>';
                        $showExportOptions = true;    
                    }
                    if ( LoopExportMp3::isAvailable( $dummyLoopSettings ) ) {
					    $exporthtml .= '<div><input type="checkbox" name="export-audio" id="export-audio" class="setting-input mr-1" ' . ( $currentLoopSettings->exportAudio ? 'checked' : '' ) .'>';
                        $exporthtml .= '<label for="export-audio"><span class="mr-1 ic ic-file-mp3"></span>' . $this->msg( 'loopsettings-export-audio-label' ) . '</label></div>';
                        $showExportOptions = true;
                    }
                    if ( LoopExportEpub::isAvailable( $dummyLoopSettings ) ) {
					    $exporthtml .= '<div><input type="checkbox" name="export-epub" id="export-epub" class="setting-input mr-1" ' . ( $currentLoopSettings->exportEpub ? 'checked' : '' ) .'>';
                        $exporthtml .= '<label for="export-epub"><span class="mr-1 ic ic-file-epub"></span>' . $this->msg( 'loopsettings-export-epub-label' ) . '</label></div>';
                        $showExportOptions = true;
                    }
                    if ( LoopExportScorm::isAvailable( $dummyLoopSettings ) ) {
                        $exporthtml .= '<div><input type="checkbox" name="export-scorm" id="export-scorm" class="setting-input mr-1" ' . ( $currentLoopSettings->exportScorm ? 'checked' : '' ) .'>';
                        $exporthtml .= '<label for="export-scorm"><span class="mr-1 ic ic-file-scorm"></span>' . $this->msg( 'loopsettings-export-scorm-label' ) . '</label></div>';
                        $showExportOptions = true;
					}
                    if ( LoopExportHtml::isAvailable( $dummyLoopSettings ) ) {
                        $exporthtml .= '<div><input type="checkbox" name="export-html" id="export-html" class="setting-input mr-1" ' . ( $currentLoopSettings->exportHtml ? 'checked' : '' ) .'>';
                        $exporthtml .= '<label for="export-html"><span class="mr-1 ic ic-file-xml"></span>' . $this->msg( 'loopsettings-export-html-label' ) . '</label></div>';
                        $showExportOptions = true;
					}
                    if ( LoopExportXml::isAvailable( $dummyLoopSettings ) ) {
					    $exporthtml .= '<div><input type="checkbox" name="export-xml" id="export-xml" class="setting-input mr-1" ' . ( $currentLoopSettings->exportXml ? 'checked' : '' ) .'>';
                        $exporthtml .= '<label for="export-xml"><span class="mr-1 ic ic-file-xml"></span>' . $this->msg( 'loopsettings-export-xml-label' ) . '</label></div>';
                        $showExportOptions = true;
                    }
                    $exporthtml .= '</div></div>';
                    if ( $showExportOptions ) {
                        $html .= $exporthtml;
                    }

				$html .= '</div>'; // end of general-tab
				
				/** 
				 * APPEARANCE TAB 
				 */	
				$html .= '<div class="tab-pane fade" id="nav-appearance" role="tabpanel" aria-labelledby="nav-appearance-tab">';
                    ### SKIN BLOCK ###
					$html .= '<div class="form-row mb-4">';
					$html .=    '<div class="col-6 pl-1">';
					$html .=        '<h3>' . $this->msg( 'loopsettings-headline-skinstyle' ) . '</h3>'; 
					$skinStyleOptions = '';
					foreach( $wgLoopSkinStyles as $style ) { 
					    if ( $style == $currentLoopSettings->skinStyle ) { 
							$selected = 'selected';
						} else {
							$selected = '';
						}
						$skinStyleOptions .= '<option value="' . $style.'" ' . $selected.'>'. $this->msg( 'loop-skin-'. $style ) .'</option>';
                    }
                    
					$html .= '<label for="skin-style">' . $this->msg( 'loopsettings-skin-style-label' ) . '</label>';
					$html .= '<select class="form-control" name="skin-style" id="skin-style" selected="'. $currentLoopSettings->skinStyle .' ">' . $skinStyleOptions . '</select>';
                    $html .=    '</div>';

                    $html .= '</div>'; #end of form-row

                    ### LOGO BLOCK ###
                    $editableStyles = "";
                    foreach( $wgLoopEditableSkinStyles as $editableStyle ) {
                        if ( !empty( $editableStyles ) ) {
                            $editableStyles .= ", ";
                        }
                        $editableStyles .= $this->msg( 'loop-skin-'. $editableStyle );
                    }
                    $html .= '<h3>' . $this->msg( 'loopsettings-headline-logo' ) . '</h3>';
					$html .= '<div class="form-row mb-4">';
					$html .=    '<div class="col-6 col-sm-6 pl-1">';
					$html .=        '<input type="checkbox" class="mr-1" name="logo-use-custom" id="logo-use-custom" '. ( ! empty( $currentLoopSettings->customLogo ) ? 'checked' : '' ) .'>';
                    $html .=        '<label for="logo-use-custom">' . $this->msg( 'loopsettings-customlogo-label' ) . '</label>';
                    $html .=        '<span class="logo-hint ic ic-info pl-1 small" id="logo-hint" title="' . $this->msg( 'loopsettings-customlogo-editablestyles', $editableStyles ). '"></span>';
					$html .=        '<input '. ( ! $currentLoopSettings->customLogo ? 'disabled' : '' ) .' name="custom-logo-filename" placeholder="Logo.png" id="custom-logo-filename" class="form-control setting-input" value="' . $currentLoopSettings->customLogoFileName.'">';
					$html .=        '<div class="invalid-feedback">' . $this->msg( 'loopsettings-customlogo-hint' ) . '</div>';
					$html .=        '<input type="hidden" name="custom-logo-filepath" id="custom-logo-filepath" value="' . $currentLoopSettings->customLogoFilePath.'">';
					$html .=    '</div>';
                    $html .=    '<div class="col-3 col-sm-6">';
                    if ( $currentLoopSettings->customLogo && ! empty( $currentLoopSettings->customLogoFilePath ) ) {
                        $html .=    "<p class='mb-1 mr-2'>" . $this->msg( 'Prefs-preview' ) . ' ' . $currentLoopSettings->customLogoFileName.":</p>";
                        $html .=    "<img src='" . $currentLoopSettings->customLogoFilePath."' style='max-width:100%; max-height: 50px;'></img>";
                    }
                    $html .=    '</div>';
                    $html .= $uploadButton;
                    $html .= '</div>';


                    $html .= '<div class="form-row mb-4">';
                    # extra sidebar
                    $html .=    '<div class="col-6 pl-1">';
					$html .=        '<h3>' . $this->msg( 'loopsettings-headline-sidebar' ) . '</h3>'; 
					$html .=        '<input class="mr-1" type="checkbox" name="extra-sidebar-active" id="extra-sidebar-active" ' . ( ! empty ( $currentLoopSettings->extraSidebar ) ? 'checked' : '' ) .'>';
                    $html .=        '<label for="extra-sidebar-active">' . $this->msg( 'loopsettings-extra-sidebar-label' ) . '</label>';
                    $html .= $linkRenderer->makeLink(
                        Title::newFromText("MediaWiki:ExtraSidebar"),
                        new HtmlArmor( $this->msg("loopsettings-extra-sidebar-linktext") ),
                        array("target" => "blank", "class" => "ml-1")
                    );
                    $html .= '  </div>';

                    
					# extra footer
                    $html .= '<div class="col-6 pl-1">';
                    $html .= '<h3>' . $this->msg( 'loopsettings-headline-extrafooter' ) . '</h3>';
					$html .= '<input type="checkbox" name="extra-footer-active" id="extra-footer-active" ' . ( ! empty ( $currentLoopSettings->extraFooter ) ? 'checked' : '' ) .'>
						<label for="extra-footer-active">' . $this->msg( 'loopsettings-extra-footer-label' ) . '</label>';
					$html .= $linkRenderer->makeLink(
						Title::newFromText("MediaWiki:ExtraFooter"),
						new HtmlArmor( $this->msg("loopsettings-extra-footer-linktext") ),
						array("target" => "blank", "class" => "ml-1")
					);
                    $html .= '</div>';
                    $html .= '</div>'; #end of form-row
                    
				$html .= '</div>'; // end of appearence-tab
				
				/** 
				 * TECH SETTINGS TAB 
				 */	
				$html .= '<div class="tab-pane fade" id="nav-tech" role="tabpanel" aria-labelledby="nav-tech-tab">';
                
                    ### CAPTCHA BLOCK ###
                    global $wgReCaptchaSiteKey, $wgReCaptchaSecretKey;
                    $captchaDisabled = "";
                    $captchaDisabledMsg = "";
                    if ( empty( $wgReCaptchaSiteKey ) || empty( $wgReCaptchaSecretKey ) ) {
                        $captchaDisabled = "disabled";
                        $captchaDisabledMsg = '<div class="alert alert-warning w-100">'. $this->msg( 'loopsettings-captcha-nokeys' )->text().'</div>';
                    }
					$html .= '<div class="form-row mb-4">';
                    $html .= '<h3>' . $this->msg( 'loopsettings-headline-captcha' )->text() . '</h3>'; 
                    $html .= $captchaDisabledMsg;
                    $html .= '<p class="mb-1">'.$this->msg( "loopsettings-captcha-desc")->text().'</p>';
					$html .= '<div class="col-12">';
					$html .= '<div><input type="checkbox" name="captcha-edit" id="captcha-edit" class="setting-input mr-1" ' . $captchaDisabled . " " . ( $currentLoopSettings->captchaEdit == "captchaEdit" ? 'checked' : '' ) .'>';
                    $html .= '<label for="captcha-edit">' . $this->msg( 'loopsettings-captcha-edit-label' )->text() . '*</label></div>';
					$html .= '<div><input type="checkbox" name="captcha-create" id="captcha-create" class="setting-input mr-1" ' . $captchaDisabled . " " . ( $currentLoopSettings->captchaCreate == "captchaCreate" ? 'checked' : '' ) .'>';
					$html .= '<label for="captcha-create">' . $this->msg( 'loopsettings-captcha-create-label' )->text() . '*</label></div>';
					$html .= '<div><input type="checkbox" name="captcha-addurl" id="captcha-addurl" class="setting-input mr-1" ' . $captchaDisabled . " " . ( $currentLoopSettings->captchaAddurl == "captchaAddurl" ? 'checked' : '' ) .'>';
                    $html .= '<label for="captcha-addurl">' . $this->msg( 'loopsettings-captcha-addurl-label' )->text() . '*</label></div>';
                    $html .= '<div><input type="checkbox" name="captcha-createaccount" id="captcha-createaccount" class="setting-input mr-1" ' . $captchaDisabled . " " . ( $currentLoopSettings->captchaCreateAccount == "captchaCreateAccount" ? 'checked' : '' ) .'>';
                    $html .= '<label for="captcha-createaccount">' . $this->msg( 'loopsettings-captcha-createaccount-label' )->text() . '</label></div>';
					$html .= '<div><input type="checkbox" name="captcha-badlogin" id="captcha-badlogin" class="setting-input mr-1" ' . $captchaDisabled . " " . ( $currentLoopSettings->captchaBadlogin == "captchaBadlogin" ? 'checked' : '' ) .'>';
                    $html .= '<label for="captcha-badlogin">' . $this->msg( 'loopsettings-captcha-badlogin-label' ) . '</label></div>';
					$html .= '<p><i>* '.$this->msg( "loopsettings-captcha-usergroup")->text().'</i></p>';
                    $html .= '</div>';
                    $html .= '</div>';

				$html .= '</div>'; // end of tech-tab

				
				/** 
				 * CONTENT TAB 
				 */	
				$html .= '<div class="tab-pane fade" id="nav-content" role="tabpanel" aria-labelledby="nav-content-tab">';

                   # $html .= '<div class="form-row">';
					$html .= '<h3>' . $this->msg( 'loopsettings-numbering' ) . '</h3>';

					$html .= '<div class="mb-1"><input type="checkbox" name="numbering-objects" id="numbering-objects" value="numberingObjects" ' . ( $currentLoopSettings->numberingObjects == true ? 'checked' : '' ) .'>
					<label for="numbering-objects">' . $this->msg( 'loopsettings-numbering-objects-label' ) . '</label></div>';

					$html .= '<div class="mb-1"><input type="radio" name="numbering-type" id="ongoing" value="ongoing" ' . ( $currentLoopSettings->numberingType == "ongoing" ? 'checked' : '' ) .'>
					<label for="ongoing">' . $this->msg( 'loopsettings-numbering-type-ongoing-label' ) . '</label></div>';

					$html .= '<div class="mb-3"><input type="radio" name="numbering-type" id="chapter" value="chapter" ' . ( $currentLoopSettings->numberingType == "chapter" ? 'checked' : '' ) .'>
					<label for="chapter">' . $this->msg( 'loopsettings-numbering-type-chapter-label' ) . '</label></div>';
              

                    #$html .= '<div class="form-row">';
                    $html .= '<h3>' . $this->msg( "loopsettings-citation-style" ) . '</h3>';
                    $html .= '<div class="mb-1"><input type="radio" name="citation-style" id="harvard" value="harvard" ' . ( $currentLoopSettings->citationStyle == "harvard" ? 'checked' : '' ) .'>';
                    $html .= '<label for="harvard"> ' . $this->msg( 'loopsettings-citation-style-harvard-label' ) . '</label></div>';

					$html .= '<div class="mb-1"><input type="radio" name="citation-style" id="vancouver" value="vancouver" ' . ( $currentLoopSettings->citationStyle == "vancouver" ? 'checked' : '' ) .'>';
					$html .= '<label for="vancouver"> ' . $this->msg( 'loopsettings-citation-style-vancouver-label' ) . '</label></div>';
                   
				$html .= '</div>'; // end of content-tab
				
			$html .= '</div>'; // end of tab-content
			
			$html .= '<input type="hidden" name="t" id="loopsettings-token" value="' . $saltedToken . '"></input>
					<input type="submit" class="mw-htmlform-submit mw-ui-button mw-ui-primary mw-ui-progressive mt-2 d-block" id="loopsettings-submit" value="' . $this->msg( 'submit' ) . '"></input>';
			
			$html .= '</form>';
			
			} else {
				$html .= '<div class="alert alert-warning" role="alert">' . $this->msg( 'loopsettings-no-permission' ) . '</div>';
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