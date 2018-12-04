<?php

class SpecialLoopSettings extends SpecialPage {
	
	function __construct() {
		parent::__construct( 'LoopSettings' );
	}

	function execute( $sub ) {
		
		global $IP, $wgSecretKey, $wgSocialIcons, $wgAvailableLicenses, $wgSpecialPages, 
		$wgSkinStyles, $wgLanguageCode, $wgSupportedLoopLanguages;
		
		$user = $this->getUser();
		$this->setHeaders();
		$out = $this->getOutput();
		$request = $this->getRequest();
		
		$out->addModules("ext.loop-settings.js");
		$out->setPageTitle( $this->msg( 'loopsettings-specialpage-title' ) );
		$html = "";
		
		$html .= Html::openElement(
				'h1',
				array(
					'id' => 'loopsettings-h1'
				)
			)
			. $this->msg( 'loopsettings-specialpage-title' ) .
			Html::closeElement(
				'h1'
			);
			
		$errors = array();	
		$requestToken = $request->getText( 't' );
		$newLoopSettings = $this->getLoopSettingsFromRequest($request);
		$fileLoopSettings = $this->getLoopSettingsFromFile();
		
		if( ! empty( $requestToken )) {
			if( $user->matchEditToken( $requestToken, $wgSecretKey, $request )) {
				
				$newFileContent = '<?php
$wgSocialIcons = ' . var_export($newLoopSettings['socialIcons'], true) . ';
$wgExtraFooter = ' . var_export($newLoopSettings['extraFooter'], true) . ';
$wgHiddenPrefs["LoopSkinStyle"] = "' . $newLoopSettings['skinStyle'] . '";
$wgImprintLink = "' . htmlspecialchars($newLoopSettings['imprintLink']) . '";
$wgPrivacyLink = "' . htmlspecialchars($newLoopSettings['privacyLink']) . '";
$wgOncampusLink = "' . htmlspecialchars($newLoopSettings['oncampusLink']) . '";

$wgRightsText = "' . htmlspecialchars($newLoopSettings['rightsText']) . '";
$wgRightsType = "' . htmlspecialchars($newLoopSettings['rightsType']) . '";
$wgRightsUrl = "' . htmlspecialchars($newLoopSettings['rightsUrl']) . '";
$wgRightsIcon = "' . htmlspecialchars($newLoopSettings['rightsIcon']) . '";
$wgCustomLogo = '. var_export($newLoopSettings['customLogo'], true) .';
$wgLanguageCode = "'.$newLoopSettings['language'].'";


';

				$servername = $_SERVER["SERVER_NAME"];
				$settingsFilePath = $IP . "/LoopSettings/LoopSettings_" . $servername . ".php";
				
				$settingsFile = fopen($settingsFilePath, 'w') or die("can't write settings");
				fwrite($settingsFile, $newFileContent);
				fclose($settingsFile);
				
				$currentLoopSettings = $newLoopSettings;
				
				if ( empty ( $newLoopSettings['errors'] ) ) {
					$html .= Html::rawElement(
							'div',
							array(
								'class' => 'alert alert-success'
							),
							$this->msg( 'loopsettings-save-success' )
						);
				} else {
					$errorMsgs = "";
					foreach( $newLoopSettings['errors'] as $error ) { 
						$errorMsgs .= $error . "<br>";
					}
					$html .= Html::rawElement(
							'div',
							array(
								'class' => 'alert alert-danger'
							),
							$errorMsgs
						);
				}
				if ( isset ( $wgSpecialPages['PurgeCache'] ) ) {
					$cache_button = '<button type="button" class="mw-htmlform-submit mw-ui-button mt-2 d-block">' . $this->msg( "purgecache" ) . '</button><br>';
					$link = Linker::link( new TitleValue( NS_SPECIAL, 'PurgeCache' ), $cache_button ); 
					$html .= $link;
				} 
			} else {
				$currentLoopSettings = $fileLoopSettings;
			}
			
		} else {
			$currentLoopSettings = $fileLoopSettings;
		}
		
		$saltedToken = $user->getEditToken( $wgSecretKey, $request );
			
		
		if ( $user->isAllowed( 'loop-settings-edit' ) ) {
			$html .= "
			<nav>
				<div class='nav nav-tabs' id='nav-tab' role='tablist'> 
					<a class='nav-item nav-link active' id='nav-general-tab' data-toggle='tab' href='#nav-general' role='tab' aria-controls='nav-general' aria-selected='true'>".$this->msg('loopsettings-tab-general')."</a>
					<a class='nav-item nav-link' id='nav-appearance-tab' data-toggle='tab' href='#nav-appearance' role='tab' aria-controls='nav-appearance' aria-selected='true'>".$this->msg('loopsettings-tab-appearance')."</a>
					<a class='nav-item nav-link' id='nav-footer-tab' data-toggle='tab' href='#nav-footer' role='tab' aria-controls='nav-footer' aria-selected='true'>".$this->msg('loopsettings-tab-footer')."</a>
				</div>
			</nav>
			<form class='mw-editform mt-3 mb-3' id='loopsettings-form' method='post' enctype='multipart/form-data'>";
			 
			$html .= '<div class="tab-content" id="nav-tabContent">
			<div class="tab-pane fade show active" id="nav-general" role="tabpanel" aria-labelledby="nav-general-tab">';
			
			### LINK BLOCK ###
			$html .= '<h3>'.$this->msg('loopsettings-headline-footerlinks').'</h3>';
			# imprint link
			$html .= "<label for='imprint-link'>" . $this->msg( "loopsettings-imprint-label" ) . "</label>
				<input type='text' pattern='([htp]{4,5}[s]{0,1}[:/]{3}.[-a-zA-Z0-9äöüÄÖÜß%&?=_:./()\[\]]{1,})|([/lop]{6}.[-a-zA-Z0-9äöüÄÖÜß_:./()\[\]]{1,})' title='" . $this->msg( "loopsettings-url-imprint-privacy-hint" ) . "'  required name='imprint-link' placeholder='URL' id='imprint-link' class='ml-2 mt-1 setting-input' value='". $currentLoopSettings['imprintLink'] ."'><br>";
			# privacy link
			$html .= "<label for='privacy-link'>" . $this->msg( "loopsettings-privacy-label" ) . "</label>
				<input type='text' pattern='([htp]{4,5}[s]{0,1}[:/]{3}.[-a-zA-Z0-9äöüÄÖÜß%&?=_:./()\[\]]{1,})|([/lop]{6}.[-a-zA-Z0-9äöüÄÖÜß_:./()\[\]]{1,})' title='" . $this->msg( "loopsettings-url-imprint-privacy-hint" ) . "' required name='privacy-link' placeholder='URL' id='privacy-link' class='ml-2 mt-1 setting-input' value='". $currentLoopSettings['privacyLink'] ."'><br>";
			# oncampus link
			$html .= "<input type='checkbox' name='oncampus-link' id='oncampus-link' value='showOncampusLink' ". ( ! empty( $currentLoopSettings['oncampusLink'] ) ? "checked" : "" ) .">
				<label for='oncampus-link'>" . $this->msg( "loopsettings-oncampus-label" ) . "</label><br><br>";
			
			
			### LICENSE BLOCK ###
			$html .= '<h3>'.$this->msg('loopsettings-headline-license').'</h3>';
			# cc-license
			$licenseOptions = "";
			foreach( $wgAvailableLicenses as $license => $option ) { 
				if ( $license == $currentLoopSettings['rightsType'] ) {
					$selected = "selected";
				} else {
					$selected = "";
				}
				if ( $license == "" ) {
					$licenseText = $this->msg('Htmlform-chosen-placeholder');
				} else {
					$licenseText = $license;
				}
				
				$licenseOptions .= '<option value="'.$license.'" '.$selected.'>'.$licenseText.'</option>';
			}
			$html .= "<input type='checkbox' name='license-use-cc' id='license-use-cc' value='licenseUseCC' ". ( ! empty( $currentLoopSettings['rightsType'] ) ? "checked" : "" ) .">
				<label for='license-use-cc'>" . $this->msg( "loopsettings-use-cc-label" ) . "</label>
				<select ". ( empty( $currentLoopSettings['rightsType'] ) ? "disabled" : "" ) ." name='rights-type' id='rights-type' selected='". $currentLoopSettings['rightsType'] ." '>
					" . $licenseOptions . "</select><br>".
				"<input type='hidden' name='rights-url' id='rights-url' value='".$wgAvailableLicenses[ $currentLoopSettings['rightsType'] ]['url']."'>" . 
				"<input type='hidden' name='rights-icon' id='rights-icon' value='".$wgAvailableLicenses[ $currentLoopSettings['rightsType'] ]['icon']."'>";
			# copyright text
			$html .= "<label for='rights-text'>" . $this->msg( "loopsettings-rights-label" ) . "</label><input type='text' name='rights-text' id='rights-text' class='ml-2 mt-1 setting-input' value='".$currentLoopSettings['rightsText']."'><br><br>";	
			
			
			### LANGUAGE BLOCK ###
			$html .= '<h3>'.$this->msg('loopsettings-headline-language').'</h3>'; 
			
			$languageOptions = "";
			foreach( $wgSupportedLoopLanguages as $language ) { 
				if ( $language == $currentLoopSettings['language'] ) { 
					$selected = "selected";
				} else {
					$selected = "";
				}
				
				$languageOptions .= '<option value="'.$language.'" '.$selected.'>'. $language .'</option>';
			}
			$html .= "<label for='language-select'>" . $this->msg( "loopsettings-language-label" ) . "</label>
				<select name='language-select' id='language-select' selected='". $currentLoopSettings['language'] ." '>
					" . $languageOptions . "</select><br><br>";
			
			$html .= '</div>'; // end of general-tab
			
		### APPEARENCE TAB ###
			$html .= '<div class="tab-pane fade" id="nav-appearance" role="tabpanel" aria-labelledby="nav-appearance-tab">';
			### SKIN BLOCK ###
			$html .= '<h3>'.$this->msg('loopsettings-headline-skinstyle').'</h3>'; 
			
			$skinStyleOptions = "";
			foreach( $wgSkinStyles as $style ) { 
			if ( $style == $currentLoopSettings['skinStyle'] ) { #TODO: Some styles should not be selectable from every loop
					$selected = "selected";
				} else {
					$selected = "";
				}
				
				$skinStyleOptions .= '<option value="'.$style.'" '.$selected.'>'. $this->msg( "loop-skinstyle-". $style ) .'</option>';
			}
			$html .= "<label for='skin-style'>" . $this->msg( "loopsettings-skin-style-label" ) . "</label>
				<select ". ( empty( $currentLoopSettings['skinStyle'] ) ? "disabled" : "" ) ." name='skin-style' id='skin-style' selected='". $currentLoopSettings['skinStyle'] ." '>
					" . $skinStyleOptions . "</select><br><br>";
			
			### LOGO BLOCK ###
			$html .= '<h3>'.$this->msg('loopsettings-headline-logo').'</h3>';
			# logo
			$html .= "<input type='checkbox' name='logo-use-custom' id='logo-use-custom' value='useCustomLogo' ". ( ! empty( $currentLoopSettings['customLogo']['useCustomLogo'] ) ? "checked" : "" ) .">
				<label for='use-custom-logo'>" . $this->msg( 'loopsettings-customlogo-label' ) . "</label>
				<input ". ( empty( $currentLoopSettings['customLogo']['useCustomLogo'] ) ? "disabled" : "" ) ." name='custom-logo-filename' placeholder='Logo.png' title='".$this->msg( 'loopsettings-customlogo-hint' )."' pattern='[a-zA-Z-_.()0-9äöüßÄÖÜ]{1,}[.]{1}[a-zA-Z]{3,}' id='custom-logo-filename' class='setting-input' value='".$currentLoopSettings['customLogo']['customFileName']."'>
				<input type='hidden' name='custom-logo-filepath' id='custom-logo-filepath' value='".$currentLoopSettings['customLogo']['customFilePath']."'><br>";
			#upload
			$html .= $this->msg( "loopsettings-upload-hint" ) . '<button type="button" class="upload-button mw-htmlform-submit mw-ui-button mt-2 d-block">' . $this->msg( "upload" ) . '</button><br>';
			
			$html .= '</div>'; // end of appearence-tab
			
		### FOOTER TAB ###	
			$html .= '<div class="tab-pane fade" id="nav-footer" role="tabpanel" aria-labelledby="nav-footer-tab">';
			
			### EXTRA FOOTER BLOCK ###
			$html .= '<h3>'.$this->msg('loopsettings-headline-extrafooter').'</h3>';
			# extra footer
			$html .= "<input type='checkbox' name='extra-footer-active' id='extra-footer-active' value='useExtraFooter' ". ( ! empty( $currentLoopSettings['extraFooter']['useExtraFooter'] ) ? "checked" : "" ) .">
				<label for='extra-footer-active'>" . $this->msg( "loopsettings-extra-footer-label" ) . "</label><br>
				<textarea rows='2' " . ( empty( $currentLoopSettings['extraFooter']['useExtraFooter'] ) ? "disabled" : "" ) . " required name='extra-footer-wikitext' placeholder='" . $this->msg( "loopsettings-extra-footer-placeholder" ) . "' id='extra-footer-wikitext' class='ml-4 mr-3 mw-editfont-monospace ml-2 mt-1 setting-input'>".$currentLoopSettings[ 'extraFooter' ][ 'wikiText' ]."</textarea><br>";
			#upload
			$html .= $this->msg( "loopsettings-upload-hint" ) . '<button type="button" class="upload-button mw-htmlform-submit mw-ui-button mt-2 d-block">' . $this->msg( "upload" ) . '</button><br>';
			
			### SOCIAL BLOCK ###
			$html .= '<h3>'.$this->msg('loopsettings-headline-social-icons').'</h3>';
			#footer-social
			foreach( $wgSocialIcons as $socialIcons => $socialIcon ) {
				$html .= "<input type='checkbox' name='footer-". $socialIcons ."-icon' id='footer-". $socialIcons ."-icon' value='". $socialIcons ."' ". ( ! empty( $currentLoopSettings['socialIcons'][ $socialIcons ]["icon"]) ? "checked" : "" ) .">
					<label for='footer-". $socialIcons ."-icon'><span class='ic ic-social-". strtolower($socialIcons) ."'></span>
					". $socialIcons . " " . $this->msg( "loopsettings-link-icon-label" ) . "</label>
					<input type='url' title='" . $this->msg( "loopsettings-url-hint" ) . "' ". ( empty( $currentLoopSettings['socialIcons'][ $socialIcons ]["icon"] ) ? "disabled" : "" ) ." name='footer-". $socialIcons ."-link' placeholder='https://www.". strtolower($socialIcons) .".com/' id='footer-". $socialIcons ."-link' class='ml-2 mt-1 setting-input' value='". $currentLoopSettings['socialIcons'][ $socialIcons ]['link'] ."'><br>";
			} 
			$html .= '</div>'; // end of footer-tab
			
			$html .= '</div>'; // end of tab-content
			$html .= Html::rawElement(
					'input',
					array(
						'type' => 'hidden',
						'name' => 't',
						'id' => 'loopsettings-token',
						'value' => $saltedToken
					)
				)
			  	. Html::rawElement(
					'input',
					array(
						'type' => 'submit',
						'class' => 'mw-htmlform-submit mw-ui-button mw-ui-primary mw-ui-progressive mt-2 d-block',
						'id' => 'loopstructure-submit',
						'value' => $this->msg( 'submit' )
					)
				) ;
				
			$html .= "</form>";
			
			} else {
				$html .= Html::openElement(
						'div',
						array(
							'class' => 'alert alert-warning',
							'role' => 'alert'
						)
					)
					. $this->msg( 'loopsettings-no-permission' )
					. Html::closeElement(
					'div'
				);
		}
		$out->addHTML($html);
	}
	
	/**
	 * Puts request content into array
	 *
	 * @param Request $request 
	 * @return Array $newLoopSettings
	 */
	private function getLoopSettingsFromRequest ( $request ) {
		
		$newLoopSettings = array(
			'socialIcons' => array(
				'Facebook' => array (
					'icon' => $request->getText( 'footer-Facebook-icon' ),
					'link' => $request->getText( 'footer-Facebook-link' )
				),
				'Twitter' => array (
					'icon' => $request->getText( 'footer-Twitter-icon' ),
					'link' => $request->getText( 'footer-Twitter-link' )
				),
				'Youtube' => array (
					'icon' => $request->getText( 'footer-Youtube-icon' ),
					'link' => $request->getText( 'footer-Youtube-link' )
				),
				'Github' => array (
					'icon' => $request->getText( 'footer-Github-icon' ),
					'link' => $request->getText( 'footer-Github-link' )
				),
				'Instagram' => array (
					'icon' => $request->getText( 'footer-Instagram-icon' ),
					'link' => $request->getText( 'footer-Instagram-link' )
				),
			),
			'extraFooter' => array(
				'useExtraFooter' => $request->getText( 'extra-footer-active' ),
				'wikiText' => $request->getText( 'extra-footer-wikitext' )
			),
			'customLogo' => array(
				'useCustomLogo' => $request->getText( 'logo-use-custom' ),
				'customFileName' => $request->getText( 'custom-logo-filename' ),
				'customFilePath' => $request->getText( 'custom-logo-filepath' )
			),
			'imprintLink' => $request->getText( 'imprint-link' ),
			'privacyLink' => $request->getText( 'privacy-link' ),
			'oncampusLink' => $request->getText( 'oncampus-link' ),
			'rightsText' =>  $request->getText( 'rights-text' ),
			'rightsType' =>  $request->getText( 'rights-type' ),
			'rightsIcon' =>  $request->getText( 'rights-icon' ),
			'rightsUrl' =>  $request->getText( 'rights-url' ),
			'skinStyle' =>  $request->getText( 'skin-style' ),
			'language' =>  $request->getText( 'language-select' ),
			
			'errors' =>  array()
			
		);
	
		if ( ! empty( $newLoopSettings['extraFooter']['wikiText'] ) ) {
			$newLoopSettings['extraFooter']['parsedText'] = $this->parse($newLoopSettings['extraFooter']['wikiText']);
		}
		if ( ! empty( $newLoopSettings['customLogo']['customFileName'] ) ) {
			$tmpParsedResult = $this->parse("{{filepath:" . $newLoopSettings['customLogo']['customFileName'] . "}}" );
			preg_match('/href="(.*)"/', $tmpParsedResult, $tmpOutputArray);
			if ( isset($tmpOutputArray[1]) ) {
				$newLoopSettings['customLogo']['customFilePath'] = $tmpOutputArray[1];
			} else {
				$newLoopSettings['customLogo']['customFileName'] = "";
				$newLoopSettings['customLogo']['customFilePath'] = "";
				$newLoopSettings['customLogo']['useCustomLogo'] = "";
				array_push($newLoopSettings['errors'], $this->msg('loopsettings-error-customlogo-notfound'));
			}
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
	 * Gets variables from Settings file and puts it into an Array
	 *
	 * @return Array
	 */
	private function getLoopSettingsFromFile () {
		
		global $wgRightsText, $wgRightsType, $wgRightsIcon, $wgRightsUrl, $wgSocialIcons, 
		$wgExtraFooter, $wgImprintLink, $wgPrivacyLink, $wgOncampusLink, $wgCustomLogo, 
		$wgSkinStyles, $wgHiddenPrefs, $wgLanguageCode;
		
		$fileLoopSettings = array(
			'rightsText' => htmlspecialchars_decode($wgRightsText),
			'rightsType' => htmlspecialchars_decode($wgRightsType),
			'rightsIcon' => htmlspecialchars_decode($wgRightsIcon),
			'rightsUrl' => htmlspecialchars_decode($wgRightsUrl),
			'socialIcons' => $wgSocialIcons,
			'extraFooter' => $wgExtraFooter,
			'customLogo' => $wgCustomLogo,
			'imprintLink' => htmlspecialchars_decode($wgImprintLink),
			'privacyLink' => htmlspecialchars_decode($wgPrivacyLink),
			'oncampusLink' => htmlspecialchars_decode($wgOncampusLink),
			'skinStyle' => $wgHiddenPrefs["LoopSkinStyle"],
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