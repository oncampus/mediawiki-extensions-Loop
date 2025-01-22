<?php

if ( !defined( 'MEDIAWIKI' ) ) die ( "This file cannot be run standalone.\n" );

use MediaWiki\MediaWikiServices;
use Wikimedia\ParamValidator\ParamValidator;


abstract class ApiLoopProgressBase extends ApiBase {
	public function __construct($main, $action)
	{
		parent::__construct($main, $action);
	}

	protected function generateId()
	{
		/*
		 * This will return a 128-bit string in base-16, resulting
		 * in a 32-character (at max) string of hexadecimal characters.
		 * Pad the string to full 32-char length if the value is lower.
		 */
		$idGenerator = MediaWikiServices::getInstance()->getGlobalIdGenerator();
		$id = $idGenerator->newTimestampedUID128(16);
		return str_pad($id, 32, 0, STR_PAD_LEFT);
	}
}

class ApiLoopProgressSave extends ApiLoopProgressBase
{
	public function __construct($main, $action)
	{
		parent::__construct($main, $action);
	}

	public function execute()
	{
		$user = $this->getUser();

		$result   = $this->getResult();

		// extract params
		$params = $this->extractRequestParams();

		// get page object
		$pageObj = $this->getTitleOrPageId( $params, 'fromdb' );
		if ( !$pageObj->exists() ) {
			$this->dieWithError(
				$this->msg( 'loopfeedback-invalid-page-id' )->escaped(),
				'notanarticle'
			);
		}

		$progress['lp_id'] = $this->generateId(); // todo test
		$progress['lp_page'] = $pageObj->getId();
		$progress['lp_user'] = $user->getId();
		$progress['lp_understood'] = $params['understood'];
		$progress['lp_timestamp'] = wfTimestampNow();

		// first look if an entry exits
		$dbProvider = MediaWikiServices::getInstance()->getDBLoadBalancer();
		$dbr = $dbProvider->getConnection(DB_REPLICA);
		$lp = $dbr->newSelectQueryBuilder()
			->select([ 'lp_id','lp_user'])
			->from('loop_progress')
			->where([
					'lp_page = "' . $pageObj->getId() . '"',
					'lp_user = "' . $user->getId() . '"'
				]
			)
			->caller(__METHOD__)->fetchRow();


		// update if there is an entry
		if(isset($lp->lp_id) and isset($lp->lp_user))
		{

			// update the notes
			$dbw = $dbProvider->getConnection(DB_PRIMARY);
			$dbw->update(
				'loop_progress',
				[
					'lp_understood' => $params['understood'],
					'lp_timestamp' => $progress['lp_timestamp']
				],
				[
					'lp_page' => $pageObj->getId(),
					'lp_user' => $user->getId()
				],
				__METHOD__
			);
		}
		else //insert if there is no entry
		{

			$dbw = wfGetDB( DB_PRIMARY );
			$dbw->insert(
				'loop_progress',
				$progress,
				__METHOD__
			);
		}

		$result->addValue($this->getModuleName(), 'lp_id', $progress['lp_id']); // todo
	}

	public function getAllowedParams()
	{
		$ret = array(
			'title' => null,
			'pageid' => array(
				ParamValidator::PARAM_TYPE => 'integer',
			),
			'understood' => array(
				ParamValidator::PARAM_TYPE => 'integer',
				ParamValidator::PARAM_REQUIRED => false,
			)
		);

		return $ret;
	}


	public function getParamDescription()
	{
		$p = $this->getModulePrefix();
		return array(
			'title' => "Title of the page to submit feedback for. Cannot be used together with {$p}pageid",
			'pageid' => "ID of the page to submit feedback for. Cannot be used together with {$p}title",
			//'anontoken' => 'Token for anonymous users',
			'understood' => 'understood'
		);
	}


	public function mustBePosted()
	{
		return false;
	}


	public function isWriteMode()
	{
		return true;
	}


	public function getPossibleErrors()
	{
		return array_merge($this->getPossibleErrors(), array(
			array('code' => 'invalidpage', 'info' => 'ArticleFeedback is not enabled on this page'),
			array('code' => 'invalidpageid', 'info' => 'Page ID is missing or invalid'),
			array('code' => 'missinguser', 'info' => 'User info is missing')
		));
	}

	public function getDescription()
	{
		return array(
			'Save loop progress'
		);
	}

	protected function getExamples()
	{
		return array(
			'api.php?action=loopprogress-save'
		);
	}

	public function getVersion()
	{
		return __CLASS__ . ': version 1.0';
	}

}


class ApiLoopProgressLoad extends ApiBase
{

	public function __construct($main, $action)
	{
		parent::__construct($main, $action);
	}

	public function execute()
	{
		// TODO manage permissions
		/*
		$permissionManager = MediaWikiServices::getInstance()->getPermissionManager();
		//$result   = $this->getResult();

		$user = $this->getUser();
		if ( $user->getBlock() != null ) {
			$this->dieWithError(
				$this->msg( 'loopfeedback-error-blocked' )->escaped(),
				'userblocked'
			);
		}

		if (!$permissionManager->userHasRight( $user, 'loopfeedback-view' ) ) {
			$this->dieWithError(
				$this->msg( 'loopfeedback-error-nopermission' )->escaped(),
				'nopermission'
			);
		}
		*/


		$result   = $this->getResult();

		$params = $this->extractRequestParams();

		$result->addValue($this->getModuleName(), 'test', $params);
	}

	public function getAllowedParams() {
		$ret = array(
			'title' => null,
			'pageid' => array(
				ParamValidator::PARAM_TYPE     => 'integer',
			),
			'progress' => array(
				ParamValidator::PARAM_TYPE     => 'integer',
				ParamValidator::PARAM_REQUIRED => false,
			)
		);

		return $ret;
	}


	public function getParamDescription()
	{
		return array();
	}


	public function mustBePosted()
	{
		return false;
	}


	public function isWriteMode()
	{
		return true;
	}


	public function getPossibleErrors()
	{
		return array_merge($this->getPossibleErrors(), array(
			array('code' => 'invalidpage', 'info' => 'ArticleFeedback is not enabled on this page'),
			array('code' => 'invalidpageid', 'info' => 'Page ID is missing or invalid'),
			array('code' => 'missinguser', 'info' => 'User info is missing'),
		));
	}

	public function getDescription()
	{
		return array(
			'Get LOOP Progress'
		);
	}

	protected function getExamples()
	{
		return array(
			'api.php?action=loopprogress-load'
		);
	}

	public function getVersion()
	{
		return __CLASS__ . ': version 1.0';
	}
}



class ApiLoopProgressSaveNote extends ApiLoopProgressBase
{

	public function __construct($main, $action)
	{
		parent::__construct($main, $action);
	}

	public function execute()
	{
		// TODO manage permissions
		/*
		$permissionManager = MediaWikiServices::getInstance()->getPermissionManager();
		//$result   = $this->getResult();

		$user = $this->getUser();
		if ( $user->getBlock() != null ) {
			$this->dieWithError(
				$this->msg( 'loopfeedback-error-blocked' )->escaped(),
				'userblocked'
			);
		}

		if (!$permissionManager->userHasRight( $user, 'loopfeedback-view' ) ) {
			$this->dieWithError(
				$this->msg( 'loopfeedback-error-nopermission' )->escaped(),
				'nopermission'
			);
		}
		*/


		$user = $this->getUser();

		$result   = $this->getResult();

		// extract params
		$params = $this->extractRequestParams();

		// get page object
		$pageObj = $this->getTitleOrPageId( $params, 'fromdb' );
		if ( !$pageObj->exists() ) {
			$this->dieWithError(
				$this->msg( 'loopfeedback-invalid-page-id' )->escaped(),
				'notanarticle'
			);
		}

		$progress['lp_id'] = $this->generateId();
		$progress['lp_page'] = $pageObj->getId();
		$progress['lp_user'] = $user->getId();
		$progress['lp_timestamp'] = wfTimestampNow();

		// check max length
		if(strlen($params['user_note']) > LoopProgress::NOTE_MAX_LENGTH) {
			$progress['lp_user_note'] = substr($params['user_note'],0,LoopProgress::NOTE_MAX_LENGTH);
		}
		else {
			$progress['lp_user_note'] = $params['user_note'];
		}

		//print_r($progress['lp_user_note']);

		// first look if an entry exits
		$dbProvider = MediaWikiServices::getInstance()->getDBLoadBalancer();
		$dbr = $dbProvider->getConnection(DB_REPLICA);
		$lp = $dbr->newSelectQueryBuilder()
			->select([ 'lp_id','lp_user'])
			->from('loop_progress')
			->where([
					'lp_page = "' . $pageObj->getId() . '"',
					'lp_user = "' . $user->getId() . '"'
				]
			)
			->caller(__METHOD__)->fetchRow();


		// update if there is an entry
        if(isset($lp->lp_id) and isset($lp->lp_user))
		{
            // update the notes
			$dbw = $dbProvider->getConnection(DB_PRIMARY);
			$dbw->update(
				'loop_progress',
				[
					'lp_user_note' => $progress['lp_user_note'],
					'lp_timestamp' => $progress['lp_timestamp']

				],
				[
					'lp_page' => $pageObj->getId(),
					'lp_user' => $user->getId(),

				],
				__METHOD__
			);
		}

        else //insert if there is no entry
		{

			$dbw = wfGetDB( DB_PRIMARY );
			$dbw->insert(
				'loop_progress',
				$progress,
				__METHOD__
			);
		}

		$result->addValue($this->getModuleName(), 'lp_id', $progress['lp_id']);
	}


	public function getAllowedParams() {
		$ret = array(
			'title' => null,
			'pageid' => array(
				ParamValidator::PARAM_TYPE     => 'integer',
			),
			'user_note' => array(
				ParamValidator::PARAM_TYPE     => 'string',
				ParamValidator::PARAM_REQUIRED => false,
			)
		);

		return $ret;
	}


	public function getParamDescription()
	{
		return array();
	}


	public function mustBePosted()
	{
		return false;
	}


	public function isWriteMode()
	{
		return true;
	}


	public function getPossibleErrors()
	{
		return array_merge($this->getPossibleErrors(), array(
			array('code' => 'invalidpage', 'info' => 'ArticleFeedback is not enabled on this page'),
			array('code' => 'invalidpageid', 'info' => 'Page ID is missing or invalid'),
			array('code' => 'missinguser', 'info' => 'User info is missing'),
		));
	}

	public function getDescription()
	{
		return array(
			'Save LOOP Progress Note'
		);
	}

	protected function getExamples()
	{
		return array(
			'api.php?action=loopprogress-save-note'
		);
	}

	public function getVersion()
	{
		return __CLASS__ . ': version 1.0';
	}
}

class ApiLoopProgressLoadNote extends ApiBase
{

	public function __construct($main, $action)
	{
		parent::__construct($main, $action);
	}

	public function execute()
	{
		// TODO manage permissions
		/*
		$permissionManager = MediaWikiServices::getInstance()->getPermissionManager();
		//$result   = $this->getResult();

		$user = $this->getUser();
		if ( $user->getBlock() != null ) {
			$this->dieWithError(
				$this->msg( 'loopfeedback-error-blocked' )->escaped(),
				'userblocked'
			);
		}

		if (!$permissionManager->userHasRight( $user, 'loopfeedback-view' ) ) {
			$this->dieWithError(
				$this->msg( 'loopfeedback-error-nopermission' )->escaped(),
				'nopermission'
			);
		}
		*/

		$user = $this->getUser();

		$result   = $this->getResult();

		// extract params
		$params = $this->extractRequestParams();

		// get page object
		$pageObj = $this->getTitleOrPageId( $params, 'fromdb' );
		if ( !$pageObj->exists() ) {
			$this->dieWithError(
				$this->msg( 'loopfeedback-invalid-page-id' )->escaped(),
				'notanarticle'
			);
		}

		$dbProvider = MediaWikiServices::getInstance()->getDBLoadBalancer();
		$dbr = $dbProvider->getConnection(DB_REPLICA);
		$lp = $dbr->newSelectQueryBuilder()
			->select([ 'lp_user_note'])
			->from('loop_progress')
			->where([
					'lp_page = "' . $pageObj->getId() . '"',
					'lp_user = "' . $user->getId() . '"'
				]
			)
			->caller(__METHOD__)->fetchRow();

		$result->addValue($this->getModuleName(), 'lp_note', $lp->lp_user_note);
	}

	public function getAllowedParams() {
		$ret = array(
			'title' => null,
			'pageid' => array(
				ParamValidator::PARAM_TYPE     => 'integer',
			)
		);

		return $ret;
	}


	public function getParamDescription()
	{
		return array();
	}


	public function mustBePosted()
	{
		return false;
	}


	public function isWriteMode()
	{
		return true;
	}


	public function getPossibleErrors()
	{
		return array_merge($this->getPossibleErrors(), array(
			array('code' => 'invalidpage', 'info' => 'ArticleFeedback is not enabled on this page'),
			array('code' => 'invalidpageid', 'info' => 'Page ID is missing or invalid'),
			array('code' => 'missinguser', 'info' => 'User info is missing'),
		));
	}

	public function getDescription()
	{
		return array(
			'Get LOOP  Note'
		);
	}

	protected function getExamples()
	{
		return array(
			'api.php?action=loopprogress-load-note'
		);
	}

	public function getVersion()
	{
		return __CLASS__ . ': version 1.0';
	}
}


