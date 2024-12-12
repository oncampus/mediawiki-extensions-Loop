<?php

use MediaWiki\MediaWikiServices;


class LoopProgress
{
 	const  NOT_UNDERSTOOD = 0;
	const  UNDERSTOOD = 1;
	const  NOT_EDITED = 3;

	public static function showProgressBar(){
		global $wgOut;
		$user = $wgOut->getUser();

		$mws = MediaWikiServices::getInstance();
		$userGroupManager = $mws->getUserGroupManager();
		$permissionManager = $mws->getPermissionManager();

		if ( !$permissionManager->userHasRight( $user, 'loopprogress' ) ) {
			return false;
		}
		return true;
	}

	public static function hasProgressPermission() {
		global $wgOut;
		$user = $wgOut->getUser();

		$mws = MediaWikiServices::getInstance();
		$userGroupManager = $mws->getUserGroupManager();
		$permissionManager = $mws->getPermissionManager();

		if ( !$permissionManager->userHasRight( $user, 'loopprogress' ) ) {
			return false;
		}
		return true;
	}

	public static function renderProgressBar() {
		global $wgOut;

		$user = $wgOut->getUser();
		$html = '';

		// get page count
		$pages = LoopProgressDBHandler::getAllPages();

		// get understood pages
		$understoodPages = LoopProgressDBHandler::getPageUnderstoodCountForUser($user);

		// should not exceed 100
		// possible if a page is set to understood that is not in the loop table of contents
		$understood_percent = min(($understoodPages->page_understood_count / $pages->count_pages) * 100, 100);

		// create progressbar
		$html .= '<div class="progressbar" style="border:2px solid black; position:relative; width:100%; 20px; display:block">'; //height:100%; // todo change px
		//if($understood_percent > 50) {
		//	$html .= '<div class="progress_tracker" style="display:block; height:24px;width:' . $understood_percent .'%;background-color:#366b9f;">' .'<span style="color:white">'. round($understood_percent,2) .'</span></div>';
		//} else {
			$html .= '<div class="progress_tracker" style="display:block; height:24px;width:' . $understood_percent .'%;background-color:#366b9f;">' . '</div>' . '<div style="color:white; position:absolute;width: 100%;height: 100%; z-index: 100; top:0; left:0; display: flex; justify-content: center"><p style="color:revert; position:absolute; mix-blend-mode: difference;">'. round($understood_percent,2) .'</p></div>';
	//	}

		$html .= '</div>';


		// test alternative progressbar
		$states = LoopProgressDBHandler::getPageUnderstoodStatesForUser($user);

		$understood_counter = 0;
		$not_understood_counter = 0;
		foreach($states as $state) {
			if($state->lp_understood == 0) {
				$not_understood_counter = $state->page_count;
			} elseif ($state->lp_understood == LoopProgress::UNDERSTOOD) {
				$understood_counter = $state->page_count;
			}
		}

		$understood_percent = min(($understood_counter / $pages->count_pages) * 100, 100);
		$not_understood_percent = min(($not_understood_counter / $pages->count_pages) * 100, 100);
		$not_edited_percent = 100 - ($understood_percent + $not_understood_percent);

		$html .= '<br>';
		$html .= '<div title="' . round($not_edited_percent,2) .  '" class="progressbar" style="display: flex;border:2px solid black; background-color:grey; opacity:0.5">';
		$html .= '<div title="' . round($understood_percent,2) .  '" class="progress_tracker" style="height:24px; display: flex;width:' . $understood_percent .'%;background-color:green;">' .'<span style="color:white">'. '' .'</span></div>';
		$html .= '<div title="' . round($not_understood_percent,2) .'" class="progress_tracker" style="height:24px; display: flex;float:left;width:' . $not_understood_percent .'%;background-color:red;">' .'<span style="color:white">'. '' .'</span></div>';
		$html .= '</div>';

		// ende progressbar

		return $html;
	}

	public static function showProgress() {
		global $wgOut;
		$user = $wgOut->getUser();

		// access control
		$mws = MediaWikiServices::getInstance();
		$userGroupManager = $mws->getUserGroupManager();
		$permissionManager = $mws->getPermissionManager();

		if ( !$permissionManager->userHasRight( $user, 'loopprogress' ) ) {    // loopfeedback-view
			return false;
		}
		return true;
	}

	public static function getNoteSaveButton() {
		global $wgOut;
		$html = '';

		$title = $wgOut->getTitle();

		if ( $title->getNamespace() != NS_MAIN ) {
			return $html;
		}

		$html .= '<button type="button" id="save_note_button">' . wfMessage( 'loopprogress-save-notes') . '</button>';

		return $html;
	}

	public static function getUnderstoodSelection() {
		global $wgOut;
		$html = '';
		$title = $wgOut->getTitle();

		// todo looking for a better solution
		if(!LoopProgress::hasProgressPermission()) {
			return $html;
		}

		if ( $title->getNamespace() != NS_MAIN ) {
			return $html;
		}

		$articleId = $title->getArticleID();

		$progress_state = LoopProgress::getProgress($articleId);

		$html .= '<br>';

		if($progress_state == LoopProgress::NOT_UNDERSTOOD) {
			$html .= '<button id="not_edited_button" class="not_active">' . wfMessage( 'loopprogress-not-edited') . '</button>';
			$html .= '<button id="understood_button" class="not_active">' . wfMessage( 'loopprogress-understood') . '</button>';
			$html .= '<button id="not_understood_button">' . wfMessage( 'loopprogress-not-understood') . '</button>';
		} else if ($progress_state == LoopProgress::UNDERSTOOD) {
			$html .= '<button id="not_edited_button" class="not_active">' . wfMessage( 'loopprogress-not-edited') . '</button>';
			$html .= '<button id="understood_button">' . wfMessage( 'loopprogress-understood') . '</button>';
			$html .= '<button id="not_understood_button" class="not_active">' . wfMessage( 'loopprogress-not-understood') . '</button>';
		} else if ($progress_state == LoopProgress::NOT_EDITED) {
			$html .= '<button id="not_edited_button" >' . wfMessage( 'loopprogress-not-edited') . '</button>';
			$html .= '<button id="understood_button" class="not_active">' . wfMessage( 'loopprogress-understood') . '</button>';
			$html .= '<button id="not_understood_button" class="not_active">' . wfMessage( 'loopprogress-not-understood') . '</button>';
		} else {
			$html .= '<button id="not_edited_button" class="not_active">' . wfMessage( 'loopprogress-not-edited') . '</button>';
			$html .= '<button id="understood_button" class="not_active">' . wfMessage( 'loopprogress-understood') . '</button>';
			$html .= '<button id="not_understood_button" class="not_active">' . wfMessage( 'loopprogress-not-understood') . '</button>';
		}

		return $html;

	}

	// this should be the only accesspoint
	// 0 = not understood, 1 = understood, 3 = not edited
	public static function getProgress($articleId) {
		global $wgOut;
		$user = $wgOut->getUser();

		/*
		$dbProvider = MediaWikiServices::getInstance()->getDBLoadBalancer();
		$dbr = $dbProvider->getConnection(DB_REPLICA);
		$lp = $dbr->newSelectQueryBuilder()
			->select([ 'lp_understood','lp_page','lp_user'])
			->from('loop_progress')
			->where([
					'lp_page = "' . $articleId . '"',
					'lp_user = "' . $user->getId() . '"'
				]
			)
			->caller(__METHOD__)->fetchRow();

		*/

		$lp = LoopProgressDBHandler::getUserNoteForPage($articleId, $user);

		if(!isset($lp->lp_understood)) {
			return LoopProgress::NOT_EDITED; //ProgressState::NotEdited;
		}

		//print_r($lp);
		//print_r($lp->lp_understood);

		return $lp->lp_understood;
	}

	// Todo use this to set content after page loaded
	public static function renderProgress() {
		global $wgOut;

		$return = '';

		//$mws = MediaWikiServices::getInstance();
		//$userGroupManager = $mws->getUserGroupManager();
		//$permissionManager = $mws->getPermissionManager();

		$title = $wgOut->getTitle();
		$user = $wgOut->getUser();

		$articleId = $title->getArticleID();

		// statement to check if the current page is a standard page
		// don't show progress on other pages
		// TODO make the other parts dependend on this statement

		// todo looking for a better solution
		if(!LoopProgress::hasProgressPermission()) {
			return '';
		}

		if ( $title->getNamespace() != NS_MAIN ) {
			return '';
		}


		// todo manage access rights and don't show on every page ...

		//get data from database

		// start
/*
		$dbProvider = MediaWikiServices::getInstance()->getDBLoadBalancer();
		$dbr = $dbProvider->getConnection(DB_REPLICA);
		$lp = $dbr->newSelectQueryBuilder()
			->select([ 'lp_understood','lp_user_note'])
			->from('loop_progress')
			->where([
					'lp_page = "' . $articleId . '"',
					'lp_user = "' . $user->getId() . '"'
				]
			)
			->caller(__METHOD__)->fetchRow();
		*/

		$lp = LoopProgressDBHandler::getUserNoteForPage($articleId, $user);

		//end


		$return .= '<div><textarea id="personal_notes" style="width:100%;margin-bottom:10px">';
		if(isset($lp->lp_user_note)) {
			$return .= $lp->lp_user_note;
		}
		$return .= '</textarea></div>';

		return $return;
	}



	public static function onBeforePageDisplay(OutputPage $out, Skin $skin)
	{
		// TODO don't show if it is a specialpage

		//TODO get understood and note from API

		$arcticle_id = $out->getTitle()->getArticleID();

		$lp_arcticle = array(
			'id' => $arcticle_id
		);

		$out->addJsConfigVars('lpArticle', $lp_arcticle);
		$out->addModules("loop.progress.js");

		return true;
	}

}


class SpecialLoopNote extends SpecialPage
{
	function __construct() {
		parent::__construct( 'LoopNote' );   // LoopFeedback
	}

	function execute( $par ) {
		$return = '';
		//global $wgLoopFeedbackLevel;

		$out = $this->getOutput();
		$request = $this->getRequest();
		$user = $this->getUser();
		//$csrfTokenSet = new CsrfTokenSet($request);
		Loop::handleLoopRequest( $out, $request, $user ); #handle editmode
		// $userOptionsLookup = MediaWikiServices::getInstance()->getUserOptionsLookup();
		// $editMode = $userOptionsLookup->getOption( $user, 'LoopEditMode', false, true );

		$permissionManager = MediaWikiServices::getInstance()->getPermissionManager();

		global $wgOut;
		$user = $wgOut->getUser();

		// access control
		$mws = MediaWikiServices::getInstance();
		$userGroupManager = $mws->getUserGroupManager();
		$permissionManager = $mws->getPermissionManager();

		if ( $permissionManager->userHasRight( $user, 'loopprogress' ) ) {    // loopfeedback-view
			// todo label
			$return .= '<h1>'. $this->msg('loopnote') . $out->setPageTitle( $this->msg('loopnote') ) . '</h1>';

			$return .= SpecialLoopNote::getAllNotes();


			$return .= '<button style="border:none;padding:0.1em 0.5em 0.1em 0.5em;background-color:#E2574C;border-radius:10px;float:right;margin-top:1em;"> PDF </button>'; // label


			$this->setHeaders();
			$out->addHTML( $return );
		}
	}

	/**
	 * Specify the specialpages-group loop
	 *
	 * @return string
	 */
	protected function getGroupName() {
		return 'loop';
	}


	private static function getAllNotes() {
		global $wgOut;

		$html = '';

		//$mws = MediaWikiServices::getInstance();
		//$userGroupManager = $mws->getUserGroupManager();
		//$permissionManager = $mws->getPermissionManager();

		//$title = $wgOut->getTitle();
		$user = $wgOut->getUser();

		$notes = LoopProgressDBHandler::getAllUserNotes($user);

		//new
		// $linkRenderer = $this->getLinkRenderer(); // for special pages
		$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
		$linkRenderer->setForceArticlePath(true);

		foreach ( $notes as $row ) {
			if(isset($row->lp_user_note) and $row->lp_user_note != '') {

				$article = Article::newFromId( $row->lp_page);

				$tocNumber = LoopProgressDBHandler::getTocNumber($row->lp_page);

				//print_r($tocNumber);
				//if(isset($tocNumber->lsi_toc_number)){
				//	echo($tocNumber->lsi_toc_number);
				//}

				if (isset($tocNumber->lsi_toc_number)) {
					$link = $linkRenderer->makeLink(
						Title::newFromID( $row->lp_page ),
						$tocNumber->lsi_toc_number //new HtmlArmor( $article->getTitle() )
					);
				}
				else
				{
					$link = ' ';
				}


				$html .= '<div style="border:0.1em solid #e9eef2; margin-top: 0.5em; padding:0em" class ="' . $article->getTitle()  . ' note_text_title' . '">';
				$html .= '<h5 style="background-color:#e9eef2; margin:0em; font-size:1.2em">' . '<span>' . $link . ' ' .'</span>' . $article->getTitle() .'</h5>';
				$html .= '<div style="margin:0em"  class="note_text" >';
				$html .= $row->lp_user_note;
				$html .= '</div>';

				$html .= '</div>';
			}
		}

		return $html;
	}

}


class LoopProgressPDF {

	public static function getXml($notes){
		$xml = LoopXml::notes2xml($notes);

		echo $xml;
	}

}

class LoopProgressDBHandler
{
	public static function getAllUserNotes($user) {
		$dbProvider = MediaWikiServices::getInstance()->getDBLoadBalancer();
		$dbr = $dbProvider->getConnection(DB_REPLICA);
		$res = $dbr->newSelectQueryBuilder()
			->select([ 'lp_page', 'lp_user_note'])
			->from('loop_progress')
			->where([
					'lp_user = "' . $user->getId() . '"'
				]
			)
			->caller(__METHOD__)->fetchResultSet();

		return $res;
	}

	public static function getUserNoteForPage($articleId, $user) {
		$dbProvider = MediaWikiServices::getInstance()->getDBLoadBalancer();
		$dbr = $dbProvider->getConnection(DB_REPLICA);
		$lp = $dbr->newSelectQueryBuilder()
			->select([ 'lp_understood','lp_user_note'])
			->from('loop_progress')
			->where([
					'lp_page = "' . $articleId . '"',
					'lp_user = "' . $user->getId() . '"'
				]
			)
			->caller(__METHOD__)->fetchRow();

		return $lp;
	}

	public static function getPageUnderstoodCountForUser($user) {
		$dbProvider = MediaWikiServices::getInstance()->getDBLoadBalancer();
		$dbr = $dbProvider->getConnection(DB_REPLICA);
		$res = $dbr->newSelectQueryBuilder()
			->select([ 'Count(lp_page) as page_understood_count', 'lp_user_note'])
			->from('loop_progress')
			->where([
					'lp_user = "' . $user->getId() . '"',
					'lp_understood = "' . LoopProgress::UNDERSTOOD . '"'
				]
			)
			->caller(__METHOD__)->fetchRow();

		return $res;
	}

	public static function getPageUnderstoodStatesForUser($user) {
		$dbProvider = MediaWikiServices::getInstance()->getDBLoadBalancer();
		$dbr = $dbProvider->getConnection(DB_REPLICA);
		$res = $dbr->newSelectQueryBuilder()
			->select([ 'Count(lp_page) as page_count', 'lp_understood'])
			->from('loop_progress')
			->where([
					'lp_user = "' . $user->getId() . '"'
				]
			)->groupBy('lp_understood')
			->caller(__METHOD__)->fetchResultSet();

		return $res;
	}

	public static function getAllPages() {
		$dbProvider = MediaWikiServices::getInstance()->getDBLoadBalancer();
		$dbr = $dbProvider->getConnection(DB_REPLICA);
		$pages = $dbr->newSelectQueryBuilder()
			->select([ 'COUNT(lsi_id) as count_pages'])
			->from('loop_structure_items')
			->caller(__METHOD__)->fetchRow();

		return $pages;
	}

	public static function getTocNumber($pageId) {
		$dbProvider = MediaWikiServices::getInstance()->getDBLoadBalancer();
		$dbr = $dbProvider->getConnection(DB_REPLICA);
		$tocNumber = $dbr->newSelectQueryBuilder()
			->select([ 'lsi_toc_number'])
			->from('loop_structure_items')
			->where(['lsi_article ="' . $pageId . '"'])
			->caller(__METHOD__)->fetchRow();

		return $tocNumber;
	}

}
