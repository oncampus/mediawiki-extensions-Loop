<?php

if ( !defined( 'MEDIAWIKI' ) ) die ( "This file cannot be run standalone.\n" );

use MediaWiki\MediaWikiServices;
use Wikimedia\ParamValidator\ParamValidator;


class ApiLoopProgressSave extends ApiBase
{
	public function __construct($main, $action)
	{
		parent::__construct($main, $action);
	}

	public function execute()
	{

		$permissionManager = MediaWikiServices::getInstance()->getPermissionManager();
		$result = $this->getResult();

		$user = $this->getUser();
		if ($user->getBlock() != null) {
			$this->dieWithError(
				$this->msg('loopfeedback-error-blocked')->escaped(),
				'userblocked'
			);
		}

		if (!$permissionManager->userHasRight($user, 'loopfeedback-view')) {
			$this->dieWithError(
				$this->msg('loopfeedback-error-nopermission')->escaped(),
				'nopermission'
			);
		}


		$params = $this->extractRequestParams();

		// get page object
		$pageObj = $this->getTitleOrPageId($params, 'fromdb');
		if (!$pageObj->exists()) {
			$this->dieWithError(
				$this->msg('loopfeedback-invalid-page-id')->escaped(),
				'notanarticle'
			);
		}

		$feedback['lp_id'] = $this->generateId();
		$feedback['lp_page'] = $pageObj->getId();
		$feedback['lp_user'] = $user->getId();
		//$feedback['lf_user_text'] = $user->getName();
		//$feedback['lf_understood'] = $params['rating'];
		//$feedback['lf_comment'] = trim($params['comment']);
		//$feedback['lf_timestamp'] = wfTimestampNow();
		//$feedback['lf_archive_timestamp'] = '00000000000000';

		$dbw = wfGetDB(DB_PRIMARY);
		$dbw->insert(
			'loop_progress',
			$feedback,
			__METHOD__
		);

		$result->addValue($this->getModuleName(), 'lp_id', $feedback['lp_id']);
	}

	public function getAllowedParams()
	{
		$ret = array(
			'title' => null,
			'pageid' => array(
				ParamValidator::PARAM_TYPE => 'integer',
			),
			'anontoken' => array(
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => false,
			),
			'rating' => array(
				ParamValidator::PARAM_TYPE => 'integer',
				ParamValidator::PARAM_REQUIRED => true,
			),
			'comment' => array(
				ParamValidator::PARAM_TYPE => 'string',
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
			'progress' => 'Progress',
			//'comment' => 'the free-form textual feedback',
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
			array('missingparam', 'anontoken'),
			array('code' => 'invalidtoken', 'info' => 'The anontoken is not 32 characters'),
			array('code' => 'invalidpage', 'info' => 'ArticleFeedback is not enabled on this page'),
			array('code' => 'invalidpageid', 'info' => 'Page ID is missing or invalid'),
			array('code' => 'missinguser', 'info' => 'User info is missing'),
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

	/**
	 * Generate a new, unique id.
	 *
	 * Data can be sharded over multiple servers, rendering database engine's
	 * auto-increment useless to generate a unique id.
	 *
	 * @return string
	 */
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

class ApiLoopProgressLoad extends ApiBase
{

	public function __construct($main, $action)
	{
		parent::__construct($main, $action);
	}

	public function execute()
	{
		//$result->addValue($this->getModuleName(), 'structure', $structureitems);
	}

	public function getAllowedParams()
	{
		return array();
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
			array('missingparam', 'anontoken'),
			array('code' => 'invalidtoken', 'info' => 'The anontoken is not 32 characters'),
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

