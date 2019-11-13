<?php 

/**
  * @description Handles general functions and settings
  * @author Dennis Krohn <dennis.krohn@th-luebeck.de>
  */
  
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file cannot be run standalone.\n" );
}

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
		
		if ( $user->isAllowed( 'edit' ) ) {
			$loopeditmodeRequestValue  = $request->getText( 'loopeditmode' );
			if( isset( $loopeditmodeRequestValue ) && ( in_array( $loopeditmodeRequestValue, array( "0", "1" ) ) ) ) {
				$user->setOption( 'LoopEditMode', $loopeditmodeRequestValue );
				$user->saveSettings(); 
			}
		}
		
		if ( $user->isAllowed( 'loop-rendermode' ) ) {
			$looprendermodeRequestValue  = $request->getText( 'looprendermode' );
			if( isset( $looprendermodeRequestValue ) && ( in_array( $looprendermodeRequestValue, array( 'offline', 'epub' ) ) ) ) {
				$user->setOption( 'LoopRenderMode', $looprendermodeRequestValue );
			} else {
				$user->setOption( 'LoopRenderMode', 'default' );
			}
		}	
			
	}
		
	public static function onExtensionLoad() {

		if ( ! defined( 'FLAGGED_REVISIONS' ) ) { 
			exit( "FlaggedRevs must be installed to run LOOP" );
		}

		# Loop Object constants
		define('LOOPOBJECTNUMBER_MARKER_PREFIX', "\x7fUNIQ--loopobjectnumber-");
		define('LOOPOBJECTNUMBER_MARKER_SUFFIX', "-QINU\x7f");

		global $wgRightsText, $wgRightsUrl, $wgRightsIcon, $wgLanguageCode, $wgDefaultUserOptions, $wgImprintLink, $wgPrivacyLink, 
		$wgWhitelistRead, $wgFlaggedRevsExceptions, $wgFlaggedRevsLowProfile, $wgFlaggedRevsTags, $wgFlaggedRevsTagsRestrictions, 
		$wgFlaggedRevsAutopromote, $wgShowRevisionBlock, $wgSimpleFlaggedRevsUI, $wgFlaggedRevsAutoReview, $wgFlaggedRevsNamespaces,
		$wgLogRestrictions, $wgFileExtensions, $wgLoopObjectNumbering, $wgLoopNumberingType, $wgExtraNamespaces, $wgLoopLiteratureCiteType,
		$wgContentHandlers, $wgexLingoPage, $wgexLingoDisplayOnce;
		
		#override preSaveTransform function by copying WikitextContent and adding a Hook
		$wgContentHandlers[CONTENT_MODEL_WIKITEXT] = 'LoopWikitextContentHandler';

		$dbr = wfGetDB( DB_REPLICA );
		# Check if table exists. SetupAfterCache hook fails if there is no loop_settings table.
		# maintenance/update.php can't create loop_settings table if SetupAfterCache Hook fails, so this check is nescessary.
		if ( $dbr->tableExists( 'loop_settings' ) ) {
			
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

			if ( isset( $row->lset_structure ) ) {
				$wgRightsText = ( !isset( $data['lset_rightstext'] ) ? $wgRightsText : $data['lset_rightstext'] );
				$wgRightsUrl = ( !isset( $data['lset_rightsurl'] ) ? $wgRightsUrl : $data['lset_rightsurl'] );
				$wgRightsIcon = ( !isset( $data['lset_rightsicon'] ) ? $wgRightsIcon : $data['lset_rightsicon'] );
				$wgLanguageCode = ( !isset( $data['lset_languagecode'] ) ? $wgLanguageCode : $data['lset_languagecode'] );
				$wgDefaultUserOptions['LoopSkinStyle'] = ( !isset( $data['lset_skinstyle'] ) ? 'loop-common' : $data['lset_skinstyle'] );
				$wgWhitelistRead[] = ( !isset( $data['lset_imprintlink'] ) ? $wgImprintLink : $data['lset_imprintlink'] );
				$wgWhitelistRead[] = ( !isset( $data['lset_privacylink'] ) ? $wgPrivacyLink : $data['lset_privacylink'] );
				$wgLoopObjectNumbering = ( !isset( $data['lset_numberingobjects'] ) ? $wgLoopObjectNumbering : $data['lset_numberingobjects'] );
				$wgLoopNumberingType = ( !isset( $data['lset_numberingtype'] ) ? $wgLoopNumberingType : $data['lset_numberingtype'] );
				$wgLoopLiteratureCiteType = ( !isset( $data['lset_citationstyle'] ) ? $wgLoopLiteratureCiteType : $data['lset_citationstyle'] );

			}
			
			# Define new name for glossary
			$wgExtraNamespaces[ NS_GLOSSARY ] = wfMessage( "loop-glossary-namespace" )->inLanguage( $wgLanguageCode )->text();

		}
		
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
		$wgFlaggedRevsAutoReview = FR_AUTOREVIEW_CREATION_AND_CHANGES;
		$wgFlaggedRevsNamespaces[] = NS_GLOSSARY; #adds Glossary to reviewing process

		# Log viewing rights
		$wgLogRestrictions["loopexport"] = "loop-view-export-log";
		$wgLogRestrictions["block"] = "loop-view-export-log"; #Benutzersperr-Logbuch
		$wgLogRestrictions["newusers"] = "loop-view-export-log"; #Neu angemeldete User-Logbuch
		$wgLogRestrictions["rights"] = "loop-view-export-log"; #Benutzerrechte-Logbuch

		# Uploadable file extensions
		$wgFileExtensions = array_merge( $wgFileExtensions, array('pdf','ppt','pptx','xls','xlsx','doc','docx','odt','odc','odp','odg','zip','svg',
			'eps','csv','psd','mp4','mp3','mpp','ter','ham','cdf','swr','xdr'));

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

		return true;
	}
	
	/**
	 * Set up LOOP-specific pages so they are not red links
	 */
	public static function setupLoopPages() {

		$systemUser = User::newSystemUser( 'LOOP_SYSTEM', array( 'steal' => true, 'create'=> true, 'validate' => true ) );
		$systemUser->addGroup("sysop");
		$summary = CommentStoreComment::newUnsavedComment( "Created for LOOP2" ); 
			
		$loopExceptionPage = WikiPage::factory( Title::newFromText( wfMessage( 'loop-tracking-category-error' )->inContentLanguage()->text(), NS_CATEGORY ));
		$loopExceptionPageContent = new WikitextContent( wfMessage( 'loop-tracking-category-error-desc' )->inContentLanguage()->text() );
		$loopExceptionPageUpdater = $loopExceptionPage->newPageUpdater( $systemUser ); 
		$loopExceptionPageUpdater->setContent( "main", $loopExceptionPageContent );
		$loopExceptionPageUpdater->saveRevision ( $summary, EDIT_NEW );


		$loopLegacyPage = WikiPage::factory( Title::newFromText( wfMessage( 'looplegacy-tracking-category' )->inContentLanguage()->text(), NS_CATEGORY ));
		$loopLegacyPageContent = new WikitextContent( wfMessage( 'looplegacy-tracking-category-desc' )->inContentLanguage()->text() );
		$loopLegacyPageUpdater = $loopLegacyPage->newPageUpdater( $systemUser ); 
		$loopLegacyPageUpdater->setContent( "main", $loopLegacyPageContent );
		$loopLegacyPageUpdater->saveRevision ( $summary, EDIT_NEW );

		$loopTerminologyPage = WikiPage::factory( Title::newFromText( "LoopTerminologyPage", NS_MEDIAWIKI ));
		$loopTerminologyPageContent = new WikitextContent( "" ); #empty
		$loopTerminologyPageUpdater = $loopTerminologyPage->newPageUpdater( $systemUser ); 
		$loopTerminologyPageUpdater->setContent( "main", $loopTerminologyPageContent );
		$loopTerminologyPageUpdater->saveRevision ( $summary, EDIT_NEW );

	}

	
	/**
	 * Adds id to object, cite and index tags if there are none
	 * @param string $text
	 */
	public static function setReferenceIds( $text ) { #todo remove id and type
		$changedText = false;
		$tmptext = mb_convert_encoding("<?xml version='1.0' encoding='utf-8'?>\n<div>" .$text.'</div>', 'HTML-ENTITIES', 'UTF-8');
		$forbiddenTags = array( 'nowiki', 'code', '!--', 'syntaxhighlight', 'source'); # don't set ids when in these tags 
		$dom = new DOMDocument("1.0", 'utf-8');
		@$dom->loadHTML( $tmptext, LIBXML_HTML_NODEFDTD );
		
		$xpath = new DOMXPath( $dom );
		
		$objectTags = array( '//loop_figure', '//loop_formula', '//loop_listing', '//loop_media', '//loop_table', '//loop_task', '//cite', '//loop_index' );
		
		$query = implode(' | ', $objectTags);
		$nodes = $xpath->query( $query );
		$changed = false;
		foreach ( $nodes as $node ) {
			# don't set ids when in these tags 
			if ( ! in_array( strtolower($node->parentNode->nodeName), $forbiddenTags ) && ! in_array( strtolower($node->parentNode->parentNode->nodeName), $forbiddenTags ) ) {
				$existingId = $node->getAttribute( 'id' );
				if( ! $existingId ) {
					$node->setAttribute('id', uniqid() );
					$changed = true;
				}
			}
		}
		if ( $changed ) {
			$changedText = mb_substr($dom->saveHTML(), 55, -21);
			$decodedText = html_entity_decode($changedText);
			return $decodedText;
		} else {
			return $text;
		}
	}

}