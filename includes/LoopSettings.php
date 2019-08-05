<?php

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
            'lset_citationstyle' => $this->citationStyle
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
		
	    $linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
	    $linkRenderer->setForceArticlePath(true);
		$user = $this->getUser();
		$out = $this->getOutput();
		$html = '';#<h1 id="loopsettings-h1">' . $this->msg( 'loopsettings-specialpage-title' ) . '</h1>';
		
		if ( $user->isAllowed( 'loop-settings-edit' ) ) {
			
			global $IP, $wgSecretKey, $wgSocialIcons, $wgAvailableLicenses, $wgSpecialPages, 
			$wgSkinStyles, $wgLanguageCode, $wgSupportedLoopLanguages;
				
			$this->setHeaders();
			$out = $this->getOutput();
			$request = $this->getRequest();
			
 			$out->addModules( 'loop.special.settings.js' );
			$out->setPageTitle( $this->msg( 'loopsettings-specialpage-title' ) );
			
			$requestToken = $request->getText( 't' );
			$uploadButton = $this->msg( 'loopsettings-upload-hint' ) . " " . 
				$linkRenderer->makelink( 
					new TitleValue( NS_SPECIAL, 'ListFiles' ), 
					new HtmlArmor( $this->getSkin()->msg ( 'listfiles' ) )
				) . '<button type="button" class="upload-button mw-htmlform-submit mw-ui-button mt-2 d-block">' . $this->msg( 'upload' ) . '</button><br>';
			
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
					<a class="nav-item nav-link" id="nav-footer-tab" data-toggle="tab" href="#nav-footer" role="tab" aria-controls="nav-footer" aria-selected="true">' . $this->msg( 'loopsettings-tab-footer' ) . '</a>
					<a class="nav-item nav-link" id="nav-content-tab" data-toggle="tab" href="#nav-content" role="tab" aria-controls="nav-content" aria-selected="true">' . $this->msg( 'loopsettings-tab-content' ) . '</a>
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
					$html .= '<div class="form-row">';

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
					
					$html .= '</div><br>';
					
					### LICENSE BLOCK ###
					$html .= '<h3>' . $this->msg( 'loopsettings-headline-license' ) . '</h3>';
					
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
					$html .= 
					"<div class='form-row'>
						<div class='col-12 col-sm-6'>
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
						'</div>
					</div><br>';
					
					### LANGUAGE BLOCK ###
					$html .= '<h3>' . $this->msg( 'loopsettings-headline-language' ) . '</h3>'; 
					
					$languageOptions = '';
					foreach( $wgSupportedLoopLanguages as $language ) { 
						if ( $language == $currentLoopSettings->languageCode ) { 
							$selected = 'selected';
						} else {
							$selected = '';
						}
						
						$languageOptions .= '<option value="' . $language.'" ' . $selected.'>'. $language .'</option>';
					}
					$html .= 
					'<label for="language-select">' . $this->msg( 'loopsettings-language-label' ) . '</label>
					<select class="form-control w-50" name="language-select" id="language-select" selected="'. $currentLoopSettings->languageCode .'">' . $languageOptions . '</select>
					<br>';
				
				$html .= '</div>'; // end of general-tab
				
				/** 
				 * APPEARANCE TAB 
				 */	
				$html .= '<div class="tab-pane fade" id="nav-appearance" role="tabpanel" aria-labelledby="nav-appearance-tab">';
					### SKIN BLOCK ###
					$html .= '<h3>' . $this->msg( 'loopsettings-headline-skinstyle' ) . '</h3>'; 
					//echo $currentLoopSettings->skinStyle;
					$skinStyleOptions = '';
					foreach( $wgSkinStyles as $style ) { 
					if ( $style == $currentLoopSettings->skinStyle ) { #TODO: Some styles should not be selectable from every loop
							$selected = 'selected';
						} else {
							$selected = '';
						}
						$skinStyleOptions .= '<option value="' . $style.'" ' . $selected.'>'. $this->msg( 'loop-skinstyle-'. $style ) .'</option>';
					}
					$html .= 
					'<label for="skin-style">' . $this->msg( 'loopsettings-skin-style-label' ) . '</label>
					<select class="form-control" name="skin-style" id="skin-style" selected="'. $currentLoopSettings->skinStyle .' ">' . $skinStyleOptions . '</select><br>';
					
					### LOGO BLOCK ###
					$html .= '<h3>' . $this->msg( 'loopsettings-headline-logo' ) . '</h3>';
					# logo
					
					$html .= 
					'<div class="form-row">
						<div class="col-9 col-sm-6 pl-1">
							<input type="checkbox" name="logo-use-custom" id="logo-use-custom" value="useCustomLogo" '. ( ! empty( $currentLoopSettings->customLogo ) ? 'checked' : '' ) .'>
							<label for="logo-use-custom">' . $this->msg( 'loopsettings-customlogo-label' ) . '</label>
							<input '. ( empty( $currentLoopSettings->customLogo ) ? 'disabled' : '' ) .' name="custom-logo-filename" placeholder="Logo.png" id="custom-logo-filename" class="form-control setting-input" value="' . $currentLoopSettings->customLogoFileName.'">
							<div class="invalid-feedback">' . $this->msg( 'loopsettings-customlogo-hint' ) . '</div>
							<input type="hidden" name="custom-logo-filepath" id="custom-logo-filepath" value="' . $currentLoopSettings->customLogoFilePath.'">
						</div>
						<div class="col-3 col-sm-6">';
							if( $currentLoopSettings->customLogo == "useCustomLogo" && ! empty( $currentLoopSettings->customLogoFilePath ) ) {
								$html .= "<p class='mb-1 mr-2'>" . $this->msg( 'Prefs-preview' ) . ' ' . $currentLoopSettings->customLogoFileName.":</p>
								<img src='" . $currentLoopSettings->customLogoFilePath."' style='max-width:100%; max-height: 50px;'></img>";
							}
							$html .= '</div>
						</div>
						<br>';
					#upload
					$html .= $uploadButton;
					
				$html .= '</div>'; // end of appearence-tab
				
				/** 
				 * FOOTER TAB 
				 */	
				$html .= '<div class="tab-pane fade" id="nav-footer" role="tabpanel" aria-labelledby="nav-footer-tab">';
				
					### EXTRA FOOTER BLOCK ###
					$html .= '<h3>' . $this->msg( 'loopsettings-headline-extrafooter' ) . '</h3>';
					
					# extra footer
					$html .= '<input type="checkbox" name="extra-footer-active" id="extra-footer-active" value="useExtraFooter" ' . ( ! empty ( $currentLoopSettings->extraFooter ) ? 'checked' : '' ) .'>
						<label for="extra-footer-active">' . $this->msg( 'loopsettings-extra-footer-label' ) . '</label><br>';
					$html .= $linkRenderer->makeLink(
						Title::newFromText("MediaWiki:ExtraFooter"),
						new HtmlArmor( $this->msg("loopsettings-extra-footer-linktext") ),
						array("target" => "blank")
					);
					$html .= "<br><br>";

					### LINK BLOCK ###
					$html .= '<h3>' . $this->msg( 'loopsettings-headline-footer-links' ) . '</h3>';
					
					#oncampus link
					$html .= '<input type="checkbox" name="oncampus-link" id="oncampus-link" value="showOncampusLink" ' . ( ! empty ( $currentLoopSettings->oncampusLink ) ? 'checked' : '' ) .'>
						<label for="oncampus-link">' . $this->msg( 'loopsettings-oncampus-label' ) . '</label><br>';
						
					#footer-social
					$socialArray = array(
						'Facebook' => array( 'icon' => $currentLoopSettings->facebookIcon, 'link' => $currentLoopSettings->facebookLink ),
						'Twitter' => array( 'icon' => $currentLoopSettings->twitterIcon, 'link' => $currentLoopSettings->twitterLink ),
						'Youtube' => array( 'icon' => $currentLoopSettings->youtubeIcon, 'link' => $currentLoopSettings->youtubeLink ),
						'Github' => array( 'icon' => $currentLoopSettings->githubIcon, 'link' => $currentLoopSettings->githubLink ), 
						'Instagram' => array( 'icon' => $currentLoopSettings->instagramIcon, 'link' => $currentLoopSettings->instagramLink )
					);
					foreach( $wgSocialIcons as $socialIcons => $socialIcon ) {
					
						$html .= '
						
							<input type="checkbox" name="footer-'. $socialIcons .'-icon" id="footer-'. $socialIcons .'-icon" value="'. $socialIcons .'" '. ( ! empty ( $socialArray[$socialIcons]['icon']) ? 'checked' : '' ) .'>
								
							<label for="footer-' . $socialIcons .'-icon"><span class="ic ic-social-' . strtolower( $socialIcons ) . '"></span>
							'. $socialIcons . ' ' . $this->msg( 'loopsettings-link-icon-label' ) . '</label>
							
							<div class="input-group mb-3">
								<input type="url" ' . ( empty( $socialArray[$socialIcons]['icon'] ) ? 'disabled' : '' ) . ' name="footer-'. $socialIcons .'-url" placeholder="https://www.'. strtolower( $socialIcons) .'.com/" id="footer-'. $socialIcons .'-url" class="setting-input form-control" value="'. $socialArray[$socialIcons]['link'] .'">
								<div class="invalid-feedback" id="feedback-'. $socialIcons .'">' . $this->msg( 'loopsettings-url-hint' ) . '</div>
							</div>';
					} 
				$html .= '</div>'; // end of footer-tab

				
				/** 
				 * CONTENT TAB 
				 */	
				$html .= '<div class="tab-pane fade" id="nav-content" role="tabpanel" aria-labelledby="nav-content-tab">';

                   # $html .= '<div class="form-row">';
					$html .= '<h3>' . $this->msg( 'loopsettings-numbering' ) . '</h3>';

					$html .= '<input type="checkbox" name="numbering-objects" id="numbering-objects" value="numberingObjects" ' . ( $currentLoopSettings->numberingObjects == true ? 'checked' : '' ) .'>
					<label for="numbering-objects">' . $this->msg( 'loopsettings-numbering-objects-label' ) . '</label><br><br>';

					$html .= '<input type="radio" name="numbering-type" id="ongoing" value="ongoing" ' . ( $currentLoopSettings->numberingType == "ongoing" ? 'checked' : '' ) .'>
					<label for="ongoing">' . $this->msg( 'loopsettings-numbering-type-ongoing-label' ) . '</label><br>';

					$html .= '<input type="radio" name="numbering-type" id="chapter" value="chapter" ' . ( $currentLoopSettings->numberingType == "chapter" ? 'checked' : '' ) .'>
					<label for="chapter">' . $this->msg( 'loopsettings-numbering-type-chapter-label' ) . '</label><br>';
                    $html .= '<br>';

                    #$html .= '<div class="form-row">';
                    $html .= '<h3>' . $this->msg( "loopsettings-citation-style" ) . '</h3>';
                    $html .= '<input type="radio" name="citation-style" id="harvard" value="harvard" ' . ( $currentLoopSettings->citationStyle == "harvard" ? 'checked' : '' ) .'>
					<label for="harvard">' . $this->msg( 'loopsettings-citation-style-harvard-label' ) . '</label><br>';

					$html .= '<input type="radio" name="citation-style" id="vancouver" value="vancouver" ' . ( $currentLoopSettings->citationStyle == "vancouver" ? 'checked' : '' ) .'>
					<label for="vancouver">' . $this->msg( 'loopsettings-citation-style-vancouver-label' ) . '</label><br>';
                   
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