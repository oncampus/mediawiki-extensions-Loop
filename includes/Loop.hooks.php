<?php
class LoopHooks {

	/**
	 * Catch the Request to perform custom action LoopEditMode and LoopRenderMode
	 *
	 * This is attached to the MediaWiki 'MediaWikiPerformAction' hook.
	 *
	 * @param OutputPage $output
	 * @param Article $article
	 * @param Title $title
	 * @param User $user
	 * @param Request $request
	 * @param Wiki $wiki
	 */
	public static function onMediaWikiPerformAction( $output, $article, $title, $user, $request, $wiki ) {

		Loop::handleLoopRequest( $output, $request, $user );

		return true;
	}

	/**
	 * Cache different page version depending on status of Mode
	 *
	 * This is attached to the MediaWiki 'PageRenderingHash' hook.
	 *
	 * @param string $confstr
	 * @param User $user
	 * @param array $forOptions
	 * @return boolean
	 */
	public static function onPageRenderingHash( &$confstr, $user, $forOptions ) {

		global $wgDefaultUserOptions;

		$editMode = $user->getOption( 'LoopEditMode', false, true );

		if ( $editMode ) {
			$confstr .= "!loopeditmode=true";
		} else {
			$confstr .= "!loopeditmode=false";
		}

		return true;
	}

	/**
	 * Restricts viewing special pages to privileged users
	 *
	 * This is attached to the MediaWiki 'SpecialPage_initList' hook.
	 *
	 * @param array $specialPages
	 * @return boolean
	 */
	public static function onSpecialPageinitList ( &$specialPages ) {
		global $wgOut;
		$user = $wgOut->getUser();
		if ( ! $user->isAllowed( "loop-view-special-pages" ) ) {

			$hidePages = array( 'Recentchangeslinked', 'Recentchanges', 'Listredirects',  'Mostlinkedcategories', 'Export', 'Uncategorizedtemplates', 
				'DoubleRedirects', 'DeletedContributions', 'Mostcategories', 'Block', 'Movepage', 'Mostrevisions', 'Unusedimages', 'Log', 
				'Mostlinkedtemplates', 'Deadendpages', 'JavaScriptTest', 'Userrights', 'Import', 'Ancientpages', 'Uncategorizedcategories', 'Activeusers', 
				'MergeHistory', 'Randompage', 'Protectedpages', 'Wantedfiles', 'Listgrouprights', 'EditWatchlist', 'Blockme', 'FileDuplicateSearch', 
				'Withoutinterwiki', 'Randomredirect', 'BlockList', 'Popularpages', 'Emailuser', 'Booksources', 'Upload', 'Confirmemail', 'Watchlist', 
				'MIMEsearch', 'Allpages', 'Fewestrevisions', 'Unblock', 'ComparePages', 'Uncategorizedimages', 'Mostinterwikis', 'Preferences', 
				'Categories', 'Statistics', 'Version', 'UploadStash', 'Undelete', 'Whatlinkshere', 'Lockdb', 'Lonelypages', 'Mostimages', 
				'Unwatchedpages', 'Shortpages', 'Protectedtitles', 'Revisiondelete', 'Newpages', 'Unusedtemplates', 'Allmessages', 'CachedPage', 
				'Filepath', 'Wantedpages', 'LinkSearch', 'Prefixindex', 'BrokenRedirects', 'Mostlinked', 'Tags', 'LoopStructureEdit', 'LoopSettings', 
				'Longpages', 'Uncategorizedpages', 'Newimages', 'Blankpage', 'Disambiguations', 'Unusedcategories', 'Wantedcategories', 
				'Unlockdb', 'PagesWithProp', 'Listfiles', 'Contributions', 'Listusers', 'Wantedtemplates', 'TrackingCategories', 'Stabilization',
				'AutoblockList',  'ResetTokens', 'Listgrants', 'Listadmins', 'Listbots', 'PasswordPolicies', 'MediaStatistics', 'ListDuplicatedFiles', 
				'ApiSandbox', 'RandomInCategory', 'Randomrootpage', 'GoToInterwiki', 'ExpandTemplates', 'ApiHelp', 'Diff', 'EditTags', 'Mycontributions', 
				'MyLanguage', 'Mypage', 'Mytalk', 'Myuploads', 'AllMyUploads', 'PermanentLink', 'Redirect', 'RunJobs', 'PageData', 'ChangeContentModel', 
				'MathStatus', 'RevisionReview', 'ReviewedVersions', 'BotPasswords', 'LinkAccounts', 'UnlinkAccounts', 'ChangeCredentials', 'RemoveCredentials',
				'PendingChanges', 'ProblemChanges', 'ReviewedPages', 'UnreviewedPages', 'QualityOversight', 'ValidationStatistics', 'ConfiguredPages'
				
			);
			foreach( $hidePages as $page ){ 
				unset( $specialPages[$page] );
			}
		}
		return true;
	}

	public static function onHtmlPageLinkRendererEnd( $linkRenderer, $target, $isKnown, &$text, &$attribs, &$ret ) {

		# Add id to loop_reference links
		if ( isset( $attribs["data-target"])) {
			$attribs["href"] .= "#". $attribs["data-target"];
		}
		return true;
	}

	
	/**
	 * Remove image link when not in loopeditmode
	 * 
	 * This is attached to the MediaWiki 'ParserMakeImageParams' hook.
	 * 
	 * @param Title $title
	 * @param File $file
	 * @param array $params
	 * @param Parser $parser
	 * @return boolean
	 */
	public static function onParserMakeImageParams( $title, $file, &$params, $parser ) {
		
		$loopEditMode = $user->getOption( 'LoopEditMode', false, true );
		$parser->getOptions()->optionUsed( 'LoopEditMode' );
		#dd($loopEditMode);
		
		if ($loopEditMode) {
			$params['frame']['no-link'] = false;
		} else {
			$params['frame']['no-link'] = true;
		}
		
		return true;
	}	
	

}
