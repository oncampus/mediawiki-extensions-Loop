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
			//var_dump( $request);
			
 			$out->addModules( 'ext.loop-settings.js' );
			$out->setPageTitle( $this->msg( 'loopsettings-specialpage-title' ) );
			
			//$loopSettings = new LoopSettings;
//$dbLoopSettings = new LoopSettings;
			//$loopSettings->addToDatabase();
			
			//echo $loopSettings->imprintLink;
			//var_dump($loopSettings);

			$errors = array();	
			$requestToken = $request->getText( 't' );

			$uploadButton = $this->msg( 'loopsettings-upload-hint' ) . " " . Linker::link( new TitleValue( NS_SPECIAL, 'ListFiles' ), $this->getSkin()->msg ( 'listfiles' ) ) . '<button type="button" class="upload-button mw-htmlform-submit mw-ui-button mt-2 d-block">' . $this->msg( 'upload' ) . '</button><br>';
			
			$currentLoopSettings = new LoopSettings();
			
			if( ! empty( $requestToken ) ) {

				if( $user->matchEditToken( $requestToken, $wgSecretKey, $request ) ) {
				
				//$currentLoopSettings = $newLoopSettings;
				//$dbLoopSettings = ;
				$currentLoopSettings->getLoopSettingsFromRequest( $request );
				//echo " newsettings";
				
				if ( empty ( $errors ) ) {
					
					$html .= '<div class="alert alert-success" role="alert">' . $this->msg( 'loopsettings-save-success' ) . '</div>';

				} else {
					$errorMsgs = '';
					foreach( $errors as $error ) { 
						
						$errorMsgs .= $error . '<br>';
						
					}
					$html .= '<div class="alert alert-danger" role="alert">' . $errorMsgs.'</div>';
					
				}
				
				$cache_button = '<button type="button" class="mw-htmlform-submit mw-ui-button mt-2 d-block">' . $this->msg( 'purgecache' ) . '</button><br>';
				$cache_link = Linker::link( new TitleValue( NS_SPECIAL, 'PurgeCache' ), $cache_button ); 
				$html .= $cache_link;
				
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
							<input type="text" pattern="' . $inputPatternImprintPrivacy.'" required name="imprint-link" placeholder="URL" id="imprint-link" class="setting-input form-control" value="'. $currentLoopSettings->imprintLink .'">
							<div class="invalid-feedback">' . $this->msg( 'loopsettings-url-imprint-privacy-hint' ) . '</div>
						</div>';
						# privacy link
						$html .= 
						'<div class="col-12 col-sm-6">
							<label for="privacy-link">' . $this->msg( 'loopsettings-privacy-label' ) . '</label>
							<input type="text" pattern="' . $inputPatternImprintPrivacy.'" required name="privacy-link" placeholder="URL" id="privacy-link" class="setting-input form-control" value="'. $currentLoopSettings->privacyLink .'">
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
							<input type="text" pattern="([-a-zA-Z0-9äöüØøAÖÜß:_\/\(\)©æÅÆç&!é\?,\.'."'".')]{0,})"' . ' placeholder="'. $this->msg( 'loopsettings-rights-text-placeholder' ) .'" name="rights-text" id="rights-text" class="setting-input form-control" value=' . '"' . $currentLoopSettings->rightsText.'"' . '>
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
					<select class="form-control" '. ( empty( $currentLoopSettings->skinStyle ) ? 'disabled' : '' ) .' name="skin-style" id="skin-style" selected="'. $currentLoopSettings->skinStyle .' ">' . $skinStyleOptions . '</select><br>';
					
					### LOGO BLOCK ###
					$html .= '<h3>' . $this->msg( 'loopsettings-headline-logo' ) . '</h3>';
					# logo
					
					$html .= 
					'<div class="form-row">
						<div class="col-9 col-sm-6 pl-1">
							<input type="checkbox" name="logo-use-custom" id="logo-use-custom" value="useCustomLogo" '. ( ! empty( $currentLoopSettings->customLogo ) ? 'checked' : '' ) .'>
							<label for="logo-use-custom">' . $this->msg( 'loopsettings-customlogo-label' ) . '</label>
							<input '. ( empty( $currentLoopSettings->customLogo ) ? 'disabled' : '' ) .' name="custom-logo-filename" placeholder="Logo.png" pattern="[-a-zA-Z_\.()0-9äöüßÄÖÜ]{1,}[\.]{1}[a-zA-Z]{3,}" id="custom-logo-filename" class="form-control setting-input" value="' . $currentLoopSettings->customLogoFileName.'">
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
						<label for="extra-footer-active">' . $this->msg( 'loopsettings-extra-footer-label' ) . '</label><br>
						<textarea rows="2" ' . ( empty( $currentLoopSettings->extraFooter ) ? 'disabled' : '' ) . ' name="extra-footer-wikitext" placeholder="' . $this->msg( 'loopsettings-extra-footer-placeholder' ) . '" id="extra-footer-wikitext" class="form-control mw-editfont-monospace setting-input">' . $currentLoopSettings->extraFooterWikitext.'</textarea><br>';
					
					# upload
					$html .= $uploadButton;
					
					### LINK BLOCK ###
					$html .= '<h3>' . $this->msg( 'loopsettings-headline-footer-links' ) . '</h3>';
					
					#oncampus link
					$html .= '<input type="checkbox" name="oncampus-link" id="oncampus-link" value="showOncampusLink" ' . ( ! empty ( $currentLoopSettings->oncampusLink ) ? 'checked' : '' ) .'>
						<label for="oncampus-link">' . $this->msg( 'loopsettings-oncampus-label' ) . '</label><br>';
					/*
					#footer-social
					#foreach( $wgSocialIcons as $socialIcons => $socialIcon ) {
					#	$html .= '<input type="checkbox" name="footer-'. $socialIcons .'-icon" id="footer-'. $socialIcons .'-icon" value="'. $socialIcons .'" '. ( ! empty ( $currentLoopSettings->socialIcons'][$socialIcons]['icon']) ? 'checked' : '' ) .'>
					#		<label for="footer-' . $socialIcons .'-icon"><span class="ic ic-social-' . strtolower( $socialIcons ) . '"></span>
					#		'. $socialIcons . ' ' . $this->msg( 'loopsettings-link-icon-label' ) . '</label>
					#		<input type="url" ' . ( empty( $currentLoopSettings->socialIcons'][$socialIcons]['icon'] ) ? 'disabled' : '' ) . ' name="footer-'. $socialIcons .'-url" placeholder="https://www.'. strtolower( $socialIcons) .'.com/" id="footer-'. $socialIcons .'-url" class="setting-input form-control" value="'. $currentLoopSettings->socialIcons'][$socialIcons]['link'] .'">
					#		<div class="invalid-feedback">' . $this->msg( 'loopsettings-url-hint' ) . '</div>
					#		<br>';
					} */
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