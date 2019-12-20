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
    public $numberingObjects;
    public $numberingType;
    public $citationStyle;
    public $extraSidebar;

    public $exportT2s;
    public $exportAudio;
    public $exportPdf;
    public $exportEpub;
    public $exportScorm;
    public $captchaEdit;
    public $captchaCreate;
    public $captchaAddurl;
    public $captchaCreateAccount;
    public $captchaBadlogin;

    /**
     * Add settings to the database
     * @return bool true
     */
    function addToDatabase() {

        $this->dbkeys = array(
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
            'lset_numberingobjects' => $this->numberingObjects,
            'lset_numberingtype' => $this->numberingType,
            'lset_citationstyle' => $this->citationStyle,
            'lset_extrasidebar' => $this->extraSidebar,
            'lset_exportt2s' => $this->$exportT2s,
            'lset_exportaudio' => $this->$exportAudio,
            'lset_exportpdf' => $this->$exportPdf,
            'lset_exportepub' => $this->$exportEpub,
            'lset_exportscorm' => $this->$exportScorm,
            'lset_captchaedit' => $this->$captchaEdit,
            'lset_captchacreate' => $this->$captchaCreate,
            'lset_captchaddurl' => $this->$captchaAddurl,
            'lset_captchacreateaccount' => $this->$captchaCreateAccount,
            'lset_captchabadlogin' => $this->$captchaBadlogin
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

        global $wgOut, $wgDefaultUserOptions, $wgImprintLink, $wgPrivacyLink, $wgOncampusLink, $wgLanguageCode, $wgLoopObjectNumbering, 
        $wgLoopNumberingType, $wgLoopLiteratureCiteType;
        
        $this->oncampusLink = $wgOncampusLink;
        $this->languageCode = $wgLanguageCode;
        $this->skinStyle = $wgOut->getUser()->getOption( 'LoopSkinStyle', $wgDefaultUserOptions['LoopSkinStyle'], true );
        $this->imprintLink = $wgImprintLink;
        $this->privacyLink = $wgPrivacyLink;
        $this->numberingObjects = $wgLoopObjectNumbering;
        $this->numberingType = $wgLoopNumberingType;
        $this->citationStyle = $wgLoopLiteratureCiteType;
        $this->exportT2s = "exportT2s";
        $this->exportAudio = "exportAudio";
        $this->exportPdf = "exportPdf";
        $this->exportEpub = "exportEpub";
        $this->exportScorm = "exportScorm";
        
        if ( isset($row->lset_structure) ) {
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
            $this->citationStyle = $data['lset_citationstyle'];
            $this->extraSidebar = $data['lset_extrasidebar'];
            $this->exportT2s = $data['lset_exportt2s'];
            $this->exportAudio = $data['lset_exportaudio'];
            $this->exportPdf = $data['lset_exportpdf'];
            $this->exportEpub = $data['lset_exportepub'];
            $this->exportScorm = $data['lset_exportscorm'];
            $this->captchaEdit = $data['lset_captchaedit'];
            $this->captchaCreate = $data['lset_captchacreate'];
            $this->captchaAddurl = $data['lset_captchaddurl'];
            $this->captchaCreateAccount = $data['lset_captchacreateaccount'];
            $this->captchaBadlogin = $data['lset_captchabadlogin'];
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
     * @return Bool
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
        
        # Numbering objects
        if ( $request->getText( 'numbering-objects' ) == 'numberingObjects' ) { 
            $this->numberingObjects = true;
        } elseif ( empty ( $request->getText( 'numbering-objects' ) ) ) {
            $this->numberingObjects = false;
        } else {
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
        
        if ( empty ( $request->getText( 'extra-sidebar-active' ) ) || $request->getText( 'extra-sidebar-active' ) == 'useExtraSidebar' ) {
            $this->extraSidebar = $request->getText( 'extra-sidebar-active' );
        } else {
            array_push( $this->errors, wfMessage( 'loopsettings-error' )  . ': ' . wfMessage( 'loopsettings-extra-sidebar-label' ) );
        }

        
        # Export t2s
        if ( $request->getText( 'export-t2s' ) == 'exportT2s' ) { 
            $this->numberingObjects = true;
        } elseif ( empty ( $request->getText( 'export-t2s' ) ) ) {
            $this->numberingObjects = false;
        } else {
            array_push( $this->errors, wfMessage( 'loopsettings-error' )  . ': ' . wfMessage( 'loopsettings-export-t2s-label' ) );
        }
        # Export audio
        if ( $request->getText( 'export-audio' ) == 'exportAudio' ) { 
            $this->numberingObjects = true;
        } elseif ( empty ( $request->getText( 'export-audio' ) ) ) {
            $this->numberingObjects = false;
        } else {
            array_push( $this->errors, wfMessage( 'loopsettings-error' )  . ': ' . wfMessage( 'loopsettings-export-audio-label' ) );
        }
        # Export pdf
        if ( $request->getText( 'export-pdf' ) == 'exportPdf' ) { 
            $this->numberingObjects = true;
        } elseif ( empty ( $request->getText( 'export-pdf' ) ) ) {
            $this->numberingObjects = false;
        } else {
            array_push( $this->errors, wfMessage( 'loopsettings-error' )  . ': ' . wfMessage( 'loopsettings-export-pdf-label' ) );
        }
        # Export epub
        if ( $request->getText( 'export-epub' ) == 'exportEpub' ) { 
            $this->numberingObjects = true;
        } elseif ( empty ( $request->getText( 'export-epub' ) ) ) {
            $this->numberingObjects = false;
        } else {
            array_push( $this->errors, wfMessage( 'loopsettings-error' )  . ': ' . wfMessage( 'loopsettings-export-epub-label' ) );
        }
        # Export scorm
        if ( $request->getText( 'export-scorm' ) == 'exportScorm' ) { 
            $this->numberingObjects = true;
        } elseif ( empty ( $request->getText( 'export-scorm' ) ) ) {
            $this->numberingObjects = false;
        } else {
            array_push( $this->errors, wfMessage( 'loopsettings-error' )  . ': ' . wfMessage( 'loopsettings-export-scorm-label' ) );
        }
        
        # Captcha edit
        if ( $request->getText( 'captcha-edit' ) == 'captcha' ) { 
            $this->numberingObjects = true;
        } elseif ( empty ( $request->getText( 'captcha-ecit' ) ) ) {
            $this->numberingObjects = false;
        } else {
            array_push( $this->errors, wfMessage( 'loopsettings-error' )  . ': ' . wfMessage( 'loopsettings-captcha-edit-label' ) );
        }
        # Captcha create
        if ( $request->getText( 'captcha-create' ) == 'captcha' ) { 
            $this->numberingObjects = true;
        } elseif ( empty ( $request->getText( 'captcha-create' ) ) ) {
            $this->numberingObjects = false;
        } else {
            array_push( $this->errors, wfMessage( 'loopsettings-error' )  . ': ' . wfMessage( 'loopsettings-captcha-create-label' ) );
        }
        # Captcha createaccount
        if ( $request->getText( 'captcha-createaccount' ) == 'captcha' ) { 
            $this->numberingObjects = true;
        } elseif ( empty ( $request->getText( 'captcha-createaccount' ) ) ) {
            $this->numberingObjects = false;
        } else {
            array_push( $this->errors, wfMessage( 'loopsettings-error' )  . ': ' . wfMessage( 'loopsettings-captcha-createaccount-label' ) );
        }
        # Captcha addurl
        if ( $request->getText( 'captcha-addurl' ) == 'captcha' ) { 
            $this->numberingObjects = true;
        } elseif ( empty ( $request->getText( 'captcha-addurl' ) ) ) {
            $this->numberingObjects = false;
        } else {
            array_push( $this->errors, wfMessage( 'loopsettings-error' )  . ': ' . wfMessage( 'loopsettings-captcha-addurl-label' ) );
        }
        # Captcha badlogin
        if ( $request->getText( 'captcha-badlogin' ) == 'captcha' ) { 
            $this->numberingObjects = true;
        } elseif ( empty ( $request->getText( 'captcha-badlogin' ) ) ) {
            $this->numberingObjects = false;
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
			
			global $IP, $wgSecretKey, $wgSocialIcons, $wgAvailableLicenses, $wgSpecialPages, $wgText2SpeechServiceUrl,
			$wgSkinStyles, $wgLanguageCode, $wgSupportedLoopLanguages, $wgXmlfo2PdfServiceUrl, $wgXmlfo2PdfServiceToken;
				
			$this->setHeaders();
			
 			$out->addModules( 'loop.special.settings.js' );
			$out->setPageTitle( $this->msg( 'loopsettings-specialpage-title' ) );
			
			$requestToken = $request->getText( 't' );
			$uploadButton = $this->msg( 'loopsettings-upload-hint' ) . " " . 
				$linkRenderer->makelink( 
					new TitleValue( NS_SPECIAL, 'ListFiles' ), 
					new HtmlArmor( $this->getSkin()->msg ( 'listfiles' ) )
				) . '<button type="button" class="upload-button mw-htmlform-submit mw-ui-button mt-2 d-block">' . $this->msg( 'upload' ) . '</button>';
			
			$currentLoopSettings = new LoopSettings();
			
			if ( ! empty( $requestToken ) ) {

				if ( $user->matchEditToken( $requestToken, $wgSecretKey, $request ) ) {
				
				$currentLoopSettings->getLoopSettingsFromRequest( $request );
				
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
			
			$html .= 
			'<nav>
				<div class="nav nav-tabs" id="nav-tab" role="tablist"> 
					<a class="nav-item nav-link active" id="nav-general-tab" data-toggle="tab" href="#nav-general" role="tab" aria-controls="nav-general" aria-selected="true">' . $this->msg( 'loopsettings-tab-general' ) . '</a>
					<a class="nav-item nav-link" id="nav-appearance-tab" data-toggle="tab" href="#nav-appearance" role="tab" aria-controls="nav-appearance" aria-selected="true">' . $this->msg( 'loopsettings-tab-appearance' ) . '</a>
                    <a class="nav-item nav-link" id="nav-content-tab" data-toggle="tab" href="#nav-content" role="tab" aria-controls="nav-content" aria-selected="true">' . $this->msg( 'loopsettings-tab-content' ) . '</a>
                    <a class="nav-item nav-link" id="nav-tech-tab" data-toggle="tab" href="#nav-tech" role="tab" aria-controls="nav-tech" aria-selected="true">' . $this->msg( 'loopsettings-tab-tech' ) . '</a>
				</div>
			</nav>
			<form class="needs-validation mw-editform mt-3 mb-3" id="loopsettings-form" method="post" novalidate enctype="multipart/form-data">';
			 
			$html .= '<div class="tab-content" id="nav-tabContent">
				<div class="tab-pane fade show active" id="nav-general" role="tabpanel" aria-labelledby="nav-general-tab">';
				
				/** 
				 * GENERAL  TAB 
				 */	
				
					### LINK BLOCK ###
					$html .= '<h3>' . $this->msg( 'loopsettings-headline-important-links' ) . '</h3>';
					$html .= '<div class="form-row mb-3">';
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
					
					#$html .= '</div>';
					$html .= '</div>';
					
					### LICENSE BLOCK ###
					$html .= '<h3>' . $this->msg( 'loopsettings-headline-license' ) . '</h3>';
					$html .= '<div class="form-row mb-3">';
					
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
					
					### LANGUAGE BLOCK ###
					$html .= '<h3>' . $this->msg( 'loopsettings-headline-language' ) . '</h3>'; 
					$html .= '<div class="form-row mb-3">';
					$html .= '<div class="col-6">';
					$languageOptions = '';
					foreach( $wgSupportedLoopLanguages as $language ) { 
						if ( $language == $currentLoopSettings->languageCode ) { 
							$selected = 'selected';
						} else {
							$selected = '';
						}
						
						$languageOptions .= '<option value="' . $language.'" ' . $selected.'>'. $language .'</option>';
					}
					$html .= '<label for="language-select">' . $this->msg( 'loopsettings-language-label' ) . '</label>
					<select class="form-control" name="language-select" id="language-select" selected="'. $currentLoopSettings->languageCode .'">' . $languageOptions . '</select>';
                    $html .= '</div>';
                    $html .= '</div>';
                    
					### EXPORT BLOCK ###
					$html .= '<h3>' . $this->msg( 'loopsettings-headline-export' ) . '</h3>'; 
					$html .= '<div class="form-row mb-3">';
					$html .= '<div class="col-6">';
					$html .= '<div class="mb-1"><input type="checkbox" name="export-t2s" id="export-t2s" class="setting-input" value="exportT2s' . ( $currentLoopSettings->exportT2s == "exportT2s" ? 'checked' : '' ) .'>';
                    $html .= '<label for="export-t2s">' . $this->msg( 'loopsettings-export-t2s-label' ) . '</label></div>';
                    if ( !empty( $wgXmlfo2PdfServiceUrl ) && !empty( $wgXmlfo2PdfServiceToken ) ) {
                        $html .= '<div class="mb-1"><input type="checkbox" name="export-pdf" id="export-pdf" class="setting-input" value="exportPdf' . ( $currentLoopSettings->exportPdf == "exportPdf" ? 'checked' : '' ) .'>';
                        $html .= '<label for="export-pdf">' . $this->msg( 'loopsettings-export-pdf-label' ) . '</label></div>';                   
                    }
					$html .= '<div class="mb-1"><input type="checkbox" name="export-epub" id="export-epub" class="setting-input" value="exportEpub' . ( $currentLoopSettings->exportEpub == "exportEpub" ? 'checked' : '' ) .'>';
                    $html .= '<label for="export-epub">' . $this->msg( 'loopsettings-export-epub-label' ) . '</label></div>';
                    if ( !empty( $wgText2SpeechServiceUrl ) ) {
					    $html .= '<div class="mb-1"><input type="checkbox" name="export-audio" id="export-audio" class="setting-input" value="exportAudio' . ( $currentLoopSettings->exportAudio == "exportAudio" ? 'checked' : '' ) .'>';
                        $html .= '<label for="export-audio">' . $this->msg( 'loopsettings-export-audio-label' ) . '</label></div>';
                    }
                    $html .= '<div class="mb-1"><input type="checkbox" name="export-scorm" id="export-scorm" class="setting-input" value="exportScorm' . ( $currentLoopSettings->exportScorm == "exportScorm" ? 'checked' : '' ) .'>';
                    $html .= '<label for="export-scorm">' . $this->msg( 'loopsettings-export-scorm-label' ) . '</label></div>';
					
                    $html .= '</div>';
                    $html .= '</div>';
                    
				$html .= '</div>'; // end of general-tab
				
				/** 
				 * APPEARANCE TAB 
				 */	
				$html .= '<div class="tab-pane fade" id="nav-appearance" role="tabpanel" aria-labelledby="nav-appearance-tab">';
                    ### SKIN BLOCK ###
					$html .= '<div class="form-row mb-3">';
					$html .=    '<div class="col-6 pl-1">';
					$html .=        '<h3>' . $this->msg( 'loopsettings-headline-skinstyle' ) . '</h3>'; 
					$skinStyleOptions = '';
					foreach( $wgSkinStyles as $style ) { 
					if ( $style == $currentLoopSettings->skinStyle ) { #TODO: Some styles should not be selectable from every loop
							$selected = 'selected';
						} else {
							$selected = '';
						}
						$skinStyleOptions .= '<option value="' . $style.'" ' . $selected.'>'. $this->msg( 'loop-skinstyle-'. $style ) .'</option>';
                    }
                    
					$html .= '<label for="skin-style">' . $this->msg( 'loopsettings-skin-style-label' ) . '</label>';
					$html .= '<select class="form-control" name="skin-style" id="skin-style" selected="'. $currentLoopSettings->skinStyle .' ">' . $skinStyleOptions . '</select>';
                    $html .=    '</div>';

                    $html .= '</div>'; #end of form-row

                    $html .= '<div class="form-row mb-3">';
                    # extra sidebar
                    $html .=    '<div class="col-6 pl-1">';
					$html .=        '<h3>' . $this->msg( 'loopsettings-headline-sidebar' ) . '</h3>'; 
					$html .=        '<input class="mr-1" type="checkbox" name="extra-sidebar-active" id="extra-sidebar-active" value="useExtraSidebar" ' . ( ! empty ( $currentLoopSettings->extraSidebar ) ? 'checked' : '' ) .'>';
                    $html .=        '<label for="extra-sidebar-active">' . $this->msg( 'loopsettings-extra-sidebar-label' ) . '</label>';
                    $html .= $linkRenderer->makeLink(
                        Title::newFromText("MediaWiki:ExtraSidebar"),
                        new HtmlArmor( $this->msg("loopsettings-extra-sidebar-linktext") ),
                        array("target" => "blank")
                    );
                    $html .= '  </div>';

                    
					# extra footer
                    $html .= '<div class="col-6 pl-1">';
                    $html .= '<h3>' . $this->msg( 'loopsettings-headline-extrafooter' ) . '</h3>';
					$html .= '<input type="checkbox" name="extra-footer-active" id="extra-footer-active" value="useExtraFooter" ' . ( ! empty ( $currentLoopSettings->extraFooter ) ? 'checked' : '' ) .'>
						<label for="extra-footer-active">' . $this->msg( 'loopsettings-extra-footer-label' ) . '</label>';
					$html .= $linkRenderer->makeLink(
						Title::newFromText("MediaWiki:ExtraFooter"),
						new HtmlArmor( $this->msg("loopsettings-extra-footer-linktext") ),
						array("target" => "blank")
					);
                    $html .= '</div>';


                    $html .= '</div>'; #end of form-row

					### LOGO BLOCK ###
					$html .= '<h3>' . $this->msg( 'loopsettings-headline-logo' ) . '</h3>';
					# logo
					$html .= '<div class="form-row" mb-3>';
					$html .=    '<div class="col-6 col-sm-6 pl-1">';
					$html .=        '<input type="checkbox" class="mr-1" name="logo-use-custom" id="logo-use-custom" value="useCustomLogo" '. ( ! empty( $currentLoopSettings->customLogo ) ? 'checked' : '' ) .'>';
					$html .=        '<label for="logo-use-custom">' . $this->msg( 'loopsettings-customlogo-label' ) . '</label>';
					$html .=        '<input '. ( empty( $currentLoopSettings->customLogo ) ? 'disabled' : '' ) .' name="custom-logo-filename" placeholder="Logo.png" id="custom-logo-filename" class="form-control setting-input" value="' . $currentLoopSettings->customLogoFileName.'">';
					$html .=        '<div class="invalid-feedback">' . $this->msg( 'loopsettings-customlogo-hint' ) . '</div>';
					$html .=        '<input type="hidden" name="custom-logo-filepath" id="custom-logo-filepath" value="' . $currentLoopSettings->customLogoFilePath.'">';
					$html .=    '</div>';
                    $html .=    '<div class="col-3 col-sm-6">';
                    if ( $currentLoopSettings->customLogo == "useCustomLogo" && ! empty( $currentLoopSettings->customLogoFilePath ) ) {
                        $html .=    "<p class='mb-1 mr-2'>" . $this->msg( 'Prefs-preview' ) . ' ' . $currentLoopSettings->customLogoFileName.":</p>";
                        $html .=    "<img src='" . $currentLoopSettings->customLogoFilePath."' style='max-width:100%; max-height: 50px;'></img>";
                    }
                    $html .=    '</div>';
                    $html .= '</div>';

					#upload
                    $html .= $uploadButton;

					### LINK BLOCK ###
					$html .= '<h3>' . $this->msg( 'loopsettings-headline-footer-links' ) . '</h3>';
					
					#oncampus link
					$html .= '<div class="mb-3"><input type="checkbox" name="oncampus-link" id="oncampus-link" value="showOncampusLink" ' . ( ! empty ( $currentLoopSettings->oncampusLink ) ? 'checked' : '' ) .'>
						<label for="oncampus-link">' . $this->msg( 'loopsettings-oncampus-label' ) . '</label></div>';
						
					#footer-social
					$socialArray = array(
						'Facebook' => array( 'icon' => $currentLoopSettings->facebookIcon, 'link' => $currentLoopSettings->facebookLink ),
						'Twitter' => array( 'icon' => $currentLoopSettings->twitterIcon, 'link' => $currentLoopSettings->twitterLink ),
						'Youtube' => array( 'icon' => $currentLoopSettings->youtubeIcon, 'link' => $currentLoopSettings->youtubeLink ),
						'Github' => array( 'icon' => $currentLoopSettings->githubIcon, 'link' => $currentLoopSettings->githubLink ), 
						'Instagram' => array( 'icon' => $currentLoopSettings->instagramIcon, 'link' => $currentLoopSettings->instagramLink )
                    );
                    $i = 1;
					foreach( $wgSocialIcons as $socialIcons => $socialIcon ) {
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
                        if ( $i % 2 == 0 || $i == count( $wgSocialIcons ) ) {
                            $html .= '</div>';
                        } 
                        $i++;
                    } 
                    
					#$html .= '</div>';
				$html .= '</div>'; // end of appearence-tab
				
				/** 
				 * TECH SETTINGS TAB 
				 */	
				$html .= '<div class="tab-pane fade" id="nav-tech" role="tabpanel" aria-labelledby="nav-tech-tab">';
                
                ### CAPTCHA BLOCK ###
					$html .= '<div class="form-row mb-3">';
					$html .= '<h3>' . $this->msg( 'loopsettings-headline-captcha' ) . '</h3>'; 
					$html .= '<div class="col-12">';
					$html .= '<div class="mb-1"><input type="checkbox" name="captcha-edit" id="captcha-edit" class="setting-input" value="captchaEdit" ' . ( $currentLoopSettings->captchaEdit == "captchaEdit" ? 'checked' : '' ) .'>';
                    $html .= '<label for="captcha-edit">' . $this->msg( 'loopsettings-captcha-edit-label' ) . '</label></div>';
					$html .= '<div class="mb-1"><input type="checkbox" name="captcha-create" id="captcha-create" class="setting-input" value="captchaCreate" ' . ( $currentLoopSettings->captchaCreate == "captchaCreate" ? 'checked' : '' ) .'>';
					$html .= '<label for="captcha-create">' . $this->msg( 'loopsettings-captcha-create-label' ) . '</label></div>';
					$html .= '<div class="mb-1"><input type="checkbox" name="captcha-addurl" id="captcha-addurl" class="setting-input" value="captchaAddurl" ' . ( $currentLoopSettings->captchaAddurl == "captchaAddurl" ? 'checked' : '' ) .'>';
                    $html .= '<label for="captcha-addurl">' . $this->msg( 'loopsettings-captcha-addurl-label' ) . '</label></div>';
                    $html .= '<div class="mb-1"><input type="checkbox" name="captcha-createaccount" id="captcha-createaccount" class="setting-input" value="captchaCreateAccount" ' . ( $currentLoopSettings->captchaCreateAccount == "captchaCreateAccount" ? 'checked' : '' ) .'>';
                    $html .= '<label for="captcha-createaccount">' . $this->msg( 'loopsettings-captcha-createaccount-label' ) . '</label></div>';
					$html .= '<div class="mb-1"><input type="checkbox" name="captcha-badlogin" id="captcha-badlogin" class="setting-input" value="captchaBadlogin" ' . ( $currentLoopSettings->captchaBadlogin == "captchaBadlogin" ? 'checked' : '' ) .'>';
                    $html .= '<label for="captcha-badlogin">' . $this->msg( 'loopsettings-captcha-badlogin-label' ) . '</label></div>';
					
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
                   
                    #$html .= '</div>';
            


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