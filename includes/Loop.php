<?php
/**
  * @description Handles general functions and settings
  * @author Dennis Krohn <dennis.krohn@th-luebeck.de>
  */

if ( !defined( 'MEDIAWIKI' ) ) die ( "This file cannot be run standalone.\n" );

use MediaWiki\MediaWikiServices;

class Loop {

	/**
	 * Save changes in LoopEditMode and LoopRenderMode
	 *
	 * This is called by 'MediaWikiPerformAction' hook.
	 *
	 * @param OutputPage $output
	 * @param Request $request
	 * @param User $user
	 */
	public static function handleLoopRequest( $output, $request, $user ) {

		LoopSettings::setupGlobalVariablesFromLoopSettings();

		$mws = MediaWikiServices::getInstance();
		$permissionManager = $mws->getPermissionManager();
		$userOptionsManager = MediaWikiServices::getInstance()->getUserOptionsManager();

		if ( $permissionManager->userHasRight( $user, 'edit' ) && $user->isAnon() == false ) {
			$loopeditmodeRequestValue  = $request->getText( 'loopeditmode' );
			if( isset( $loopeditmodeRequestValue ) && ( in_array( $loopeditmodeRequestValue, array( "0", "1" ) ) ) ) {
				$userOptionsManager->setOption( $user, 'LoopEditMode', $loopeditmodeRequestValue );
				$userOptionsManager->saveOptions($user);
			}
		}

		if ( $permissionManager->userHasRight( $user, 'loop-rendermode' ) && $user->isAnon() == false ) {
			$looprendermodeRequestValue  = $request->getText( 'looprendermode' );
			if( isset( $looprendermodeRequestValue ) && ( in_array( $looprendermodeRequestValue, array( 'offline', 'epub' ) ) ) ) {
				$userOptionsManager->setOption( $user, 'LoopRenderMode', $looprendermodeRequestValue );
			} else {
				$userOptionsManager->setOption( $user, 'LoopRenderMode', 'default' );
			}
		}

		# If there is no Structure, create one.
		$loopStructure = new LoopStructure();
		$loopStructureItems = $loopStructure->getStructureitems();
		if ( empty( $loopStructureItems ) ) {
			$loopStructure->setInitialStructure();
		}

	}

	public static function onExtensionLoad() {

		# Loop Object constants
		define('LOOPOBJECTNUMBER_MARKER_PREFIX', "\x7fUNIQ--loopobjectnumber-");
		define('LOOPOBJECTNUMBER_MARKER_SUFFIX', "-QINU\x7f");

		global $wgLanguageCode, $wgLoopImprintLink, $wgWhitelistRead, $wgFlaggedRevsExceptions, $wgFlaggedRevsLowProfile, $wgFlaggedRevsTags,
		$wgFlaggedRevsTagsRestrictions, $wgFlaggedRevsAutopromote, $wgShowRevisionBlock, $wgSimpleFlaggedRevsUI, $wgFlaggedRevsAutoReview,
		$wgFlaggedRevsNamespaces, $wgLogRestrictions, $wgFileExtensions, $wgExtraNamespaces, $wgContentHandlers, $wgexLingoPage, $wgexLingoDisplayOnce,
		$wgLoopPrivacyLink, $wgCaptchaTriggers, $wgCaptchaClass, $wgReCaptchaSiteKey, $wgReCaptchaSecretKey, $wgExternalLinkTarget;

		# external links to new tab
		$wgExternalLinkTarget = '_blank';

		#override preSaveTransform function by copying WikitextContent and adding a Hook
		$wgContentHandlers[CONTENT_MODEL_WIKITEXT] = 'LoopWikitextContentHandler';

		# Captcha before settings configuration
		$wgCaptchaTriggers["bugreport"] = ( !isset( $wgCaptchaTriggers["bugreport"] ) ? true : $wgCaptchaTriggers["bugreport"] );
		$wgCaptchaClass = 'ReCaptchaNoCaptcha';

		# Define new name for glossary
		$glossary = array( "de" => "Glossar", "de-formal" => "Glossar", "en" => "Glossary", "es" => "Glosario", "sv" => "Ordlista" );
		$wgExtraNamespaces[ NS_GLOSSARY ] = array_key_exists( $wgLanguageCode, $glossary ) ? $glossary[ $wgLanguageCode ] : "Glossary";

		$wgWhitelistRead = is_array( $wgWhitelistRead ) ? $wgWhitelistRead : array();
		$wgWhitelistRead = array_merge ( $wgWhitelistRead, [ "Spezial:Impressum", "Spezial:Datenschutz", "Special:Imprint", "Special:Privacy", "Especial:Imprint", "Especial:Privacy", "Speciel:Imprint", "Speciel:Privacy"] );
		$wgWhitelistRead = array_merge ( $wgWhitelistRead, [ "Spezial:LoopRSS", "Special:LoopRSS", "Especial:LoopRSS", "Speciel:LoopRSS"] );
		$wgWhitelistRead = array_merge ( $wgWhitelistRead, [ "Spezial:LoopManualUpdater", "Special:LoopManualUpdater", "Especial:LoopManualUpdater", "Speciel:LoopManualUpdater"] ); # during migration only
		$wgWhitelistRead[] = $wgLoopImprintLink;
		$wgWhitelistRead[] = $wgLoopPrivacyLink;
		$wgWhitelistRead[] = "MediaWiki:Common.js";
		$wgWhitelistRead[] = "MediaWiki:Common.css";
		$wgWhitelistRead[] = "MediaWiki:Common.js";
		$wgWhitelistRead[] = "MediaWiki:ExtraFooter";

		# FlaggedRevs Settings
		$wgFlaggedRevsLowProfile = false;
		$wgFlaggedRevsExceptions = array();
		$wgFlaggedRevsTags = array(
			'official' => array( 'levels' => 1, 'quality' => 1, 'pristine' => 1 )
		);
		$wgFlaggedRevsTagsRestrictions = array(
			'official' => array( 'review' => 1, 'autoreview' => 1 )
		);
		$wgFlaggedRevsAutopromote = false;
		$wgShowRevisionBlock = false;
		$wgSimpleFlaggedRevsUI = false;
		$wgFlaggedRevsAutoReview = 3; # FR_AUTOREVIEW_CREATION_AND_CHANGES
		$wgFlaggedRevsNamespaces[] = NS_GLOSSARY; #adds Glossary to reviewing process

		# Log viewing rights
		$wgLogRestrictions["loopexport"] = "loop-view-export-log";
		$wgLogRestrictions["block"] = "loop-view-export-log"; #Benutzersperr-Logbuch
		$wgLogRestrictions["newusers"] = "loop-view-export-log"; #Neu angemeldete User-Logbuch
		$wgLogRestrictions["rights"] = "loop-view-export-log"; #Benutzerrechte-Logbuch

		# Uploadable file extensions
		$wgFileExtensions = array_unique( array_merge( $wgFileExtensions, array('pdf','ppt','pptx','xls','xlsx','doc','docx','odt','odc','odp','odg','zip','svg',
			'eps','csv','psd','mp4','mp3','mpp','ter','ham','cdf','swr','xdr', 'swf' ) ) );

		# Disable Talk Namespaces
		$wgNamespaceProtection[NS_TALK] = ['*'];
		$wgNamespaceProtection[NS_USER_TALK] = ['*'];
		$wgNamespaceProtection[NS_PROJECT_TALK] = ['*'];
		$wgNamespaceProtection[NS_FILE_TALK] = ['*'];
		$wgNamespaceProtection[NS_MEDIAWIKI_TALK] = ['*'];
		$wgNamespaceProtection[NS_TEMPLATE_TALK] = ['*'];
		$wgNamespaceProtection[NS_HELP_TALK] = ['*'];
		$wgNamespaceProtection[NS_CATEGORY_TALK] = ['*'];

		# Lingo configuration
		$wgexLingoPage = 'MediaWiki:LoopTerminologyPage';
		$wgexLingoDisplayOnce = true;

		# Captcha post settings configuration
		if ( empty( $wgReCaptchaSecretKey ) && empty( $wgReCaptchaSiteKey ) ) {
			# no captchas if there is no captcha service
			$wgCaptchaTriggers["edit"] = false;
			$wgCaptchaTriggers["create"] = false;
			$wgCaptchaTriggers["addurl"] = false;
			$wgCaptchaTriggers["sendemail"] = false;
			$wgCaptchaTriggers["createaccount"] = false;
			$wgCaptchaTriggers["badlogin"] = false;
			$wgCaptchaTriggers["badloginperuser"] = false;
			$wgCaptchaTriggers["bugreport"] = false;
		}

		return true;
	}

	/**
	 * Set up LOOP-specific pages so they are not red links
	 */
	public static function setupLoopPages() {

		$userGroupManager = MediaWikiServices::getInstance()->getUserGroupManager();

		$systemUser = User::newFromName( 'LOOP_SYSTEM' );
		if ( $systemUser->getId() != 0 ) {
			$userGroupManager->addUserToGroup ( $systemUser, "sysop" );
		}
		$summary = CommentStoreComment::newUnsavedComment( "Created for LOOP2" );

		$loopExceptionPage = MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle( Title::newFromText( wfMessage( 'loop-tracking-category-error' )->inContentLanguage()->text(), NS_CATEGORY ));
		$loopExceptionPageContent = new WikitextContent( wfMessage( 'loop-tracking-category-error-desc' )->inContentLanguage()->text() );
		$loopExceptionPageUpdater = $loopExceptionPage->newPageUpdater( $systemUser );
		$loopExceptionPageUpdater->setContent( "main", $loopExceptionPageContent );
		$loopExceptionPageUpdater->saveRevision ( $summary, EDIT_NEW );

		$loopLegacyPage = MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle( Title::newFromText( wfMessage( 'looplegacy-tracking-category' )->inContentLanguage()->text(), NS_CATEGORY ));
		$loopLegacyPageContent = new WikitextContent( wfMessage( 'looplegacy-tracking-category-desc' )->inContentLanguage()->text() );
		$loopLegacyPageUpdater = $loopLegacyPage->newPageUpdater( $systemUser );
		$loopLegacyPageUpdater->setContent( "main", $loopLegacyPageContent );
		$loopLegacyPageUpdater->saveRevision ( $summary, EDIT_NEW );

		$loopTerminologyPage = MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle( Title::newFromText( "LoopTerminologyPage", NS_MEDIAWIKI ));
		$loopTerminologyPageContent = new WikitextContent( "" ); #empty
		$loopTerminologyPageUpdater = $loopTerminologyPage->newPageUpdater( $systemUser );
		$loopTerminologyPageUpdater->setContent( "main", $loopTerminologyPageContent );
		$loopTerminologyPageUpdater->saveRevision ( $summary, EDIT_NEW );

	}


	/**
	 * Adds id to object, cite and index tags if there are none
	 * @param string $text
	 */
	public static function setReferenceIds( $text ) { #todo remove id and type # why?

		# REGEX: All tags to get IDs that don't have id="" or id='' (might be empty!)
		$regex = '/(<(loop_figure|loop_formula|loop_listing|loop_media|loop_table|loop_task|cite|loop_index|loop_screenshot)([^>]*(\s*id=[\'"]{1}[^>]*[\'"]{1}\s*){0})[^>]*)(>)/iUs';
		preg_match_all( $regex, $text, $occurences );
		$tmpText = $text;
		$one = 1;
		foreach ( $occurences[1] as $i => $val ) { # replace occurrences with markers
			$tmpText = preg_replace( $regex, "%LOOPIDMARKER$i%", $tmpText, 1);
		}

		foreach ( $occurences[1] as $i => $val ) { # replace markers with ids - this is safe for identical entries like <cite> without any attributes
			if ( strpos( $val, "id=\"" ) === false ) {
				$tmpReplace = $val.' id="'.uniqid().'">';
			} else {
				$tmpReplace = $val.'>';
			}
			$tmpText = str_replace( "%LOOPIDMARKER$i%", $tmpReplace, $tmpText, $one );
		}

		return $tmpText;
		#dd($text, $tmpText, $occurences, $count);
		/*
		$changedText = false;
		$tmptext = mb_convert_encoding("<?xml version='1.0' encoding='utf-8'?>\n<div>" .$text.'</div>', 'HTML-ENTITIES', 'UTF-8');
		$forbiddenTags = array( 'nowiki', 'code', '!--', 'syntaxhighlight', 'source'); # don't set ids when in these tags
		$dom = new DOMDocument("1.0", 'utf-8');
		@$dom->loadHTML( $tmptext, LIBXML_HTML_NODEFDTD );

		$xpath = new DOMXPath( $dom );

		$objectTags = array( '//loop_figure', '//loop_formula', '//loop_listing', '//loop_media', '//loop_table', '//loop_task', '//cite', '//loop_index', '//loop_screenshot' );

		$query = implode(' | ', $objectTags);
		$nodes = $xpath->query( $query );
		$change = array();
		foreach ( $nodes as $node ) {
			# don't set ids when in these tags
			if ( ! in_array( strtolower($node->parentNode->nodeName), $forbiddenTags ) && ! in_array( strtolower($node->parentNode->parentNode->nodeName), $forbiddenTags ) ) {
				$existingId = $node->getAttribute( 'id' );
				if( ! $existingId ) {
					$change[$node->nodeName] = uniqid();
				}
			}
		}
		if ( !empty ( $change ) ) {
			dd($change);
			#$changedText = mb_substr($dom->saveHTML(), 55, -21);
			#$decodedText = html_entity_decode($changedText);
			return $decodedText;
		} else {
			return $text;
		}
		*/

	}

}


class SpecialLoopImprint extends UnlistedSpecialPage {

	function __construct() {
		parent::__construct( 'LoopImprint' );
	}

	function execute( $par ) {

		global $wgLoopExternalImprintPrivacy, $wgLoopExternalImprintUrl;
		$out = $this->getOutput();
		$request = $this->getRequest();
		$user = $this->getUser();
		Loop::handleLoopRequest( $out, $request, $user ); #handle editmode
		$out->setPageTitle( $this->msg( "imprint" ) );
		$this->setHeaders();

		if ( $wgLoopExternalImprintPrivacy && !empty ( $wgLoopExternalImprintUrl ) ) {

			$return = self::renderLoopImprintSpecialPage();

			if ( empty( $return ) ) {
				global $wgLoopImprintLink;
				$out->redirect ( $wgLoopImprintLink );
			}
			$out->addHTML($return);

		} else {
			global $wgLoopImprintLink;
			$out->redirect ( $wgLoopImprintLink );
		}
	}

	public static function renderLoopImprintSpecialPage () {

		global $wgServerName, $wgLoopExternalImprintPrivacy, $wgLoopExternalImprintUrl;

		$url = $wgLoopExternalImprintUrl.'?loop=' . $wgServerName;

		$cha = curl_init();
		curl_setopt($cha, CURLOPT_URL, ($url));
		curl_setopt($cha, CURLOPT_ENCODING, "UTF-8" );
		curl_setopt($cha, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($cha, CURLOPT_FOLLOWLOCATION, true);
		$return = curl_exec( $cha );
		curl_close( $cha );

		return $return;
	}
}


class SpecialLoopPrivacy extends UnlistedSpecialPage {

	function __construct() {
		parent::__construct( 'LoopPrivacy' );
	}

	function execute( $par ) {

		global $wgLoopExternalImprintPrivacy, $wgLoopExternalPrivacyUrl;
		$out = $this->getOutput();
		$request = $this->getRequest();
		$user = $this->getUser();
		Loop::handleLoopRequest( $out, $request, $user ); #handle editmode
		$out->setPageTitle( $this->msg( "privacy" ) );
		$this->setHeaders();

		if ( $wgLoopExternalImprintPrivacy && !empty ( $wgLoopExternalPrivacyUrl ) ) {
			$return = self::renderLoopPrivacySpecialPage();
			if ( empty( $return ) ) {
				global $wgLoopPrivacyLink;
				$out->redirect ( $wgLoopPrivacyLink );
			}
			$out->addHTML($return);

		} else {
			global $wgLoopPrivacyLink;
			$out->redirect ( $wgLoopPrivacyLink );
		}

	}

	public static function renderLoopPrivacySpecialPage () {

		global $wgServerName, $wgLoopExternalPrivacyUrl;

		$url = $wgLoopExternalPrivacyUrl.'?loop=' . $wgServerName;

		$cha = curl_init();
		curl_setopt($cha, CURLOPT_URL, ($url));
		curl_setopt($cha, CURLOPT_ENCODING, "UTF-8" );
		curl_setopt($cha, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($cha, CURLOPT_FOLLOWLOCATION, true);
		$return = curl_exec( $cha );
		curl_close( $cha );

		return $return;
	}
}
