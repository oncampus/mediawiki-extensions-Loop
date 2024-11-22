<?php

use MediaWiki\MediaWikiServices;

class LoopProgress
{
	public static function showProgressBar(){
		return true;
	}

	public static function renderProgressBar() {
		global $wgOut;
		$user = $wgOut->getUser();

		$html = '';

		//get data from db

		// get pages total
		$dbProvider = MediaWikiServices::getInstance()->getDBLoadBalancer();
		$dbr = $dbProvider->getConnection(DB_REPLICA);
		$lp = $dbr->newSelectQueryBuilder()
			->select([ 'COUNT(page_id) as count_pages']) // ,'page_namespace'
			->from('page')
			->where([
					'page_namespace = "' . NS_MAIN . '"'
				]
			)
			->caller(__METHOD__)->fetchRow();

		//echo($lp->count_pages);


		// get understood pages
		$dbProvider = MediaWikiServices::getInstance()->getDBLoadBalancer();
		$dbr = $dbProvider->getConnection(DB_REPLICA);
		$res = $dbr->newSelectQueryBuilder()
			->select([ 'Count(lp_page) as page_understood_count', 'lp_user_note'])
			->from('loop_progress')
			->where([
					'lp_user = "' . $user->getId() . '"',
					'lp_understood = "' . 1 . '"'
				]
			)
			->caller(__METHOD__)->fetchRow();

		//print_r($res->page_understood_count);

		$understood_percent = ($res->page_understood_count / $lp->count_pages) * 100;

		// create progressbar
		$html .= '<div class="progressbar" style="border:2px solid black">';
		$html .= '<div class="progress_tracker" style="height:24px;width:' . $understood_percent .'%;background-color:#366b9f;"></div>';
		$html .= '</div>';

		return $html;
	}

	public static function showProgress() {
		global $wgOut;

		return true;
	}

	public static function getNoteSaveButton() {
		global $wgOut;
		$html = '';


		$title = $wgOut->getTitle();

		if ( $title->getNamespace() != NS_MAIN ) {
			return $html;
		}

		$html .= '<button type="button" id="save_note_button">Save Notes</button>';

		return $html;
	}

	public static function getUnderstoodSelection() {

		// todo not dry
		global $wgOut;
		$html = '';
		$title = $wgOut->getTitle();
		if ( $title->getNamespace() != NS_MAIN ) {
			return $html;
		}


		$html .=  '<br><select name="understood" id="page_understood">
					<option value="not_edited">Nicht Bearbeitet</option>
					<option value="understood">Verstanden</option>  //TODO Label
					<option value="not_understood">Nicht Verstanden</option> //TODO Label
					</select>';

		return $html;

	}

	public static function getProgress($articleId) {
		//$res = '';

		// Todo is there a better way???
		global $wgOut;
		$user = $wgOut->getUser();


		$dbProvider = MediaWikiServices::getInstance()->getDBLoadBalancer();
		$dbr = $dbProvider->getConnection(DB_REPLICA);
		$lp = $dbr->newSelectQueryBuilder()
			->select([ 'lp_understood'])
			->from('loop_progress')
			->where([
					'lp_page = "' . $articleId . '"',
					'lp_user = "' . $user->getId() . '"'
				]
			)
			->caller(__METHOD__)->fetchRow();

		return $lp;

		//return $res;
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

		if ( $title->getNamespace() != NS_MAIN ) {
			return '';
		}


		// todo manage access rights and don't show on every page ...

		//get data from database
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

		$out->setPageTitle( $this->msg('loopfeedback') );

		// todo label
		$return .= '<h1>'. 'Notizen' . '</h1>';

		$return .= SpecialLoopNote::getAllNotes();


		$this->setHeaders();
		$out->addHTML( $return );
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

		//$articleId = $title->getArticleID();



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

		foreach ( $res as $row ) {
			if(isset($row->lp_user_note) and $row->lp_user_note != '') {
						// style="border:1px solid black"
				$article = Article::newFromId( $row->lp_page);

				$html .= '<div class ="' . $article->getTitle() . ' note_text_title' . '">';
				$html .= '<h4 style="background-color: f0f0f0">' . $article->getTitle() . '</h4>';
				$html .= '<div class="note_text" style="border:1px solid black">';
				$html .= $row->lp_user_note;
				$html .= '</div>';
				$html .= '</div>';
			}
		}

		return $html;
	}



// todo
// statt einem select soll es 3 button mit den Status verstanden, nicht verstanden ... geben


}
