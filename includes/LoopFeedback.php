<?php
/**
  * @description
  * @ingroup Extensions
  * @author Marc Vorreiter @vorreiter <marc.vorreiter@th-luebeck.de>, Dennis Krohn @krohnden <dennis.krohn@th-luebeck.de>
  */

if ( !defined( 'MEDIAWIKI' ) ) die ( "This file cannot be run standalone.\n" );

use MediaWiki\MediaWikiServices;
use MediaWiki\Session\CsrfTokenSet;
use Wikimedia\Rdbms\SelectQueryBuilder;

class LoopFeedback {

	public static function getShowFeedback() {

		global $wgOut, $wgLoopFeedbackLevel, $wgLoopFeedbackMode;

        if ( $wgLoopFeedbackLevel == 'none' ) {
			return false;
		}

		$mws = MediaWikiServices::getInstance();
		$userGroupManager = $mws->getUserGroupManager();
		$permissionManager = $mws->getPermissionManager();

		$title = $wgOut->getTitle();
		$user = $wgOut->getUser();
		if ( !$permissionManager->userHasRight( $user, 'loopfeedback-view' ) || in_array( "shared", $userGroupManager->getUserGroups($user) ) || in_array( "shared_basic", $userGroupManager->getUserGroups($user) ) ) {
			return false;
		}

		$show_feedback = false;
		$articleId = $title->getArticleID();

		if ( $title->getNamespace() == NS_MAIN ) {
            // überprüfen, ob ein Feeback auf der Seite angezeigt werden soll

            $loopStructure = new LoopStructure();
            $loopStructureItems = $loopStructure->getStructureItems();


            if ( ( $wgLoopFeedbackLevel == 'module' ) && ( $wgLoopFeedbackMode == 'always' ) ) {
                foreach ( $loopStructureItems as $lsi ) {
                    if ( $lsi->article == $articleId ) {
                        $show_feedback = true;
                    }
                }
            } elseif ( ( $wgLoopFeedbackLevel == 'module' ) && ( $wgLoopFeedbackMode == 'last_sublevel' ) ) {
                $in_last = false;
                $found = false;
               foreach ( $loopStructureItems as $lsi ) {
                    if ( $lsi->tocLevel == 1 ) {
                        if ( $found == false ) {
                            $in_last = true;
                        } else {
                            $in_last = false;
                        }
                    }
                    if ( $lsi->article == $articleId ) {
                        $found = true;
                    }
                }
                if ( ( $in_last == true) && ( $found == true) ) {
                    $show_feedback = true;
                }
            } elseif ( ( $wgLoopFeedbackLevel == 'module' ) && ( $wgLoopFeedbackMode == 'second_half' ) ) {
                $count = 0;
                $found_pos = 0;
               foreach ( $loopStructureItems as $lsi ) {
                    $count++;
                    if ( $lsi->article == $articleId ) {
                        $found_pos = $count;
                    }
                }
                if ( $found_pos >= ( $count / 2 ) ) {
                    $show_feedback = true;
                }
            } elseif ( ( $wgLoopFeedbackLevel == 'chapter' ) && ( $wgLoopFeedbackMode == 'always' ) ) {
               foreach ( $loopStructureItems as $lsi ) {
                    if ( $lsi->article == $articleId ) {
                        $show_feedback = true;
                    }
				}
				if ( $loopStructure->getMainpage() == $articleId ) {
					$show_feedback = false;
				}
            } elseif ( ( $wgLoopFeedbackLevel == 'chapter' ) && ( $wgLoopFeedbackMode == 'last_sublevel' ) ) {
                $in_last = false;
                $found = false;
                $skip = false;
               foreach ( $loopStructureItems as $lsi ) {
                    if ( $lsi->tocLevel == 1) {
                        if ( $found == true) {
                            $skip = true;
                        } else {
                            $in_last = false;
                        }
                    }
                    if ( ( $lsi->tocLevel == 2) && ( $skip == false) ) {
                        if ( $found == false) {
                            $in_last= true;
                        } else {
                            $in_last= false;
                        }
                    }
                    if ( $lsi->article == $articleId) {
                        $found = true;
                    }
                }
                if ( ( $in_last == true) && ( $found == true) ) {
                    $show_feedback = true;
                }
            } elseif ( ( $wgLoopFeedbackLevel == 'chapter' ) && ( $wgLoopFeedbackMode == 'second_half' ) ) {
                $count = 0;
                $found_pos = 0;
                $skip = false;
               foreach ( $loopStructureItems as $lsi ) {
                    if ( $lsi->tocLevel == 1) {
                        if ( $found_pos > 0) {
                            $skip = true;
                        } else {
                            $count=0;
                        }
                    }
                    if ( $skip == false) {
                        $count++;
                    }
                    if ( $lsi->article == $articleId) {
                        $found_pos = $count;
                    }
                }

                if ( $found_pos >= ( $count / 2 ) ) {
                    $show_feedback = true;
                }
			}
		}

		return $show_feedback;
	}

	public static function renderFeedbackBox() {

		global $wgOut;
		$return = '';
		if ( in_array( "loop.feedback.js", $wgOut->getModules() ) ) {
			$return .= '<form id="lf_form">';
				$return .= '<p id="lf_feedback_for" class="mb-1">' . wfMessage( 'loopfeedback-feedback-for-module') . '</p>';
					$return .= '<div class="lf-rating-wrapper">';
						$return .= '<label for="loopfeedback-1" title="'.wfMessage( 'loopfeedback-rating-value1' )->text().'" ><span class="ic ic-star cursor-pointer loopfeedback-star"></span></label>';
						$return .= '<input class="d-none" id="loopfeedback-1" name="lf_rating" type="radio" value="1" required/>';
						$return .= '<label for="loopfeedback-2"><span class="ic ic-star loopfeedback-star cursor-pointer"></span></label>';
						$return .= '<input class="d-none" id="loopfeedback-2" name="lf_rating" type="radio" value="2"/>';
						$return .= '<label for="loopfeedback-3"><span class="ic ic-star loopfeedback-star cursor-pointer"></span></label>';
						$return .= '<input class="d-none" id="loopfeedback-3" name="lf_rating" type="radio" value="3"/>';
						$return .= '<label for="loopfeedback-4"><span class="ic ic-star loopfeedback-star cursor-pointer"></span></label>';
						$return .= '<input class="d-none" id="loopfeedback-4" name="lf_rating" type="radio" value="4"/>';
						$return .= '<label for="loopfeedback-5" title="'.wfMessage( 'loopfeedback-rating-value5' )->text().'" ><span class="ic ic-star cursor-pointer loopfeedback-star"></span></label>';
						$return .= '<input class="d-none" id="loopfeedback-5" name="lf_rating" type="radio" value="5"/>';
					$return .= '</div>';
				$return .= '<textarea id="lf_comment" class="form-control" placeholder="'. wfMessage( 'loopfeedback-comment-placeholder' )->text() .'"></textarea>';
				$return .= '<input type="button" disabled rows="3" class="btn btn-sm mw-ui-button mw-ui-primary mw-ui-progressive mt-2 d-block cursor-pointer text-right" value="'. wfMessage( 'loopfeedback-submit-button-text' )->text() .'" id="lf_send"/>';
			$return .= '</form>';
		} else {
			$return .= wfMessage( 'loopfeedback-already-done' )->text();
		}

        return $return;
    }

	public static function onBeforePageDisplay( OutputPage $out, Skin $skin ) {
		if ( self::getShowFeedback() ) {
			global $wgLoopFeedbackLevel, $wgLoopFeedbackMode;
			$permissionManager = MediaWikiServices::getInstance()->getPermissionManager();

			$articleId = $out->getTitle()->getArticleID();
			$user = $out->getUser();
			$return = '';

			// ermitteln welches Feedback auf der Seite angezeigt werden soll

			$tempItem = LoopStructureItem::newFromIds( $articleId );
			$akt_tl = $tempItem->tocLevel;

			$lf_articleid = 0;

			if ( $tempItem != null ) {
				$ancestors = array();
				$tl = $tempItem->tocLevel;
				$ancestors[$tl] = $tempItem->article;
				while ( $tempItem->parentArticle ) {
					$tempItem = LoopStructureItem::newFromIds( $tempItem->parentArticle );
					$tl = $tempItem->tocLevel;
					$ancestors[$tl] = $tempItem->article;
				}

				switch ( $wgLoopFeedbackLevel ) {
					case 'module':
						$lf_articleid = $ancestors[0];
						$lf_toclevel = 0;
						break;
					case 'chapter':
						if ( $akt_tl >= 1 ) {
							$lf_articleid = $ancestors[1];
							$lf_toclevel = 1;
						}
						break;
				}
			}
			if ( $lf_articleid != 0 ) {

				# ermitteln, ob bereits ein Feedback abgegeben wurde
				$dbProvider = MediaWikiServices::getInstance()->getDBLoadBalancer();
				$dbr = $dbProvider->getConnection(DB_REPLICA);
				$lf = $dbr->newSelectQueryBuilder()
					->select([ 'lf_id', 'lf_page'])
					->from('loop_feedback')
					->where([
							'lf_page = "' . $lf_articleid . '"',
							'lf_user = "' . $user->getId() . '"',
							'lf_archive_timestamp = "00000000000000"',
						]
					)
					->caller(__METHOD__)->fetchRow();

				if ( !isset( $lf->lf_id ) ) {
					$lf_title = Title::newFromID( $lf_articleid );
					$specialtitle = Title::newFromText( 'LoopFeedback', NS_SPECIAL );
					$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
					$linkRenderer->setForceArticlePath(true); #required for readable links
					$resultlink = $linkRenderer->makeLink(
						$specialtitle,
						new HtmlArmor( wfMessage( 'loopfeedback-specialpage-feedback-resultlink' )->text() ),
						array(
							'style' => 'text-decoration:underline'
						),
						array(
							'view' => 'page',
							'page' => $lf_articleid
						)
					);

					if ( $permissionManager->userHasRight( $user, 'loopfeedback-view-results' ) ) {
						$view_results = 1;
					} else {
						$view_results = 0;
					}

					if ( $permissionManager->userHasRight( $user, 'loopfeedback-view-comments' ) ) {
						$view_comments = 1;
					} else {
						$view_comments = 0;
					}

					$lf_arcticle = array(
						'id' => $lf_articleid,
						'title' => $lf_title->getFullText(),
						'toclevel' => $lf_toclevel,
						'resultlink' => $resultlink,
						'view_results' => $view_results,
						'view_comments' => $view_comments
					);

					$out->addModules( "loop.feedback.js" );
					$out->addJsConfigVars( 'lfArticle', $lf_arcticle );
				}
			}
		}
		return true;
	}

    function getPeriods( $page = false ) {

		$periods = array();
		$dbProvider = MediaWikiServices::getInstance()->getDBLoadBalancer();
		$dbr = $dbProvider->getConnection(DB_REPLICA);

		if ( !$page ) {
			$lfs = $dbr->newSelectQueryBuilder()
				->select('lf_archive_timestamp')
				->from('loop_feedback')
				->where('lf_archive_timestamp <> 00000000000000')
				->orderBy('lf_archive_timestamp', SelectQueryBuilder::SORT_DESC)
				->caller(__METHOD__)
				->fetchResultSet();

		} else {
			$lfs = $dbr->newSelectQueryBuilder()
				->select('lf_archive_timestamp')
				->from('loop_feedback')
				->where(['lf_archive_timestamp <> 00000000000000',
						'lf_page' => $page])
				->orderBy('lf_archive_timestamp', SelectQueryBuilder::SORT_DESC)
				->caller(__METHOD__)->fetchResultSet();
		}
		$timestamps = array();

		foreach ($lfs as $row){
			$timestamps[] = $row[ 'lf_archive_timestamp' ];
		}

		if ( count( $timestamps) > 0 ) {

			$periods[] = array(
						'begin' => $timestamps[0],
						'end' => '00000000000000',
						'begin_text' => $this->formatTimestamp( $timestamps[0] ),
						'end_text' => wfMessage( 'loopfeedback-specialpage-period-now' )->text()
						);

			$lasttimestamp = '';
			foreach ( $timestamps as $timestamp) {
				if ( $lasttimestamp == '' ) {
					$lasttimestamp = $timestamp;
				} else {

					$periods[] = array(
						'begin' => $timestamp,
						'end' => $lasttimestamp,
						'begin_text' => $this->formatTimestamp( $timestamp ),
						'end_text' => $this->formatTimestamp( $lasttimestamp )
						);

					$lasttimestamp = $timestamp;
				}
			}

			$periods[] = array(
				'begin' => '00000000000000',
				'end' => $timestamp,
				'begin_text' => wfMessage( 'loopfeedback-specialpage-period-begin' )->text(),
				'end_text' => $this->formatTimestamp( $timestamp)
				);

		} else{

			$periods[] = array(
				'begin' => '00000000000000',
				'end' => '00000000000000',
				'begin_text' => wfMessage( 'loopfeedback-specialpage-period-begin' )->text(),
				'end_text' => wfMessage( 'loopfeedback-specialpage-period-now' )->text()
				);

		}

		return $periods;
	}

	function getDetails( $pageid, $comments = false, $timestamp='00000000000000', $dbDomain = false ): array {
		$dbProvider = MediaWikiServices::getInstance()->getDBLoadBalancerFactory()->getMainLB( $dbDomain );
		$dbr = $dbProvider->getConnection(DB_REPLICA);
		$lfs = $dbr->newSelectQueryBuilder()
			->select( [
				'lf_id', 'lf_user', 'lf_user_text', 'lf_rating', 'lf_comment', 'lf_timestamp',
				])
			->from('loop_feedback')
			->where(['lf_page' => $pageid])
			->orderBy('lf_timestamp', SelectQueryBuilder::SORT_DESC)
			->caller(__METHOD__)->fetchResultSet();
		// second where clause 'lf_archive_timestamp' => $timestamp, ->where('lf_page = ' . $pageid)
			var_dump($lfs);

		$return = array(
			'pageid' => $pageid,
			'count' => array (
				'all' => 0,
				'comments' => 0,
				1 => 0,
				2 => 0,
				3 => 0,
				4 => 0,
				5 => 0,
				),
			'sum' => 0,
			'average' => 0,
			'average_stars' => 0,
			'comments' => array()
			);

		foreach($lfs as $row){
			$rating = $row[ 'lf_rating' ];
			$return[ 'count' ][ 'all' ]++;
			$return[ 'count' ][$rating]++;
			$return[ 'sum' ] = $return[ 'sum' ]+$rating;
			if ( $row[ 'lf_comment' ] != '' ) {
				$return['count']['comments']++;
				if ($comments) {
					$return['comments'][] = array(
						'timestamp' => $row['lf_timestamp'],
						'timestamp_text' => $this->formatTimestamp($row['lf_timestamp']),
						'comment' => $row['lf_comment']
					);
				}
			}
		}
		if ( $return[ 'count' ][ 'all' ] > 0) {
			$return[ 'average' ] = round( ( $return[ 'sum' ] / $return[ 'count' ][ 'all' ]),1);
			$return[ 'average_stars' ] = round( $return[ 'average' ]);
		}
		return $return;
	}

	function formatTimestamp( $ts ) {
		$ts_unix = wfTimestamp( TS_UNIX, $ts );
		$ts_display = date ( 'd.m.Y', $ts_unix);
		return $ts_display;
    }

}


class SpecialLoopFeedback extends SpecialPage {

	function __construct() {
		parent::__construct( 'LoopFeedback' );
	}

	function execute( $par ) {
		global $wgLoopFeedbackLevel;

		$out = $this->getOutput();
		$request = $this->getRequest();
		$user = $this->getUser();
		$csrfTokenSet = new CsrfTokenSet($request);
		Loop::handleLoopRequest( $out, $request, $user ); #handle editmode
		// $userOptionsLookup = MediaWikiServices::getInstance()->getUserOptionsLookup();
		// $editMode = $userOptionsLookup->getOption( $user, 'LoopEditMode', false, true );

		$permissionManager = MediaWikiServices::getInstance()->getPermissionManager();

		$out->setPageTitle( $this->msg('loopfeedback') );

		$action = $request->getText( 'action' );
		$view 	= $request->getText( 'view' );
		$page 	= $request->getText( 'page' );
		$period_begin = $request->getText( 'period_begin' );
		$period_end = $request->getText( 'period_end' );
		$token  = $request->getText( 'token' );

		if ( $wgLoopFeedbackLevel == 'none' ) {
			$view = 'none';
		} else {
			if ( $action == 'reset_page' )  {
				if ( $page != '' ) {
					if ( $csrfTokenSet->matchToken( $token ,'reset-feedback' ) ) {
						$this->resetPage( $page );
						$view = 'all';
						$period_begin = '00000000000000';
						$period_end = '00000000000000';
					} else {
						$view = 'confirm_reset_page';
					}
				}
			}
			if ( $action == 'reset_all' )  {
				if ( $csrfTokenSet->matchToken( $token ,'reset-feedback' ) ) {
					$this->resetAll();
					$view = 'all';
					$period_begin = '00000000000000';
					$period_end = '00000000000000';
				} else {
					$view = 'confirm_reset_all';
				}
			}

			if ( $view == '' ) {
				if ( $wgLoopFeedbackLevel == 'none' ) {
					$view = 'none';
				} elseif ( $wgLoopFeedbackLevel == 'module' ) {
					if ( $page == '' ) {
						$loopStructure = new LoopStructure();
						$loopStructure->loadStructureItems();
						$page = $loopStructure->getMainpage();
					}
					$view = 'page';
				} elseif ( $wgLoopFeedbackLevel == 'chapter' ) {
					$view = 'all';
				}
			}
			if ( $period_begin == '' ) {
				$period_begin = '00000000000000';
			}
			if ( $period_end == '' ) {
				$period_end = '00000000000000';
			}
		}

		$return = '';
		if ( $view == 'none' ) {
			$return .= $this->printInactive();
		}
		if ( $view == 'confirm_reset_page' ) {
			if ( !$permissionManager->userHasRight( $user, 'loopfeedback-reset' ) ) {
				throw new PermissionsError( 'loopfeedback-reset' );
			}
			$return .= $this->printConfirmResetPage( $page, $period_begin, $period_end );
		}
		if ( $view == 'confirm_reset_all' ) {
			if ( !$permissionManager->userHasRight( $user, 'loopfeedback-reset' ) ) {
				throw new PermissionsError( 'loopfeedback-reset' );
			}
			$return .= $this->printConfirmResetAll( $page, $period_begin, $period_end );
		}
		if ( $view == 'overview' ) {
			if ( !$permissionManager->userHasRight( $user, 'loopfeedback-view-results' ) ) {
				throw new PermissionsError( 'loopfeedback-view-result' );
			}
			$return .= $this->printOverview();
		}
		if ( $view == 'all' ) {
			if ( !$permissionManager->userHasRight( $user, 'loopfeedback-view-results' ) ) {
				throw new PermissionsError( 'loopfeedback-view-result' );
			}
			$return .= $this->printAll();
		}
		if ( $view == 'page' ) {
			if ( !$permissionManager->userHasRight( $user, 'loopfeedback-view-results' ) ) {
				throw new PermissionsError( 'loopfeedback-view-result' );
			}
			$return .= $this->printPage( $page, $period_begin, $period_end);
		}

		$this->setHeaders();
		$out->addHTML( $return );

	}


	private function printInactive() {
		$return = '';

		$return .= '<h1>'.wfMessage( 'loopfeedback-specialpage-header' )->text().'</h1>';
		$return .= '<p>'.wfMessage( 'loopfeedback-specialpage-inactive' )->text().'</p>';

		return $return;
	}


	private function printOverview() {
		$return = '';

		$return .= '<h1>'.wfMessage( 'loopfeedback-specialpage-header' )->text().'</h1>';
		$return .= '<h2>'.wfMessage( 'loopfeedback-specialpage-header-overview-archive' )->text().'</h2>';

		$specialtitle = Title::newFromText( 'LoopFeedback', NS_SPECIAL );

		$loopFeedback = new LoopFeedback;
		$periods = $loopFeedback->getPeriods();
		$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
		$linkRenderer->setForceArticlePath(true); #required for readable links

		foreach ( $periods as $period) {
            $return .= $linkRenderer->makeLink(
                $specialtitle,
                new HtmlArmor( $period[ 'begin_text' ].' - '.$period[ 'end_text' ] ),
				array(),
                array (
					'view' => 'all',
					'period_begin' => $period[ 'begin' ],
					'period_end' => $period[ 'end' ]
				) );
		}

		return $return;
	}

	private function printConfirmResetPage ( $page, $period_begin, $period_end ) {
		$return = '';
		$title = Title::newFromID( $page );
		$return .= wfMessage( 'loopfeedback-specialpage-confirm-reset-page' , $title->getPrefixedText() )->text();
		$return .= '<br/>';
        $specialtitle = Title::newFromText( 'LoopFeedback', NS_SPECIAL );
        $linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
		$linkRenderer->setForceArticlePath(true); #required for readable links

		$csrfTokenSet = new CsrfTokenSet($this->getRequest());
		$reset_token = $csrfTokenSet->getToken( 'reset-feedback' );

		$return .= $linkRenderer->makeLink(
            $specialtitle,
			new HtmlArmor( wfMessage( 'loopfeedback-specialpage-confirm-reset-page-link' )->text() ),
			array(
					'class' => 'loopfeedback-button mw-ui-button mw-ui-primary mw-ui-progressive mt-2 d-block float-right ml-2'
				),
			array (
				'action' => 'reset_page',
				'page' => $page,
				'token' => $reset_token,
				'view' => 'all',
				'period_begin' => $period_begin,
				'period_end' => $period_end
			)
		);

        $return .= $linkRenderer->makeLink(
            $specialtitle,
			new HtmlArmor( wfMessage( 'loopfeedback-specialpage-cancel' )->text() ),
			array(
					'class' => 'loopfeedback-button mw-ui-button mw-ui-primary mw-ui-progressive mt-2 d-block float-right'
				),
			array(
				'view' => 'page',
				'page' => $page,
				'period_begin' => $period_begin,
				'period_end' => $period_end
			)
		);


		return $return;
	}

	private function printConfirmResetAll ( $page, $period_begin, $period_end ) {
		$return = '';

		$title = Title::newFromID( $page );

		$return .= wfMessage( 'loopfeedback-specialpage-confirm-reset-all' )->text();
		$return .= '<br/>';

		$specialtitle = Title::newFromText( 'LoopFeedback', NS_SPECIAL );
		$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
        $linkRenderer->setForceArticlePath(true); #required for readable links
		$return .= ' ';

		$csrfTokenSet = new CsrfTokenSet( $this->getRequest() );
		$reset_token = $csrfTokenSet->getToken( 'reset-feedback' );
        $return .= $linkRenderer->makeLink(
            $specialtitle,
			new HtmlArmor( wfMessage( 'loopfeedback-specialpage-confirm-reset-all-link' )->text() ),
			array(
					'class' => 'loopfeedback-button mw-htmlform-submit mw-ui-button mw-ui-primary mw-ui-progressive mt-2 d-block float-right ml-3'
				),
			array (
				'action' => 'reset_all',
				'token' => $reset_token,
				'view' => 'all',
				'period_begin' => $period_begin,
				'period_end' => $period_end
			)
		);
        $return .= $linkRenderer->makeLink(
            $specialtitle,
			new HtmlArmor( wfMessage( 'loopfeedback-specialpage-cancel' )->text() ),
			array(
					'class' => 'loopfeedback-button mw-htmlform-submit mw-ui-button mw-ui-primary mw-ui-progressive mt-2 d-block float-right ml-3'
				),
			array(
				'view' => 'all',
				'period_begin' => $period_begin,
				'period_end' => $period_end
			)
		);

		return $return;
	}



	private function printPage( $page, $period_begin, $period_end ) {
		global $wgLoopFeedbackLevel;

		$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
		$linkRenderer->setForceArticlePath(true); #required for readable links
		$permissionManager = MediaWikiServices::getInstance()->getPermissionManager();

		if ( $period_begin == '' ) {
			$period_begin = '00000000000000';
		}
		if ( $period_end == '' ) {
			$period_end = '00000000000000';
		}
		$return = '';

		$return .= '<h1>'.wfMessage( 'loopfeedback-specialpage-header-detail' )->text().'</h1>';

		$title = Title::newFromID ( $page );

		$return .= '<div class="col-12"><div class="row">';

		if ( $wgLoopFeedbackLevel == 'chapter' ) {
			$return .= '<h3>'.wfMessage( 'loopfeedback-specialpage-feedback-for-chapter' )->text() . ' "' . $title->getPrefixedText().'"</h3>';
		} else {
			$return .= '<h3>'.wfMessage( 'loopfeedback-specialpage-feedback-for-module' )->text() . ' "' . $title->getPrefixedText().'"</h3>';
		}
		$return .= '</div>';

		$loopFeedback = new LoopFeedback;
		$feedback_periods = $loopFeedback->getPeriods( $page );


		if ( count( $feedback_periods) > 1) {

			$return .= '<div class="row">';
			$return .= '<p>'.wfMessage( 'loopfeedback-specialpage-header-period' )->text().'</p>';
			$specialtitle = Title::newFromText( 'LoopFeedback', NS_SPECIAL );
			foreach ( $feedback_periods as $feedback_period ) {

				if ( ( $feedback_period[ 'end' ] == $period_end ) ) {
					$css_class='font-weight-bold';
				} else {
					$css_class='loopfeedback-period_button';
				}

                $return .= $linkRenderer->makeLink(
                    $specialtitle,
					new HtmlArmor( $feedback_period[ 'begin_text' ].' - '.$feedback_period[ 'end_text' ] ),
					array(
						'class' => $css_class . " ml-2"
					),
					array (
						'view' => 'page',
						'page' => $page,
						'period_begin' => $feedback_period[ 'begin' ],
						'period_end' => $feedback_period[ 'end' ]
					) ).' ';
			}
			$return .= '</div>';
		}


		$return .= '<div class="row">';
		$feedback_detail = $loopFeedback->getDetails( $page, true, $period_end );

		if ( $wgLoopFeedbackLevel == 'chapter' ) {
			$return .= wfMessage( 'loopfeedback-rating-descr-chapter' )->text();
		} else {
			$return .= wfMessage( 'loopfeedback-rating-descr-module' )->text();
		}
		$return .= '</div>';
		$return .= '<div class="row mt-2 mb-3">';
		$return .= '<div class="col-12 col-md-6">';

		$return .= '<div class="row mt-2 mb-0">';

		$return .= '<div class="loopfeedback-bar-stars float-left">';
		$return .= $this->printStars( $feedback_detail[ 'average' ] );
		$return .= '</div>';

		if ( $feedback_detail[ 'count' ][ 'all' ] > 0) {
			$return .= "<p class='float-left'>" . wfMessage( 'loopfeedback-specialpage-feedback-info-average-stars', $feedback_detail[ 'average' ])->text() . '</p><br/>';
			$return .= '</div>';

			$return .= '<div class="row">';
			$return .= "<p class='float-left'>" . wfMessage( 'loopfeedback-specialpage-feedback-info-sum-all', $feedback_detail[ 'count' ][ 'all' ])->text() . '</p>';
			$return .= '</div>';
		} else {
			$return .= wfMessage( 'loopfeedback-specialpage-feedback-info-no-feedback' )->text();
			$return .= '</div>';
		}

		$return .= '<div class="row">';
		if ( $feedback_detail[ 'count' ][ 'comments' ] > 0) {
			$return .= '<p class="pl-0">' . wfMessage( 'loopfeedback-specialpage-feedback-info-count-comments', $feedback_detail[ 'count' ][ 'comments' ])->text().'</p>';
		} else {
			$return .= '<p class="pl-0">' . wfMessage( 'loopfeedback-specialpage-feedback-info-no-comments' )->text().'</p>';
		}

		if ( $permissionManager->userHasRight( $this->getUser(),'loopfeedback-view-comments' ) ) {
			foreach ( $feedback_detail[ 'comments' ] as $comment) {
				$return .= '<p><strong>'.$comment[ 'timestamp_text' ].'</strong><br/>'.$comment[ 'comment' ].'</p>';
			}
		}
		$return .= '</div>';

		$a = $feedback_detail[ 'count' ][ 'all' ];

		$return .= '</div>';
		$return .= '<div class="col-12 col-md-6">';
		for ( $i = 5; $i >= 1; $i--)  {
			if ( $a > 0) {
				$c = $feedback_detail[ 'count' ][$i];
				$f = ( $c / $a ) * 100;
			} else {
				$f = 0;
			}
			$return .= '<div class="row loopfeedback-bar">';
			$return .= '<div class="loopfeedback-bar-stars">'.$this->printStars( $i ).'</div>';
			$return .= '<div class="progress mt-1"><div class="progress-bar" role="progressbar" style="width: '.$f.'%" aria-valuenow="'.$f.'" aria-valuemin="0" aria-valuemax="100"></div></div>';

			$return .= '<div class="loopfeedback-bar-info ml-2">( '.$feedback_detail[ 'count' ][$i].' )</div>';
			$return .= '</div>';
		}



		$return .= '</div>';
		$return .= '</div>';
		$return .= '</div>';

		$return .= '<hr/>';
		$specialtitle = Title::newFromText( 'LoopFeedback', NS_SPECIAL );

		if ( $wgLoopFeedbackLevel == 'chapter' ) {
            $return .= $linkRenderer->makeLink(
                $specialtitle,
				new HtmlArmor( wfMessage( 'loopfeedback-specialpage-all-link' ) ),
				array(
					'class' => 'loopfeedback-button mw-ui-button mw-ui-primary mw-ui-progressive mt-2 d-block float-right ml-2'
				),
				array (
					'view' => 'all',
					'period_begin' => $period_begin,
					'period_end' => $period_end)
			);
			$return .= ' ';
		}

		if ( $period_end == '00000000000000' ) {

            $return .= $linkRenderer->makeLink(
                $specialtitle,
                new HtmlArmor( wfMessage( 'loopfeedback-specialpage-reset-page-link' ) . "*" ),
				array(
					'class' => 'loopfeedback-button mw-ui-button mw-ui-primary mw-ui-progressive mt-2 d-block float-right'
				),
				array (
					'page' => $page,
					'action' => 'reset_page',
					'period_begin' => $period_begin,
					'period_end' => $period_end
				)
			);

			$return .= ' '.wfMessage( 'loopfeedback-specialpage-reset-info' );

		}

		return $return;
	}

	private function printAll () {
		global $wgLoopFeedbackLevel, $wgLoopLegacyPageNumbering;

		$return = '';
		$return .= '<h1>'.wfMessage( 'loopfeedback-specialpage-header' )->text().'</h1>';

		$return .= '<table class="table-striped loopfeedback-special-table">';
		$return .= '<tr><th class="pl-2 pr-1">';

		if ( $wgLoopFeedbackLevel == 'chapter' ) {
			$return .= wfMessage( 'loopfeedback-specialpage-feedback-for-chapter' )->text();
		} else {
			$return .= wfMessage( 'loopfeedback-specialpage-feedback-for-module' )->text();
		}

		$return .= '</th><th colspan="2">';

		if ( $wgLoopFeedbackLevel == 'chapter' ) {
			$return .= wfMessage( 'loopfeedback-rating-descr-chapter' )->text();
		} else {
			$return .= wfMessage( 'loopfeedback-rating-descr-module' )->text();
		}

		$return .= '</th></tr>';

		if ( $wgLoopFeedbackLevel == 'module' ) {
			$condition = 0;
		} elseif ( $wgLoopFeedbackLevel == 'chapter' ) {
			$condition = 1;
		}

        $loopStructure = new LoopStructure();
        $loopStructureItems = $loopStructure->getStructureItems();

		$specialtitle = Title::newFromText( 'LoopFeedback', NS_SPECIAL );
		$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
		$linkRenderer->setForceArticlePath(true); #required for readable links

		$n = 0;
		foreach ( $loopStructureItems as $lsi ) {
			if ( $lsi->tocLevel == $condition ) {

				$n++;
				$return .= '<tr><td class="pl-2 pr-1">';

				$lf_item_text = '';
				if ( $wgLoopLegacyPageNumbering ) {
					$lf_item_text .= $lsi->tocNumber .' ';
				}
				$lf_item_text .= $lsi->tocText;

				$return .= $linkRenderer->makeLink(
						$specialtitle,
						new HtmlArmor( $lf_item_text ),
						array(),
						array (
							'view' => 'page',
							'page' => $lsi->article
						)
					);
				$return .= '</td>';

				$loopFeedback = new LoopFeedback;
				$feedback_detail = $loopFeedback->getDetails( $lsi->article );

				$return .= '<td class="pl-2 pr-1">'. $this->printStars( $feedback_detail[ 'average' ] ).'</td>';

				$return .= '<td class="pl-2 pr-1">';

				if ( $feedback_detail[ 'count' ][ 'all' ] > 0) {
					$return .= wfMessage( 'loopfeedback-specialpage-feedback-info-sum-all', $feedback_detail[ 'count' ][ 'all' ] )->text().'<br/>';
					$return .= wfMessage( 'loopfeedback-specialpage-feedback-info-average-stars', round( $feedback_detail[ 'average' ], 2 ) )->text().'<br/>';
				} else {
					$return .= wfMessage( 'loopfeedback-specialpage-feedback-info-no-feedback' )->text().'<br/>';
				}
				if ( $feedback_detail[ 'count' ][ 'comments' ] > 0) {
					$return .= wfMessage( 'loopfeedback-specialpage-feedback-info-count-comments', $feedback_detail[ 'count' ][ 'comments' ] )->text();
				} else {
					$return .= wfMessage( 'loopfeedback-specialpage-feedback-info-no-comments' )->text();
				}
				$return .= '</td>';

				$return .= '</tr>';

			}
		}

		$return .= '</table>';
		$return .= '<hr/>';
        $return .= $linkRenderer->makeLink(
                $specialtitle,
				new HtmlArmor( wfMessage( 'loopfeedback-specialpage-reset-all-link' ) . "*" ),
				array(
					'class' => 'loopfeedback-button float-right mw-htmlform-submit mw-ui-button mw-ui-primary mw-ui-progressive mt-2 d-block'
				),
				array (
					'action' => 'reset_all'
				)
			);

			$return .= ' '.wfMessage( 'loopfeedback-specialpage-reset-info' );

		return $return;
	}

	private static function printStars( $rating ) {

	$printreturn = '<div class="lf_rating_wrapper">
<span class="ic ic-star ' . ( $rating >= 1 ? 'lf-colour-active' : 'lf-colour-idle' ) . '"></span>
<span class="ic ic-star ' . ( $rating >= 2 ? 'lf-colour-active' : 'lf-colour-idle' ) . '"></span>
<span class="ic ic-star ' . ( $rating >= 3 ? 'lf-colour-active' : 'lf-colour-idle' ) . '"></span>
<span class="ic ic-star ' . ( $rating >= 4 ? 'lf-colour-active' : 'lf-colour-idle' ) . '"></span>
<span class="ic ic-star ' . ( $rating >= 5 ? 'lf-colour-active' : 'lf-colour-idle' ) . '"></span>
</div>';

		return $printreturn;
	}

	function toNearestHalf( $val ) {
		return round( $val * 2 ) / 2;
	}

	function formatTimestamp( $ts ) {
		$ts_unix = wfTimestamp( TS_UNIX, $ts );
		$ts_display = wfTimestamp( TS_DB, $ts_unix );
		return $ts_display;
	}

	function resetPage ( $page ) {
		$dbProvider = MediaWikiServices::getInstance()->getDBLoadBalancer();
		$dbw = $dbProvider->getConnection(DB_PRIMARY);
		//$dbw = wfGetDB( DB_PRIMARY );
		$dbw->update( 'loop_feedback',
		array( 'lf_archive_timestamp' => wfTimestampNow() ),
		array(
			'lf_page' => $page,
			'lf_archive_timestamp' => '00000000000000'
			),
		__METHOD__ );
		return true;
	}

	function resetAll () {
		$dbProvider = MediaWikiServices::getInstance()->getDBLoadBalancer();
		$dbw = $dbProvider->getConnection(DB_PRIMARY);
		//$dbw = wfGetDB( DB_PRIMARY );
		$dbw->update( 'loop_feedback',
		array( 'lf_archive_timestamp' => wfTimestampNow() ),
		array(
			'lf_archive_timestamp' => '00000000000000'
			),
		__METHOD__ );

		return true;
    }

	/**
	 * Specify the specialpages-group loop
	 *
	 * @return string
	 */
	protected function getGroupName() {
		return 'loop';
    }

}
