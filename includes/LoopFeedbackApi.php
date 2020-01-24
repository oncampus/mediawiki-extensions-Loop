<?php 
/**
  * @description 
  * @ingroup Extensions
  * @author Marc Vorreiter @vorreiter <marc.vorreiter@th-luebeck.de>, Dennis Krohn @krohnden <dennis.krohn@th-luebeck.de>
  */
  
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file cannot be run standalone.\n" );
}

class ApiLoopFeedbackSave extends ApiBase {
	public function __construct( $main, $action ) {
		parent::__construct( $main, $action );
	}

	public function execute() {
		wfProfileIn( __METHOD__ );

		$result   = $this->getResult();
		
		$user = $this->getUser();
		if ( $user->isBlocked() ) {
			$this->dieUsage(
				$this->msg( 'loopfeedback-error-blocked' )->escaped(),
				'userblocked'
			);
		}

		if (!$user->isAllowed( 'loopfeedback_view' ) ) {
			$this->dieUsage(
				$this->msg( 'loopfeedback-error-blocked' )->escaped(),
				'userblocked'
			);
		}		
		
		
		$params = $this->extractRequestParams();

		// get page object
		$pageObj = $this->getTitleOrPageId( $params, 'fromdb' );
		if ( !$pageObj->exists() ) {
			$this->dieUsage(
				$this->msg( 'loopfeedback-invalid-page-id' )->escaped(),
				'notanarticle'
			);
		}		
			
		$feedback['lf_id'] = $this->generateId();
		$feedback['lf_page'] = $pageObj->getId();
		$feedback['lf_user'] = $user->getId();
		$feedback['lf_user_text'] = $user->getName();
		$feedback['lf_rating'] = $params['rating'];
		$feedback['lf_comment'] = trim ($params['comment']);
		$feedback['lf_timestamp'] = wfTimestampNow();
		$feedback['lf_archive_timestamp'] = '00000000000000';
		
		$dbw =& wfGetDB( DB_MASTER );
		$dbw->insert(
			'loop_feedback',
			$feedback,
			__METHOD__
		);

		$result->addValue( $this->getModuleName(), 'lf_id', $feedback['lf_id'] );

		
		wfProfileOut( __METHOD__ );
	}

	public function getAllowedParams() {
		$ret = array(
			'title' => null,
			'pageid' => array(
				ApiBase::PARAM_TYPE     => 'integer',
			),
			'anontoken' => array(
				ApiBase::PARAM_TYPE     => 'string',
				ApiBase::PARAM_REQUIRED => false,
			),
			'rating' => array(
				ApiBase::PARAM_TYPE     => 'integer',
				ApiBase::PARAM_REQUIRED => true,
			),
			'comment' => array(
				ApiBase::PARAM_TYPE     => 'string',
				ApiBase::PARAM_REQUIRED => false,
			)
		);

		return $ret;
	}


	public function getParamDescription() {
		$p = $this->getModulePrefix();
		return array(
			'title'      => "Title of the page to submit feedback for. Cannot be used together with {$p}pageid",
			'pageid'     => "ID of the page to submit feedback for. Cannot be used together with {$p}title",
			'anontoken'  => 'Token for anonymous users',
			'rating'     => 'Rating',
			'comment'    => 'the free-form textual feedback',
		);
	}


	public function mustBePosted() { return false; }


	public function isWriteMode() { return true; }


	public function getPossibleErrors() {
		return array_merge( parent::getPossibleErrors(), array(
			array( 'missingparam', 'anontoken' ),
			array( 'code' => 'invalidtoken', 'info' => 'The anontoken is not 32 characters' ),
			array( 'code' => 'invalidpage', 'info' => 'ArticleFeedback is not enabled on this page' ),
			array( 'code' => 'invalidpageid', 'info' => 'Page ID is missing or invalid' ),
			array( 'code' => 'missinguser', 'info' => 'User info is missing' ),
		) );
	}

	public function getDescription() {
		return array(
			'Save loop feedback'
		);
	}

	protected function getExamples() {
		return array(
			'api.php?action=loopfeedback-save'
		);
	}

	public function getVersion() {
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
	protected function generateId() {
		/*
		 * This will return a 128-bit string in base-16, resulting
		 * in a 32-character (at max) string of hexadecimal characters.
		 * Pad the string to full 32-char length if the value is lower.
		 */
		$id = UIDGenerator::newTimestampedUID128( 16 );
		return str_pad( $id, 32, 0, STR_PAD_LEFT );
	}	
}

class ApiLoopFeedbackStructure extends ApiBase {

	public function __construct( $main, $action ) {
		parent::__construct( $main, $action );
	}

	public function execute() {
		wfProfileIn( __METHOD__ );

		$result   = $this->getResult();
		

		$user = $this->getUser();
		if ( $user->isBlocked() ) {
			$this->dieUsage(
				$this->msg( 'loopfeedback-error-blocked' )->escaped(),
				'userblocked'
			);
		}
		
		if (!$user->isAllowed( 'loopfeedback_view_results' ) ) {
			$this->dieUsage(
				$this->msg( 'loopfeedback-error-blocked' )->escaped(),
				'userblocked'
			);
		}			

		$dbr = wfGetDB( DB_REPLICA );
		$res = $dbr->select(
		'loopstructure',
		array(
			'Id',
			'IndexArticleId',
			'TocLevel',
			'TocNumber',
			'TocText',
			'Sequence',
			'ArticleId',
			'PreviousArticleId',
			'NextArticleId',
			'ParentArticleId',
			'IndexOrder'
			),
			array(
			//'TocLevel' => 0
			),
			__METHOD__,
			array(
			'ORDER BY' => 'Sequence ASC'
			)
			);		
		$structureitems = array();
		foreach ( $res as $row ) {
			if ($row->TocLevel < 2) {
				$tn = ($row->TocNumber == '' ) ? 0 : $row->TocNumber;
				$tl = ($row->TocLevel == '' ) ? 0 : $row->TocLevel;

				#$result->addValue( $this->getModuleName(), 'structureitem_'.$row->Sequence , array ( 'tocnumber'=>$tn,'toctext'=>$row->TocText,'article'=>$row->ArticleId) );
			
				$structureitems[] = array ( 'toclevel'=>$tl,'tocnumber'=>$tn,'toctext'=>$row->TocText,'article'=>$row->ArticleId);
			}	
		}
		
		$result->addValue( $this->getModuleName(), 'structure', $structureitems);
		
		
		// $result->addValue( $this->getModuleName(), 'structure', $resultData);
		

		
		wfProfileOut( __METHOD__ );
	}

	public function getAllowedParams() {
		return array();
	}


	public function getParamDescription() {
		return array();
	}


	public function mustBePosted() { return false; }


	public function isWriteMode() { return true; }


	public function getPossibleErrors() {
		return array_merge( parent::getPossibleErrors(), array(
			array( 'missingparam', 'anontoken' ),
			array( 'code' => 'invalidtoken', 'info' => 'The anontoken is not 32 characters' ),
			array( 'code' => 'invalidpage', 'info' => 'ArticleFeedback is not enabled on this page' ),
			array( 'code' => 'invalidpageid', 'info' => 'Page ID is missing or invalid' ),
			array( 'code' => 'missinguser', 'info' => 'User info is missing' ),
		) );
	}

	public function getDescription() {
		return array(
			'Get LOOP Structure'
		);
	}

	protected function getExamples() {
		return array(
			'api.php?action=loop-get-structure'
		);
	}

	public function getVersion() {
		return __CLASS__ . ': version 1.0';
	}
}

class ApiLoopFeedbackPageDetails extends ApiBase {

	public function __construct( $main, $action ) {
		parent::__construct( $main, $action );
	}

	public function execute() {
		wfProfileIn( __METHOD__ );

        // Tell squids to cache
        $this->getMain()->setCacheMode( 'public' );
        // Set the squid & private cache time in seconds
        $this->getMain()->setCacheMaxAge( 0 );		
		
		$result   = $this->getResult();
		

		$user = $this->getUser();
		if ( $user->isBlocked() ) {
			$this->dieUsage(
				$this->msg( 'loopfeedback-error-blocked' )->escaped(),
				'userblocked'
			);
		}

		if (!$user->isAllowed( 'loopfeedback_view_results' ) ) {
			$this->dieUsage(
				$this->msg( 'loopfeedback-error-blocked' )->escaped(),
				'userblocked'
			);
		}		
		
		$params = $this->extractRequestParams();

		if (isset($params['comments']) ) {
			if ($params['comments'] == 1) {
				$comments = true;	
			} else {
				$comments = false;	
			}
		} else {
			$comments=false;
		}
		
		if (isset($params['timestamp']) ) {
			$timestamp = $params['timestamp'];	
		} else {
			$timestamp='00000000000000';
		}
		
		// get page object
		$pageObj = $this->getTitleOrPageId( $params, 'fromdb' );
		if ( !$pageObj->exists() ) {
			$this->dieUsage(
				$this->msg( 'loopfeedback-invalid-page-id' )->escaped(),
				'notanarticle'
			);
		}		
				
		$lfb = new LoopFeedback;
		$feedback_detail = $lfb->getDetails($pageObj->getId(),$comments);	
		
		$result->addValue( null, $this->getModuleName(), array( 'pageDetails'=>array(
			'average'=> $feedback_detail['average'],
			'average_stars'=> $feedback_detail['average_stars'],
			'count_all'=> $feedback_detail['count']['all'],
			'count_comments'=> $feedback_detail['count']['comments'],
			'count_1'=> $feedback_detail['count'][1],
			'count_2'=> $feedback_detail['count'][2],
			'count_3'=> $feedback_detail['count'][3],
			'count_4'=> $feedback_detail['count'][4],
			'count_5'=> $feedback_detail['count'][5]
		) ) );
		
		if ($comments) {
			$result->addValue( null, $this->getModuleName(), array( 'comments'=> $feedback_detail['comments']) );
		}
		
		wfProfileOut( __METHOD__ );
	}

	public function getAllowedParams() {
		$ret = array(
			'title' => null,
			'pageid' => array(
				ApiBase::PARAM_TYPE     => 'integer',
			),
			'comments' => array(
				ApiBase::PARAM_TYPE     => 'integer',
				ApiBase::PARAM_REQUIRED => false,
			),
			'timestamp' => array(
				ApiBase::PARAM_TYPE     => 'string',
				ApiBase::PARAM_REQUIRED => false,
			),			
		);

		return $ret;
	}


	public function getParamDescription() {
		$p = $this->getModulePrefix();
		return array(
			'title'      => "Title of the page to submit feedback for. Cannot be used together with {$p}pageid",
			'pageid'     => "ID of the page to submit feedback for. Cannot be used together with {$p}title",
			'comments'	=> "0 or 1 return comments",
			'timestamp'	=> "timestamp in MW format"
		);
	}


	public function mustBePosted() { return false; }


	public function isWriteMode() { return true; }


	public function getPossibleErrors() {
		return array_merge( parent::getPossibleErrors(), array(
			array( 'missingparam', 'anontoken' ),
			array( 'code' => 'invalidtoken', 'info' => 'The anontoken is not 32 characters' ),
			array( 'code' => 'invalidpage', 'info' => 'ArticleFeedback is not enabled on this page' ),
			array( 'code' => 'invalidpageid', 'info' => 'Page ID is missing or invalid' ),
			array( 'code' => 'missinguser', 'info' => 'User info is missing' ),
		) );
	}

	public function getDescription() {
		return array(
			'Save loop feedback'
		);
	}

	protected function getExamples() {
		return array(
			'api.php?action=loopfeedback-page-details'
		);
	}

	public function getVersion() {
		return __CLASS__ . ': version 1.0';
    }
    
}

class ApiLoopFeedbackOverview extends ApiBase {

    public function __construct( $main, $action ) {
		parent::__construct( $main, $action );
	}

	public function execute() {
		wfProfileIn( __METHOD__ );

		$result   = $this->getResult();
		
		$user = $this->getUser();
		if ( $user->isBlocked() ) {
			$this->dieUsage(
				$this->msg( 'loopfeedback-error-blocked' )->escaped(),
				'userblocked'
			);
		}
		
		if (!$user->isAllowed( 'loopfeedback_view_results' ) ) {
			$this->dieUsage(
				$this->msg( 'loopfeedback-error-blocked' )->escaped(),
				'userblocked'
			);
		}			

		$dbr = wfGetDB( DB_REPLICA );
		$res = $dbr->select(
				'loop_feedback',
				array(
					'quantity' => 'COUNT(lf_id)',
					'rating' => 'lf_rating'
						
				),
				array( 'lf_archive_timestamp' => '00000000000000' ),
				__METHOD__,
				array(
						'GROUP BY' => 'rating',
						'ORDER BY' => 'rating'
				),
				array()
				);
		$fr = array();
		foreach ($res as $row) {
			$fr[$row->rating] = $row->quantity;
		}
		
		$result_array=array();
		for ($i=1;$i<6;$i++) {
			if ($fr[$i]) {
				$result_array[$i] = $fr[$i];
			} else {
				$result_array[$i] = 0;
			}
		}
		
		
		$result->addValue( $this->getModuleName(), 'ratings', $result_array);
		
		wfProfileOut( __METHOD__ );
	}

	public function getAllowedParams() {
		return array();
	}


	public function getParamDescription() {
		return array();
	}


	public function mustBePosted() { return false; }


	public function isWriteMode() { return true; }


	public function getPossibleErrors() {
		return array_merge( parent::getPossibleErrors(), array(
			array( 'missingparam', 'anontoken' ),
			array( 'code' => 'invalidtoken', 'info' => 'The anontoken is not 32 characters' ),
			array( 'code' => 'invalidpage', 'info' => 'ArticleFeedback is not enabled on this page' ),
			array( 'code' => 'invalidpageid', 'info' => 'Page ID is missing or invalid' ),
			array( 'code' => 'missinguser', 'info' => 'User info is missing' ),
		) );
	}

	public function getDescription() {
		return array(
			'Get LOOP feedback overview'
		);
	}

	protected function getExamples() {
		return array(
			'api.php?action=loop-get-overview'
		);
	}

	public function getVersion() {
		return __CLASS__ . ': version 1.0';
	}
}