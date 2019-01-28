<?php

class SpecialLoopSettings extends SpecialPage {
	
	function __construct() {
		parent::__construct( 'LoopSettings' );
	}

	function execute( $sub ) {
		
		$user = $this->getUser();
		$out = $this->getOutput();
		$html = '<h1 id="loopsettings-h1">' . $this->msg( 'loopsettings-specialpage-title' ) . '</h1>';
		
		if ( $user->isAllowed( 'loop-settings-edit' ) ) {
			
			global $IP, $wgSecretKey, $wgSocialIcons, $wgAvailableLicenses, $wgSpecialPages, 
			$wgSkinStyles, $wgLanguageCode, $wgSupportedLoopLanguages;
				
			$this->setHeaders();
			$out = $this->getOutput();
			$request = $this->getRequest();
			
 			$out->addModules( 'ext.loop-settings.js' );
			$out->setPageTitle( $this->msg( 'loopsettings-specialpage-title' ) );
			
				
			$errors = array();	
			$requestToken = $request->getText( 't' );
			$fileLoopSettings = $this->getLoopSettingsFromFile();
			$newLoopSettings = $this->getLoopSettingsFromRequest( $request, $fileLoopSettings);
			$uploadButton = $this->msg( 'loopsettings-upload-hint' ) . " " . Linker::link( new TitleValue( NS_SPECIAL, 'ListFiles' ), $this->getSkin()->msg ( 'listfiles' ) ) . '<button type="button" class="upload-button mw-htmlform-submit mw-ui-button mt-2 d-block">' . $this->msg( 'upload' ) . '</button><br>';
			
			if( ! empty( $requestToken ) ) {
				if( $user->matchEditToken( $requestToken, $wgSecretKey, $request ) ) {
# unusual formatting for writing a file					
$newFileContent = 
'<?php
$wgSocialIcons = ' . var_export( $newLoopSettings['socialIcons'], true) . ';
$wgExtraFooter = ' . var_export( $newLoopSettings['extraFooter'], true) . ';
$wgHiddenPrefs["LoopSkinStyle"] = "' . htmlspecialchars( $newLoopSettings['skinStyle']) . '";
$wgImprintLink = "' . htmlspecialchars( $newLoopSettings['imprintLink']) . '";
$wgPrivacyLink = "' . htmlspecialchars( $newLoopSettings['privacyLink']) . '";
$wgOncampusLink = "' . htmlspecialchars( $newLoopSettings['oncampusLink']) . '";

$wgRightsText = "' . htmlspecialchars( $newLoopSettings['rightsText']) . '";
$wgRightsType = "' . htmlspecialchars( $newLoopSettings['rightsType']) . '";
$wgRightsUrl = "' . htmlspecialchars( $newLoopSettings['rightsUrl']) . '";
$wgRightsIcon = "' . htmlspecialchars( $newLoopSettings['rightsIcon']) . '";
$wgCustomLogo = '. var_export( $newLoopSettings['customLogo'], true) .';
$wgLanguageCode = "'.htmlspecialchars( $newLoopSettings['language']) . '";
';
				$servername = $_SERVER['SERVER_NAME'];
				$settingsFilePath = $IP . '/LocalSettings/LocalSettings_' . $servername . '.php';
				
				$settingsFile = fopen( $settingsFilePath, 'w' ) or die( "can't write settings" );
				fwrite( $settingsFile, $newFileContent);
				fclose( $settingsFile);
				
				$currentLoopSettings = $newLoopSettings;
				
				if ( empty ( $newLoopSettings['errors'] ) ) {
					
					$html .= '<div class="alert alert-success" role="alert">' . $this->msg( 'loopsettings-save-success' ) . '</div>';

				} else {
					$errorMsgs = '';
					foreach( $newLoopSettings['errors'] as $error ) { 
						
						$errorMsgs .= $error . '<br>';
						
					}
					$html .= '<div class="alert alert-danger" role="alert">' . $errorMsgs.'</div>';
					
				}
				
				$cache_button = '<button type="button" class="mw-htmlform-submit mw-ui-button mt-2 d-block">' . $this->msg( 'purgecache' ) . '</button><br>';
				$cache_link = Linker::link( new TitleValue( NS_SPECIAL, 'PurgeCache' ), $cache_button ); 
				$html .= $cache_link;
				
			} else {
				$currentLoopSettings = $fileLoopSettings;
			}
			
		} else {
			$currentLoopSettings = $fileLoopSettings;
		}
		
		$saltedToken = $user->getEditToken( $wgSecretKey, $request );
			
			$html .= 
			'<nav>
				<div class="nav nav-tabs" id="nav-tab" role="tablist"> 
					<a class="nav-item nav-link active" id="nav-general-tab" data-toggle="tab" href="#nav-general" role="tab" aria-controls="nav-general" aria-selected="true">' . $this->msg( 'loopsettings-tab-general' ) . '</a>
					<a class="nav-item nav-link" id="nav-appearance-tab" data-toggle="tab" href="#nav-appearance" role="tab" aria-controls="nav-appearance" aria-selected="true">' . $this->msg( 'loopsettings-tab-appearance' ) . '</a>
					<a class="nav-item nav-link" id="nav-footer-tab" data-toggle="tab" href="#nav-footer" role="tab" aria-controls="nav-footer" aria-selected="true">' . $this->msg( 'loopsettings-tab-footer' ) . '</a>
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
						$inputPatternImprintPrivacy = '([Hh]{1}[Tt]{2}[Pp]{1}[Ss]{0,1}[:]{1}[/]{2}[-a-zA-Z0-9äöØåæÅÆøüÄÖÜß%&?=_:./()\[\]]{1,})|([\/]{1}[Ll]{1}[Oo]{2}[Pp]{1}[\/]{1}[-a-zA-Z0-9ØåæÅÆøäöüÄÖÜß_:.\/()\[\]]{1,})';
						# imprint link
						$html .= 
						'<div class="col-12 col-sm-6">
							<label for="imprint-link">' . $this->msg( 'loopsettings-imprint-label' ) . '</label>
							<input type="text" pattern="' . $inputPatternImprintPrivacy.'" required name="imprint-link" placeholder="URL" id="imprint-link" class="setting-input form-control" value="'. $currentLoopSettings["imprintLink"] .'">
							<div class="invalid-feedback">' . $this->msg( 'loopsettings-url-imprint-privacy-hint' ) . '</div>
						</div>';
						# privacy link
						$html .= 
						'<div class="col-12 col-sm-6">
							<label for="privacy-link">' . $this->msg( 'loopsettings-privacy-label' ) . '</label>
							<input type="text" pattern="' . $inputPatternImprintPrivacy.'" required name="privacy-link" placeholder="URL" id="privacy-link" class="setting-input form-control" value="'. $currentLoopSettings["privacyLink"] .'">
							<div class="invalid-feedback">' . $this->msg( 'loopsettings-url-imprint-privacy-hint' ) . '</div>
						</div>';
					
					$html .= '</div><br>';
					
					### LICENSE BLOCK ###
					$html .= '<h3>' . $this->msg( 'loopsettings-headline-license' ) . '</h3>';
					
					# cc-license
					$licenseOptions = '';
					foreach( $wgAvailableLicenses as $license => $option ) { 
						if ( $license == $currentLoopSettings['rightsType'] ) {
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
							<input type='checkbox' name='license-use-cc' id='license-use-cc' value='licenseUseCC' ". ( ! empty( $currentLoopSettings['rightsType'] ) ? 'checked' : '' ) .">
							<label for='license-use-cc'>" . $this->msg( 'loopsettings-use-cc-label' ) . "</label>
							<select class='form-control' ". ( empty( $currentLoopSettings['rightsType'] ) ? 'disabled' : '' ) ." name='rights-type' id='rights-type' selected='". $currentLoopSettings['rightsType'] ." '>
							" . $licenseOptions . "</select>
						</div>";
					
					# license text
					$html .= 
						'<div class="col-12 col-sm-6">
							<label for="rights-text">' . $this->msg( 'loopsettings-rights-label' ) . '</label>
							<input type="text" pattern="([-a-zA-Z0-9äöüØøAÖÜß:_\/\(\)©æÅÆç&!é\?,\.'."'".')]{0,})"' . ' placeholder="'. $this->msg( 'loopsettings-rights-text-placeholder' ) .'" name="rights-text" id="rights-text" class="setting-input form-control" value=' . '"' . $currentLoopSettings['rightsText'].'"' . '>
							<div class="invalid-feedback">' . $this->msg( 'loopsettings-rights-text-hint' ) . " ©,:._-!?&/()'</div>" .
						'</div>
					</div><br>';
					
					### LANGUAGE BLOCK ###
					$html .= '<h3>' . $this->msg( 'loopsettings-headline-language' ) . '</h3>'; 
					
					$languageOptions = '';
					foreach( $wgSupportedLoopLanguages as $language ) { 
						if ( $language == $currentLoopSettings['language'] ) { 
							$selected = 'selected';
						} else {
							$selected = '';
						}
						
						$languageOptions .= '<option value="' . $language.'" ' . $selected.'>'. $language .'</option>';
					}
					$html .= 
					'<label for="language-select">' . $this->msg( 'loopsettings-language-label' ) . '</label>
					<select class="form-control w-50" name="language-select" id="language-select" selected="'. $currentLoopSettings['language'] .'">' . $languageOptions . '</select>
					<br>';
				
				$html .= '</div>'; // end of general-tab
				
				/** 
				 * APPEARANCE TAB 
				 */	
				$html .= '<div class="tab-pane fade" id="nav-appearance" role="tabpanel" aria-labelledby="nav-appearance-tab">';
					### SKIN BLOCK ###
					$html .= '<h3>' . $this->msg( 'loopsettings-headline-skinstyle' ) . '</h3>'; 
					
					$skinStyleOptions = '';
					foreach( $wgSkinStyles as $style ) { 
					if ( $style == $currentLoopSettings['skinStyle'] ) { #TODO: Some styles should not be selectable from every loop
							$selected = 'selected';
						} else {
							$selected = '';
						}
						$skinStyleOptions .= '<option value="' . $style.'" ' . $selected.'>'. $this->msg( 'loop-skinstyle-'. $style ) .'</option>';
					}
					$html .= 
					'<label for="skin-style">' . $this->msg( 'loopsettings-skin-style-label' ) . '</label>
					<select class="form-control" '. ( empty( $currentLoopSettings['skinStyle'] ) ? 'disabled' : '' ) .' name="skin-style" id="skin-style" selected="'. $currentLoopSettings['skinStyle'] .' ">' . $skinStyleOptions . '</select><br>';
					
					### LOGO BLOCK ###
					$html .= '<h3>' . $this->msg( 'loopsettings-headline-logo' ) . '</h3>';
					# logo
					
					$html .= 
					'<div class="form-row">
						<div class="col-9 col-sm-6 pl-1">
							<input type="checkbox" name="logo-use-custom" id="logo-use-custom" value="useCustomLogo" '. ( ! empty( $currentLoopSettings['customLogo']['useCustomLogo'] ) ? 'checked' : '' ) .'>
							<label for="logo-use-custom">' . $this->msg( 'loopsettings-customlogo-label' ) . '</label>
							<input '. ( empty( $currentLoopSettings['customLogo']['useCustomLogo'] ) ? 'disabled' : '' ) .' name="custom-logo-filename" placeholder="Logo.png" pattern="[-a-zA-Z_\.()0-9äöüßÄÖÜ]{1,}[\.]{1}[a-zA-Z]{3,}" id="custom-logo-filename" class="form-control setting-input" value="' . $currentLoopSettings['customLogo']['customFileName'].'">
							<div class="invalid-feedback">' . $this->msg( 'loopsettings-customlogo-hint' ) . '</div>
							<input type="hidden" name="custom-logo-filepath" id="custom-logo-filepath" value="' . $currentLoopSettings['customLogo']['customFilePath'].'">
						</div>
						<div class="col-3 col-sm-6">';
							if( $currentLoopSettings['customLogo']['useCustomLogo'] == "useCustomLogo" && ! empty( $currentLoopSettings['customLogo']['customFilePath'] ) ) {
								$html .= "<p class='mb-1 mr-2'>" . $this->msg( 'Prefs-preview' ) . ' ' . $currentLoopSettings['customLogo']['customFileName'].":</p>
								<img src='" . $currentLoopSettings['customLogo']['customFilePath']."' style='max-width:100%; max-height: 50px;'></img>";
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
					$html .= '<input type="checkbox" name="extra-footer-active" id="extra-footer-active" value="useExtraFooter" ' . ( ! empty ( $currentLoopSettings['extraFooter']['useExtraFooter'] ) ? 'checked' : '' ) .'>
						<label for="extra-footer-active">' . $this->msg( 'loopsettings-extra-footer-label' ) . '</label><br>
						<textarea rows="2" ' . ( empty( $currentLoopSettings['extraFooter']['useExtraFooter'] ) ? 'disabled' : '' ) . ' name="extra-footer-wikitext" placeholder="' . $this->msg( 'loopsettings-extra-footer-placeholder' ) . '" id="extra-footer-wikitext" class="form-control mw-editfont-monospace setting-input">' . $currentLoopSettings['extraFooter']['wikiText'].'</textarea><br>';
					
					# upload
					$html .= $uploadButton;
					
					### LINK BLOCK ###
					$html .= '<h3>' . $this->msg( 'loopsettings-headline-footer-links' ) . '</h3>';
					
					#oncampus link
					$html .= '<input type="checkbox" name="oncampus-link" id="oncampus-link" value="showOncampusLink" ' . ( ! empty ( $currentLoopSettings['oncampusLink'] ) ? 'checked' : '' ) .'>
						<label for="oncampus-link">' . $this->msg( 'loopsettings-oncampus-label' ) . '</label><br>';
					
					#footer-social
					foreach( $wgSocialIcons as $socialIcons => $socialIcon ) {
						$html .= '<input type="checkbox" name="footer-'. $socialIcons .'-icon" id="footer-'. $socialIcons .'-icon" value="'. $socialIcons .'" '. ( ! empty ( $currentLoopSettings['socialIcons'][$socialIcons]['icon']) ? 'checked' : '' ) .'>
							<label for="footer-' . $socialIcons .'-icon"><span class="ic ic-social-' . strtolower( $socialIcons ) . '"></span>
							'. $socialIcons . ' ' . $this->msg( 'loopsettings-link-icon-label' ) . '</label>
							<input type="url" ' . ( empty( $currentLoopSettings['socialIcons'][$socialIcons]['icon'] ) ? 'disabled' : '' ) . ' name="footer-'. $socialIcons .'-url" placeholder="https://www.'. strtolower( $socialIcons) .'.com/" id="footer-'. $socialIcons .'-url" class="setting-input form-control" value="'. $currentLoopSettings['socialIcons'][$socialIcons]['url'] .'">
							<div class="invalid-feedback">' . $this->msg( 'loopsettings-url-hint' ) . '</div>
							<br>';
					} 
				$html .= '</div>'; // end of footer-tab
				
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
	 * Puts request content into array
	 *
	 * @param Request $request 
	 * @return Array $newLoopSettings
	 */
	private function getLoopSettingsFromRequest ( $request, $previousSettings ) {
		
		global $wgSocialIcons, $wgSkinStyles, $wgAvailableLicenses, $wgSupportedLoopLanguages;
		
		$newLoopSettings = array(
			'errors' =>  array()
		);
	
		if ( empty ( $request->getText( 'rights-text' ) ) || preg_match( "/([-a-zA-Z0-9äöüAÖÜß:_\/\(\)©åæÅÆç&!é\?,\.')]{0,})/", $request->getText( 'rights-text' ) ) ) {
			$newLoopSettings['rightsText'] = $request->getText( 'rights-text' );
		} else {
			$newLoopSettings['rightsText'] = $previousSettings['rightsText'];
			array_push( $newLoopSettings['errors'], $this->msg( 'loopsettings-error' )  . ': ' . $this->msg( 'loopsettings-rights-label' ) );
		}
		
		foreach( $wgSocialIcons as $socialIcons => $socialIcon ) {
			
			if ( empty( $request->getText( 'footer-' . $socialIcons . '-icon' ) ) || $request->getText( 'footer-' . $socialIcons . '-icon' ) == $socialIcons ) {
				$newLoopSettings['socialIcons'][$socialIcons]['icon'] = $request->getText( 'footer-' . $socialIcons . '-icon' );
				
				if ( ! empty( $request->getText( 'footer-' . $socialIcons . '-icon' ) && filter_var( $request->getText( 'footer-' . $socialIcons . '-url' ), FILTER_VALIDATE_URL ) ) ) {
					$newLoopSettings['socialIcons'][$socialIcons]['url'] = $request->getText( 'footer-' . $socialIcons . '-url' );
				} else {
					$newLoopSettings['socialIcons'][$socialIcons]['url'] = '';
				}
			} else {
				$newLoopSettings['socialIcons'][$socialIcons]['icon'] = $previousSettings['socialIcons'][$socialIcons]['icon'];
				$newLoopSettings['socialIcons'][$socialIcons]['url'] = $previousSettings['socialIcons'][$socialIcons]['url'];
				array_push( $newLoopSettings['errors'], $this->msg( 'loopsettings-error' )  . ': ' . $socialIcons );
			}	
			
		}
		
		$regExLoopLink = '/([\/]{1}[Ll]{1}[Oo]{2}[Pp]{1}[\/]{1}[-a-zA-Z0-9äöüØåæÅÆøÄÖÜß_:.\/()\[\]]{1,})/';
		
		if ( filter_var( $request->getText( 'privacy-link' ), FILTER_VALIDATE_URL ) || preg_match( $regExLoopLink, $request->getText( 'privacy-link' ) ) ) {
			$newLoopSettings['privacyLink'] = $request->getText( 'privacy-link' );
		} else {
			$newLoopSettings['privacyLink'] = $previousSettings['privacyLink'];
			array_push( $newLoopSettings['errors'], $this->msg( 'loopsettings-error' )  . ': ' . $this->msg( 'loopsettings-privacy-label' ) );
		}
		
		if ( filter_var( $request->getText( 'imprint-link' ), FILTER_VALIDATE_URL ) || preg_match( $regExLoopLink, $request->getText( 'imprint-link' ) ) ) {
			$newLoopSettings['imprintLink'] = $request->getText( 'imprint-link' );
		} else {
			$newLoopSettings['imprintLink'] = $previousSettings['imprintLink'];
			array_push( $newLoopSettings['errors'], $this->msg( 'loopsettings-error' )  . ': ' . $this->msg( 'loopsettings-imprint-label' ) );
		}
		
		if ( empty ( $request->getText( 'oncampus-link' ) ) || $request->getText( 'oncampus-link' ) == 'showOncampusLink' ) {
			$newLoopSettings['oncampusLink'] = $request->getText( 'oncampus-link' );
		} else {
			$newLoopSettings['oncampusLink'] = $previousSettings['oncampusLink'];
			array_push( $newLoopSettings['errors'], $this->msg( 'loopsettings-error' )  . ': ' . $this->msg( 'loopsettings-oncampus-label' ) );
		}
		
		if ( empty ( $request->getText( 'rights-type' ) ) || isset ( $wgAvailableLicenses[$request->getText( 'rights-type' )] ) ) {
			$newLoopSettings['rightsType'] = $request->getText( 'rights-type' );
			$newLoopSettings['rightsIcon'] = $wgAvailableLicenses[$newLoopSettings['rightsType']]['icon'];
			$newLoopSettings['rightsUrl'] = $wgAvailableLicenses[$newLoopSettings['rightsType']]['url'];
		} else {
			$newLoopSettings['rightsType'] = $previousSettings['rightsType'];
			$newLoopSettings['rightsIcon'] = $previousSettings['rightsIcon'];
			$newLoopSettings['rightsUrl'] = $previousSettings['rightsUrl'];
			array_push( $newLoopSettings['errors'], $this->msg( 'loopsettings-error' )  . ': ' . $this->msg( 'loopsettings-use-cc-label' ) );
		}
		
		if ( in_array( $request->getText( 'skin-style' ), $wgSkinStyles ) ) {
			$newLoopSettings['skinStyle'] = $request->getText( 'skin-style' );
		} else {
			$newLoopSettings['skinStyle'] = $previousSettings['skinStyle'];
			array_push( $newLoopSettings['errors'], $this->msg( 'loopsettings-error' )  . ': ' . $this->msg( 'loopsettings-skin-style-label' ) );
		}
		
		if ( in_array( $request->getText( 'language-select' ), $wgSupportedLoopLanguages ) ) {
			$newLoopSettings['language'] = $request->getText( 'language-select' );
		} else {
			$newLoopSettings['language'] = $previousSettings['language'];
			array_push( $newLoopSettings['errors'], $this->msg( 'loopsettings-error' )  . ': ' . $this->msg( 'loopsettings-language-label' ) );
		}
		
		if ( empty ( $request->getText( 'extra-footer-active' ) ) || $request->getText( 'extra-footer-active' ) == 'useExtraFooter' ) {
				$newLoopSettings['extraFooter']['useExtraFooter'] = $request->getText( 'extra-footer-active' );
			if ( ! empty ( $request->getText( 'extra-footer-wikitext' ) ) ) {
				$newLoopSettings['extraFooter']['wikiText'] = $request->getText( 'extra-footer-wikitext' );
				$newLoopSettings['extraFooter']['parsedText'] = $this->parse( $newLoopSettings['extraFooter']['wikiText'] );
			}
		} else {
			$newLoopSettings['extraFooter']['useExtraFooter'] = $previousSettings['extraFooter']['useExtraFooter'];
			$newLoopSettings['extraFooter']['wikiText'] = $previousSettings['extraFooter']['useExtraFooter'];
			$newLoopSettings['extraFooter']['parsedText'] = $previousSettings['extraFooter']['parsedText'];
			array_push( $newLoopSettings['errors'], $this->msg( 'loopsettings-error' )  . ': ' . $this->msg( 'loopsettings-extra-footer-label' ) );
		}
		
		if ( empty( $request->getText( 'logo-use-custom' ) ) ) {
			$newLoopSettings['customLogo']['useCustomLogo'] = '';
			$newLoopSettings['customLogo']['customFileName'] = '';
			$newLoopSettings['customLogo']['customFilePath'] = '';
		} else if ( $request->getText( 'logo-use-custom' ) == 'useCustomLogo' ) {
			$newLoopSettings['customLogo']['useCustomLogo'] = 'useCustomLogo';
			if ( preg_match( '/[-a-zA-Z_\.\(\)0-9äöüØåæÅÆßÄÖÜØ]{1,}[\.]{1}[a-zA-Z]{3,}/', $request->getText( 'custom-logo-filename' ) ) ) {
				$newLoopSettings['customLogo']['customFileName'] = $request->getText( 'custom-logo-filename' );
				$tmpParsedResult = $this->parse( '{{filepath:' . $request->getText( 'custom-logo-filename' ) . '}}' );
				preg_match( '/href="(.*)"/', $tmpParsedResult, $tmpOutputArray);
				if ( isset ( $tmpOutputArray[1] ) ) {
					$newLoopSettings['customLogo']['customFilePath'] = $tmpOutputArray[1];
				} else {
					$newLoopSettings['customLogo']['useCustomLogo'] = $previousSettings['customLogo']['useCustomLogo'];
					$newLoopSettings['customLogo']['customFileName'] = $previousSettings['customLogo']['customFileName'];
					$newLoopSettings['customLogo']['customFilePath'] = $previousSettings['customLogo']['customFilePath'];
					array_push( $newLoopSettings['errors'], $this->msg( 'loopsettings-error-customlogo-notfound' ) );
				}
			} else {
				$newLoopSettings['customLogo']['useCustomLogo'] = $previousSettings['customLogo']['useCustomLogo'];
				$newLoopSettings['customLogo']['customFileName'] = $previousSettings['customLogo']['customFileName'];
				$newLoopSettings['customLogo']['customFilePath'] = $previousSettings['customLogo']['customFilePath'];
				array_push( $newLoopSettings['errors'], $this->msg( 'loopsettings-error' )  . ': ' . $this->msg( 'loopsettings-customlogo-label' ) );
			}
		} else {
			$newLoopSettings['customLogo']['useCustomLogo'] = $previousSettings['customLogo']['useCustomLogo'];
			$newLoopSettings['customLogo']['customFileName'] = $previousSettings['customLogo']['customFileName'];
			$newLoopSettings['customLogo']['customFilePath'] = $previousSettings['customLogo']['customFilePath'];
			array_push( $newLoopSettings['errors'], $this->msg( 'loopsettings-error' )  . ': ' . $this->msg( 'loopsettings-customlogo-label' ) );
		}
			
		
		return $newLoopSettings;
	
	}
	
	/**
	 * Parses custom content
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
	 * Load items from database
	 */
	public function loadSettings() {
	
		$dbr = wfGetDB( DB_SLAVE );
		
		$res = $dbr->select(
			'loop_settings',
			array(
				'lset_imprintlink',
				'lset_privacylink',
				'lset_oncampuslink',
				'lset_rightstext',
				'lset_rightstype',
				'lset_rightsurl',
				'lset_rightsicon',
				'lset_customlogo_use',
				'lset_customlogo_filename',
				'lset_customlogo_filepath',
				'lset_languagecode',
				'lset_soc_fb_icon',
				'lset_soc_fb_link',
				'lset_soc_tw_icon',
				'lset_soc_tw_link',
				'lset_soc_yt_icon',
				'lset_soc_yt_link',
				'lset_soc_gh_icon',
				'lset_soc_gh_link',
				'lset_soc_in_icon',
				'lset_soc_in_link'
			)
		);
	}

	/**
	 * Gets variables from Settings file and puts it into an Array
	 *
	 * @return Array
	 */
	private function getLoopSettingsFromFile () {
		
		global $wgRightsText, $wgRightsType, $wgRightsIcon, $wgRightsUrl, $wgSocialIcons, 
		$wgExtraFooter, $wgImprintLink, $wgPrivacyLink, $wgOncampusLink, $wgCustomLogo, 
		$wgSkinStyles, $wgHiddenPrefs, $wgLanguageCode;
		
		$fileLoopSettings = array(
			'rightsText' => htmlspecialchars_decode( $wgRightsText ),
			'rightsType' => htmlspecialchars_decode( $wgRightsType ),
			'rightsIcon' => htmlspecialchars_decode( $wgRightsIcon ),
			'rightsUrl' => htmlspecialchars_decode( $wgRightsUrl ),
			'imprintLink' => htmlspecialchars_decode( $wgImprintLink ),
			'privacyLink' => htmlspecialchars_decode( $wgPrivacyLink ),
			'oncampusLink' => htmlspecialchars_decode( $wgOncampusLink ),
			'skinStyle' => $wgHiddenPrefs['LoopSkinStyle'],
			'socialIcons' => $wgSocialIcons,
			'extraFooter' => $wgExtraFooter,
			'customLogo' => $wgCustomLogo,
			'language' => $wgLanguageCode
		);
		
		return $fileLoopSettings;
	
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