<?php class Loop {

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
		$wgLogRestrictions, $wgFileExtensions, $wgLoopObjectNumbering, $wgLoopNumberingType, $wgExtraNamespaces;
		
		$systemUser = User::newSystemUser( 'LOOP_SYSTEM', [ 'steal' => false, 'create'=> true, 'validate' => true ] ); 
		$systemUser->addGroup("sysop");
		
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

			}
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

		# Define new name for glossary
		$wgExtraNamespaces[ NS_GLOSSARY ] = wfMessage( "loop-glossary-namespace" )->inLanguage( $wgLanguageCode )->text();

		return true;
	}
	
	/**
	 * Set up LOOP-specific pages so they are not red links
	 */
	public static function setupLoopPages() {
		
		global $wgOut;

		$loopExceptionPage = WikiPage::factory( Title::newFromText( wfMessage( 'loop-tracking-category-error' )->text(), NS_CATEGORY ));
		$loopExceptionPageContent = new WikitextContent( wfMessage( 'loop-tracking-category-error-desc' )->inContentLanguage()->text() );
		$loopExceptionPage->doEditContent( $loopExceptionPageContent, '', EDIT_NEW, false, $wgOut->getUser() );

	}

}