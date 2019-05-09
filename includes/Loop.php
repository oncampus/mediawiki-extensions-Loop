<?php class Loop {

	/**
	 * Save changes in LoopEditMode and LoopRenderMode
	 * 
	 * This is called by 'SpecialPageBeforeExecute' and 'MediaWikiPerformAction' hooks.
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
		$wgFlaggedRevsAutopromote, $wgShowRevisionBlock, $wgSimpleFlaggedRevsUI, $wgFlaggedRevsAutoReview, $wgLogRestrictions,
		$wgFileExtensions, $wgLoopObjectNumbering, $wgLoopNumberingType;
		
		$dbr = wfGetDB( DB_REPLICA );
		# Check if table exists. SetupAfterCache hook fails if there is no loop_settings table.
		# maintenance/update.php can't create loop_settings table if SetupAfterCache Hook fails, so this check is nescessary.
		if ( $dbr->tableExists( 'loop_settings' ) ) {

			$res = $dbr->select(
				'loop_settings',
				array( 
					'lset_id', 
					'lset_rightstext', 
					'lset_rightsurl', 
					'lset_rightsicon', 
					'lset_languagecode', 
					'lset_skinstyle', 
					'lset_imprintlink', 
					'lset_privacylink', 
					'lset_numberingobjects', 
					'lset_numberingtype'
				),
				array(),
				__METHOD__,
				array( 'ORDER BY' => 'lset_id DESC LIMIT 1' )
			);
			$row = $res->fetchObject();

			if ( isset( $row->lset_id ) ) {
				$wgRightsText = ( empty( $row->lset_rightstext ) ? $wgRightsText : $row->lset_rightstext );
				$wgRightsUrl = ( empty( $row->lset_rightsurl ) ? $wgRightsUrl : $row->lset_rightsurl );
				$wgRightsIcon = ( empty( $row->lset_rightsicon ) ? $wgRightsIcon : $row->lset_rightsicon  );
				$wgLanguageCode = ( empty( $row->lset_languagecode ) ? $wgLanguageCode : $row->lset_languagecode );
				$wgDefaultUserOptions['LoopSkinStyle'] = ( empty( $row->lset_skinstyle ) ? 'loop-common' : $row->lset_skinstyle );
				$wgWhitelistRead[] = empty( $row->lset_imprintlink ) ? $wgImprintLink : $row->lset_imprintlink;
				$wgWhitelistRead[] = empty( $row->lset_privacylink ) ? $wgPrivacyLink : $row->lset_privacylink;
				$wgLoopObjectNumbering = ( empty( $row->lset_numberingobjects ) ? $wgLoopObjectNumbering : $row->lset_numberingobjects );
				$wgLoopNumberingType = ( empty( $row->lset_numberingtype ) ? $wgLoopNumberingType : $row->lset_numberingtype );
				
			}
		}
		
		$wgWhitelistRead[] = "MediaWiki:Common.css";
		$wgWhitelistRead[] = "MediaWiki:Common.js";
		$wgWhitelistRead[] = "MediaWiki:ExtraFooter";
		
		# FlaggedRevs Settings
		#global $IP; // move here? kann bei mw-einrichtung nicht automatisch angegeben werden und wirft wegen folgender zeilen fehler.
		#require_once "$IP/extensions/FlaggedRevs/FlaggedRevs.php";
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

		return true;
	}
	
	/**
	 * Set up LOOP-specific pages so they are not red links
	 */
	private static function setupLoopPages() {
		
		global $wgOut;

		$loopExceptionPage = WikiPage::factory( Title::newFromText( wfMessage( 'loop-tracking-category-error' )->text(), NS_CATEGORY ));
		$loopExceptionPageContent = new WikitextContent( wfMessage( 'loop-tracking-category-error-desc' )->inContentLanguage()->text() );
		$loopExceptionPage->doEditContent( $loopExceptionPageContent, '', EDIT_NEW, false, $wgOut->getUser() );

	}

}