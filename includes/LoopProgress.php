<?php

use MediaWiki\MediaWikiServices;


class LoopProgress
{
 	const  NOT_UNDERSTOOD = 0;
	const  UNDERSTOOD = 1;
	const  NOT_EDITED = 3;
	const NOTE_MAX_LENGTH = 1500;

	public static function onArticleDeleteComplete( &$article, User &$user, $reason, $id, $content, LogEntry $logEntry, $archivedRevisionCount ) {

		echo($id);
		//LoopProgressDBHandler::removeAllNotesWithId ( $id );

		return true;
	}

	public static function showProgressBar(){
		global $wgOut, $wgPersonalizationFeature;

		$user = $wgOut->getUser();

		$mws = MediaWikiServices::getInstance();
		$userGroupManager = $mws->getUserGroupManager();
		$permissionManager = $mws->getPermissionManager();

		if ( !$permissionManager->userHasRight( $user, 'loopprogress' ) ) {
			return false;
		}
		if(!$wgPersonalizationFeature == "true") {
			return false;
		}
		return true;
	}

	public static function hasProgressPermission() {
		global $wgOut, $wgPersonalizationFeature;
		$user = $wgOut->getUser();

		$mws = MediaWikiServices::getInstance();
		$userGroupManager = $mws->getUserGroupManager();
		$permissionManager = $mws->getPermissionManager();

		if($wgPersonalizationFeature == "false") {
			return false;
		}

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
		// use counter of loop_structure_items instead?
		//$pages = LoopProgressDBHandler::getAllPages();
		$pages = LoopProgressDBHandler::getTocElementCount(); // page_count

		// get understood pages
		//$understoodPages = LoopProgressDBHandler::getPageUnderstoodCountForUser($user);

		$understoodPages = LoopProgressDBHandler::getPageUnderstoodCountForUser($user);

		$understood_percent = round(min(($understoodPages->page_understood_count / $pages->page_count) * 100, 100),0);

		$html .=
			'<div class="main">
				<div class="prog-wrapper">
					<div class="loading-wrapper" style="grid-template-columns: '. $understood_percent . '% auto;">
						<div class="loading-bar-prog">
						</div>
						<div class="loading-bar">
						</div>
						<div class="loading-contents loading-contents-left" style="clip-path: inset(0 calc(100% - '. $understood_percent . '%) 0 0);">
							<p class="loading-state p-0 m-0">'. $understood_percent . '%</p>
						</div>
						<div class="loading-contents loading-contents-right">
							<p class="loading-state p-0 m-0">'. $understood_percent . '%</p>
						</div>
					</div>
				</div>
			</div>';

		return $html;
	}

	public static function showProgress() {
		global $wgOut;
		$user = $wgOut->getUser();

		// access control
		$mws = MediaWikiServices::getInstance();
		$userGroupManager = $mws->getUserGroupManager();
		$permissionManager = $mws->getPermissionManager();

		if ( !$permissionManager->userHasRight( $user, 'loopprogress' ) ) {
			return false;
		}
		return true;
	}

	public static function getNoteSaveButton() {
		global $wgOut;
		$html = '';

		$title = $wgOut->getTitle();

		if(!LoopProgress::hasProgressPermission()) {
			return $html;
		}

		if ( $title->getNamespace() != NS_MAIN ) {
			return $html;
		}

		$html .= '<button type="button" id="save_note_button" class="progress-button status-saved">' .'<span id="save_note_button_img"></span><p id="status_not_saved">'. wfMessage( 'loopprogress-save-notes') . '</p><p id="status_saved" class="status-active">' . wfMessage('loopprogress-note-saved') .'</p></button>';

		return $html;
	}

	public static function getUnderstoodSelection() {
		global $wgOut;
		$html = '';
		$title = $wgOut->getTitle();

		if(!LoopProgress::hasProgressPermission()) {
			return $html;
		}

		if ( $title->getNamespace() != NS_MAIN ) {
			return $html;
		}

		$articleId = $title->getArticleID();

		$progress_state = LoopProgress::getProgress($articleId);

		if($progress_state == LoopProgress::NOT_UNDERSTOOD) {
			$html .= '<button id="not_edited_button" class="progress-button not_active">' . wfMessage( 'loopprogress-not-edited') . '</button>';
			$html .= '<button id="understood_button" class="progress-button not_active">' . '<span> ✓ </span>' . wfMessage( 'loopprogress-understood') . '</button>';
			$html .= '<button id="not_understood_button" class="progress-button">' . '<span> X </span>' . wfMessage( 'loopprogress-not-understood') . '</button>';
		} else if ($progress_state == LoopProgress::UNDERSTOOD) {
			$html .= '<button id="not_edited_button" class="progress-button not_active">' . wfMessage( 'loopprogress-not-edited') . '</button>';
			$html .= '<button id="understood_button" class="progress-button">' . '<span> ✓ </span>' . wfMessage( 'loopprogress-understood') . '</button>';
			$html .= '<button id="not_understood_button" class="progress-button not_active">' . '<span> X </span>' . wfMessage( 'loopprogress-not-understood') . '</button>';
		} else if ($progress_state == LoopProgress::NOT_EDITED) {
			$html .= '<button id="not_edited_button" class="progress-button">' . wfMessage( 'loopprogress-not-edited') . '</button>';
			$html .= '<button id="understood_button" class="progress-button not_active">' . '<span> ✓ </span>' . wfMessage( 'loopprogress-understood') . '</button>';
			$html .= '<button id="not_understood_button" class="progress-button not_active">' . '<span> X </span>' . wfMessage( 'loopprogress-not-understood') . '</button>';
		} else {
			$html .= '<button id="not_edited_button" class="progress-button not_active">' . wfMessage( 'loopprogress-not-edited') . '</button>';
			$html .= '<button id="understood_button" class="progress-button not_active">' . '<span> ✓ </span>' . wfMessage( 'loopprogress-understood') . '</button>';
			$html .= '<button id="not_understood_button" class="progress-button not_active">' . '<span> X </span>' . wfMessage( 'loopprogress-not-understood') . '</button>';
		}

		return $html;

	}

	// this should be the only accesspoint
	// 0 = not understood, 1 = understood, 3 = not edited
	public static function getProgress($articleId) {
		global $wgOut;
		$user = $wgOut->getUser();

		$lp = LoopProgressDBHandler::getUserNoteForPage($articleId, $user);

		if(!isset($lp->lp_understood)) {
			return LoopProgress::NOT_EDITED;
		}

		return $lp->lp_understood;
	}

	public static function createNotebookLink()
	{
		if(!LoopProgress::hasProgressPermission()) {
			return;
		}

		$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
		$linkRenderer->setForceArticlePath(true);

		echo $linkRenderer->makeLink( new TitleValue( NS_SPECIAL, 'LoopNote' ), new HtmlArmor('<div class="float-right mt-1 ml-2"><span style="color:orange; font-size: 2em;" id="notebook-logo"></span></div>'));
	}

	public static function renderProgress() {
		global $wgOut;

		$return = '';

		$title = $wgOut->getTitle();
		$user = $wgOut->getUser();

		$articleId = $title->getArticleID();

		if(!LoopProgress::hasProgressPermission()) {
			return '';
		}

		if ( $title->getNamespace() != NS_MAIN ) {
			return '';
		}

		$lp = LoopProgressDBHandler::getUserNoteForPage($articleId, $user);

		$return .= '<div><textarea id="personal_notes" maxlength="' . LoopProgress::NOTE_MAX_LENGTH . '">';
		if(isset($lp->lp_user_note)) {
			$return .= $lp->lp_user_note;
		}
		$return .= '</textarea></div>';

		return $return;
	}



	public static function onBeforePageDisplay(OutputPage $out, Skin $skin)
	{
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
	function __construct()
	{
		parent::__construct('LoopNote');
	}

	function execute($sub)
	{
		$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
		$linkRenderer->setForceArticlePath(true);

		$return = '';

		$out = $this->getOutput();
		$request = $this->getRequest();
		$user = $this->getUser();

		Loop::handleLoopRequest($out, $request, $user); #handle editmode
		// $userOptionsLookup = MediaWikiServices::getInstance()->getUserOptionsLookup();
		// $editMode = $userOptionsLookup->getOption( $user, 'LoopEditMode', false, true );

		$permissionManager = MediaWikiServices::getInstance()->getPermissionManager();

		global $wgOut;
		$user = $wgOut->getUser();

		// access control
		$mws = MediaWikiServices::getInstance();
		$userGroupManager = $mws->getUserGroupManager();
		$permissionManager = $mws->getPermissionManager();

		if ($permissionManager->userHasRight($user, 'loopprogress')) {
			$return .= '<div class="mb-3"><h1 style="display:inline">' . $this->msg('loopnote') . $out->setPageTitle($this->msg('loopnote')) . '</h1>' . '<div id="extend-all" type="button"></div></div>';

			$return .= '<div id="note-collection">';
			$return .= SpecialLoopNote::getAllNotes();

			$return .= '</div>';

			$this->setHeaders();
			$out->addHTML($return);
		}
	}

	/**
	 * Specify the specialpages-group loop
	 *
	 * @return string
	 */
	protected function getGroupName()
	{
		return 'loop';
	}

	private static function getAllNotes()
	{
		global $wgOut;

		$html = '';

		$mws = MediaWikiServices::getInstance();
		$userGroupManager = $mws->getUserGroupManager();
		$permissionManager = $mws->getPermissionManager();

		$user = $wgOut->getUser();

		if (!$permissionManager->userHasRight($user, 'loopprogress')) {
			return $html;
		}

		$notes = LoopProgressDBHandler::getAllUserNotesWithTocNumber($user);

		$extra_notes = LoopProgressDBHandler::getAllUserNotesWithoutTocNumber($user);

		$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
		$linkRenderer->setForceArticlePath(true);

		foreach ($notes as $note) {
			if (isset($note->lp_user_note) and $note->lp_user_note != '') {

				$article = Article::newFromId($note->lp_page);

				if(isset($note->lsi_toc_number)) {
					$link = $linkRenderer->makeLink(
						Title::newFromID($note->lp_page),
						new HtmlArmor($note->lsi_toc_number)
					);
				} else {
					$link = ' ';
				}

				$id = uniqid();
				$html .= '<div class="notebook-row w-100 mb-1 overflow-hidden">';
				$html .= '<input id="note-' . $id . '" type="checkbox" name="personal-note">';
				$html .= '<label for="note-' . $id . '" class="d-block cursor-pointer mb-0 mr-2 w-100 p-2 pl-2">' . $link . ' ' . $article->getTitle() . '</label>';
				$html .= '<div class="notebook-content overflow-hidden">';
				$html .= '<div class="m-2 m-md-3">' . $note->lp_user_note . '</div>';
				$html .= '</div>';
				$html .= '</div>';
			}
		}

		foreach ($extra_notes as $note) {
			if (isset($note->lp_user_note) and $note->lp_user_note != '') {

				$article = Article::newFromId($note->lp_page);

				if(isset($note->lsi_toc_number)) {
					$link = $linkRenderer->makeLink(
						Title::newFromID($note->lp_page),
						new HtmlArmor($note->lsi_toc_number)
					);
				} else {
					$link = ' ';
				}

				$id = uniqid();
				$html .= '<div class="notebook-row w-100 mb-1 overflow-hidden">';
				$html .= '<input id="note-' . $id . '" type="checkbox" name="personal-note">';
				$html .= '<label for="note-' . $id . '" class="d-block cursor-pointer mb-0 mr-2 w-100 p-2 pl-2">' . $link . ' ' . $article->getTitle() . '</label>';
				$html .= '<div class="notebook-content overflow-hidden">';
				$html .= '<div class="m-2 m-md-3">' . $note->lp_user_note . '</div>';
				$html .= '</div>';
				$html .= '</div>';
			}
		}

		return $html;
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

	public static function getAllUserNotesWithTocNumber($user){
		$dbProvider = MediaWikiServices::getInstance()->getDBLoadBalancer();
		$dbr = $dbProvider->getConnection(DB_REPLICA);
		$res = $dbr->newSelectQueryBuilder()
			->select([ 'lp_page', 'lp_user_note','lsi_article,lsi_toc_number'])
			->from('loop_progress')
			->leftjoin('loop_structure_items',null,'lp_page=lsi_article')
			->where([
					'lp_user = "' . $user->getId() . '"',
					'lsi_toc_number != ""',
					'lp_user_note != ""'
				]
			)
			->orderBy('lsi_sequence')
			->caller(__METHOD__)->fetchResultSet();

		return $res;
	}

	public static function getAllUserNotesWithoutTocNumber($user){
		$dbProvider = MediaWikiServices::getInstance()->getDBLoadBalancer();
		$dbr = $dbProvider->getConnection(DB_REPLICA);
		$res = $dbr->newSelectQueryBuilder()
			->select([ 'lp_page', 'lp_user_note','lsi_article,lsi_toc_number'])
			->from('loop_progress')
			->leftjoin('loop_structure_items',null,'lp_page=lsi_article')
			->where([
					'lp_user = "' . $user->getId() . '"',
					'lsi_toc_number IS NULL',
					'lp_user_note != ""'
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
/*
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
*/

/*
 * 		$dbProvider = MediaWikiServices::getInstance()->getDBLoadBalancer();
		$dbr = $dbProvider->getConnection(DB_REPLICA);
		$res = $dbr->newSelectQueryBuilder()
			->select([ 'lp_page', 'lp_user_note','lsi_article,lsi_toc_number'])
			->from('loop_progress')
			->join('loop_structure_items',null,'lp_page=lsi_article')
			->where([
					'lp_user = "' . $user->getId() . '"',
					'lsi_toc_number != ""',
					'lp_user_note != ""'
				]
			)
			->orderBy('lsi_toc_number')
			->caller(__METHOD__)->fetchResultSet();

		return $res;
 *
 */

	public static function getPageUnderstoodCountForUser($user) {
		$dbProvider = MediaWikiServices::getInstance()->getDBLoadBalancer();
		$dbr = $dbProvider->getConnection(DB_REPLICA);
		$res = $dbr->newSelectQueryBuilder()
			->select([ 'Count(lp_page) as page_understood_count', 'lp_user_note'])
			->from('loop_progress')
			->join('loop_structure_items',null,'lp_page=lsi_article')
			->where([
					'lp_user = "' . $user->getId() . '"',
					'lp_understood = "' . LoopProgress::UNDERSTOOD . '"',
					'lsi_toc_number != ""'
				]
			)
			->caller(__METHOD__)->fetchRow();

		return $res;
	}

	public static function getTocElementCount() {
		$dbProvider = MediaWikiServices::getInstance()->getDBLoadBalancer();
		$dbr = $dbProvider->getConnection(DB_REPLICA);
		$res = $dbr->newSelectQueryBuilder()
			->select([ 'Count(lsi_id) as page_count'])
			->from('loop_structure_items')
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

	/*
	public static function deleteAllNotesForUser($userId) {
		$dbr = wfGetDB( DB_PRIMARY );
		$dbr->delete(
			'loop_progress',
			'lp_user = ' . $userId,
			__METHOD__
		);

	}

	public static function deleteProgressOlderThan($xDays){ //$olderThanDays) {
		$timestamp = '20241209000000';


		$givenTime = wfTimestamp(TS_UNIX, $timestamp);
		$currentTime = time();
		$threshold = $xDays * 24 * 60 * 60;


		$thresholdDate = $currentTime - $threshold;

		if (($currentTime - $givenTime) > $threshold) {
			echo "The timestamp is older than $xDays days.\n";
		} else {
			echo "The timestamp is within $xDays days.\n";
		}

		$dbr = wfGetDB( DB_PRIMARY );
		$dbr->delete(
			'loop_progress',
			'lp_timestamp < ' . $thresholdDate,
			__METHOD__
		);
	}
	*/

	// dalem new
	// todo test
	public static function removeAllNotesWithId ( $article ) {

		$dbr = wfGetDB( DB_PRIMARY );
		$dbr->delete(
			'loop_progress',
			'lp_page = ' . $article,
			__METHOD__
		);

		return true;
	}


}
