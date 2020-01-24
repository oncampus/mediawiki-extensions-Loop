<?php 
/**
  * @description 
  * @ingroup Extensions
  * @author Marc Vorreiter @vorreiter <marc.vorreiter@th-luebeck.de>, Dennis Krohn @krohnden <dennis.krohn@th-luebeck.de>
  */
  
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file cannot be run standalone.\n" );
}

use MediaWiki\MediaWikiServices;

class LoopFeedback {

    public static function onBeforePageDisplay( &$out, &$skin ) {

        global $wgLoopFeedbackLevel, $wgLoopFeedbackMode;
        if ( $wgLoopFeedbackLevel == 'none' ) {
			return true;
		}
		
		$title = $out->getTitle();
		$user = $out->getUser();
		
		if ( !$user->isAllowed( 'loopfeedback-view' ) ) {
			return true;
		}
		
		$articleId = $title->getArticleID();
		
		if ( $title->getNamespace() == NS_MAIN ) {
            // überprüfen, ob ein Feeback auf der Seite angezeigt werden soll
                
            $loopStructure = new LoopStructure();
            $loopStructureItems = $loopStructure->getStructureItems();
            
            $show_feedback = false;
                
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
            
                if ( $found_pos >= ( $count/2) ) {
                    $show_feedback = true;
                }			
            }
            
            if ( $show_feedback == true) {
                    
                // ermitteln welches Feeback auf der Seite angezeigt werden soll
                
                $tempItem = LoopStructureItem::newFromIds( $articleId);
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
                
                    $dbr = wfGetDB( DB_REPLICA );
                    $lf = $dbr->selectRow(
                        'loop_feedback',
                        array( 'lf_id' ),
                        array(
                            'lf_page' => $lf_articleid,
                            //'lf_user_text' => $user->getName(),
                            'lf_user' => $user->getId(),
                            'lf_archive_timestamp' => '00000000000000'
                        ),
                        __METHOD__
                    );
                    
                    if (!isset( $lf->lf_id) ) {
                    
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
                        
                        if ( $user->isAllowed( 'loopfeedback-view-results' ) ) {
                            $view_results = 1;
                        } else {
                            $view_results = 0;
                        }
                        
                        if ( $user->isAllowed( 'loopfeedback-view-comments' ) ) {
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

                        $out->addJsConfigVars( 'lfArticle', $lf_arcticle);
                        $out->addModules( 'loop.feedback.js' );
                    }
                }
            }
        }
    }

    function getPeriods( $page = false ) {
		
		$periods = array();
		
		$dbr = wfGetDB( DB_REPLICA );
		
		if ( !$page ) {
			$lfs = $dbr->select(
				'loop_feedback',
				array( 
					"DISTINCT (lf_archive_timestamp)"
				),
				array(
					0 => "lf_archive_timestamp <> '00000000000000'"
				),
				__METHOD__,
				array(
					'ORDER BY' => 'lf_archive_timestamp DESC'
				)
			);
		} else {
			$lfs = $dbr->select(
				'loop_feedback',
				array( 
					"DISTINCT (lf_archive_timestamp)"
				),
				array(
					0 => "lf_archive_timestamp <> '00000000000000'",
					1 => "lf_page = '$page'",
				),
				__METHOD__,
				array(
					'ORDER BY' => 'lf_archive_timestamp DESC'
				)
			);					
		}
		
		$timestamps = array();
		while ( $row = $dbr->fetchRow( $lfs ) ) {
			$timestamps[] = $row[ 'lf_archive_timestamp' ];
		}
		
		if ( count( $timestamps) > 0 ) {
			
			$periods[] = array(
						'begin' => $timestamps[0],
						'end' => '00000000000000',
						'begin_text' => self::formatTimestamp( $timestamps[0] ),
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
						'begin_text' => self::formatTimestamp( $timestamp ),
						'end_text' => self::formatTimestamp( $lasttimestamp )
						);

					$lasttimestamp = $timestamp;
				}
			}
			
			$periods[] = array(
				'begin' => '00000000000000',
				'end' => $timestamp,
				'begin_text' => wfMessage( 'loopfeedback-specialpage-period-begin' )->text(),
				'end_text' => self::formatTimestamp( $timestamp)
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
	
	function getDetails( $pageid, $comments= false, $timestamp='00000000000000' ) {
		$dbr = wfGetDB( DB_REPLICA );
		$lfs = $dbr->select(
			'loop_feedback',
			array( 
				'lf_id',
				'lf_user',
				'lf_user_text',
				'lf_rating',
				'lf_comment',
				'lf_timestamp'
			),
			array(
				'lf_page' => $pageid,
				'lf_archive_timestamp' => $timestamp
			),
			__METHOD__,
			array(
				'ORDER BY' => 'lf_timestamp DESC'
			)			
		);
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
		while ( $row = $dbr->fetchRow( $lfs ) ) {
			$rating = $row[ 'lf_rating' ];
			$return[ 'count' ][ 'all' ]++;
			$return[ 'count' ][$rating]++;
			$return[ 'sum' ] = $return[ 'sum' ]+$rating;
			if ( $row[ 'lf_comment' ] != '' ) {
				$return[ 'count' ][ 'comments' ]++;
				if ( $comments == true) {
					$return[ 'comments' ][] = array (
						'timestamp' => $row[ 'lf_timestamp' ],
						'timestamp_text' => self::formatTimestamp( $row[ 'lf_timestamp' ]),
						'comment' => $row[ 'lf_comment' ]
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
	
	function formatTimestamp( $ts) {
		$ts_unix = wfTimestamp( TS_UNIX, $ts );
		//$ts_display = wfTimestamp( TS_DB, $ts_unix );
		#$ts_display = date ( 'd.m.Y H:i', $ts_unix);
		$ts_display = date ( 'd.m.Y', $ts_unix);
		return $ts_display;
    }	
    
}


class SpecialLoopFeedback extends SpecialPage {

	function __construct() {
		parent::__construct( 'LoopFeedback' );
	}

	function execute( $par ) {
		global $wgParser, $wgParserConf, $wgLoopLegacyPageNumbering, $wgLoopFeedbackLevel;

        
		$out = $this->getOutput();
		$request = $this->getRequest();
		$user = $this->getUser();
		Loop::handleLoopRequest( $out, $request, $user ); #handle editmode

		$editMode = $user->getOption( 'LoopEditMode', false, true );
		$out->setPageTitle( $this->msg('loopfeedback') );
        
		#$wgOut->addModules( 'ext.LoopFeedbackSpecial' );

		$action = $request->getText( 'action' );
		$view 	= $request->getText( 'view' );
		$page 	= $request->getText( 'page' );
		$period_begin = $request->getText( 'period_begin' );
		$period_end = $request->getText( 'period_end' );
		$token  = $request->getText( 'token' );
		
		if ( $action == 'reset_page' )  {
			if ( $page != '' ) {
				if ( $this->getUser()->matchEditToken( $token ,'reset-feedback' ) ) {
					self::resetPage( $page);
					$view = 'all';
					$period_begin = '00000000000000';
					$period_end = '00000000000000';
				} else {
					$view = 'confirm_reset_page';
				}
			}
		}
		if ( $action == 'reset_all' )  {
			if ( $this->getUser()->matchEditToken( $token ,'reset-feedback' ) ) {
				self::resetAll();
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
		
		$return = '';
		if ( $view == 'none' ) {
			$return .= self::printInactive();
		}		
		if ( $view == 'confirm_reset_page' ) {
			if ( !$this->getUser()->isAllowed( 'loopfeedback-reset' ) ) {
				throw new PermissionsError( 'loopfeedback-reset' );
			}
			$return .= self::printConfirmResetPage( $page, $period_begin, $period_end );
		}
		if ( $view == 'confirm_reset_all' ) {
			if ( !$this->getUser()->isAllowed( 'loopfeedback-reset' ) ) {
				throw new PermissionsError( 'loopfeedback-reset' );
			}		
			$return .= self::printConfirmResetAll( $page, $period_begin, $period_end );
		}
		if ( $view == 'overview' ) {
			if ( !$this->getUser()->isAllowed( 'loopfeedback-view-results' ) ) {
				throw new PermissionsError( 'loopfeedback-view-result' );
			}		
			$return .= self::printOverview();
		}
		if ( $view == 'all' ) {
			if ( !$this->getUser()->isAllowed( 'loopfeedback-view-results' ) ) {
				throw new PermissionsError( 'loopfeedback-view-result' );
			}		
			$return .= self::printAll();
		}		
		if ( $view == 'page' ) {
			if ( !$this->getUser()->isAllowed( 'loopfeedback-view-results' ) ) {
				throw new PermissionsError( 'loopfeedback-view-result' );
			}		
			$return .= self::printPage( $page, $period_begin, $period_end);
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

		foreach ( $periods as $period) {

            $linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
            $linkRenderer->setForceArticlePath(true); #required for readable links
            $return .= $linkRenderer->makeLink(
                $specialtitle,
                new HtmlArmor( $period[ 'begin_text' ].' - '.$period[ 'end_text' ] ),
				array(),
                array (
					'view' => 'all',
					'period_begin' => $period[ 'begin' ],
					'period_end' => $period[ 'end' ]
				) ).'<br/>';		
		}

		return $return;
	}

	private function printConfirmResetPage ( $page,$period_begin,$period_end) {
		$return = '';
		
		$title = Title::newFromID( $page);
		
		$return .= wfMessage( 'loopfeedback-specialpage-confirm-reset-page' , $title->getPrefixedText() )->text();
		
		$return .= '<br/><br/>';
		
        $specialtitle = Title::newFromText( 'LoopFeedback', NS_SPECIAL );
        
        $linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
        $linkRenderer->setForceArticlePath(true); #required for readable links
        $return .= $linkRenderer->makeLink(
            $specialtitle,
			new HtmlArmor( wfMessage( 'loopfeedback-specialpage-cancel' )->text() ),
			array(
					'class' => 'loopfeedback-button'
				),
			array(
				'view' => 'page',
				'page' =>$page,
				'period_begin' => $period_begin,
				'period_end' => $period_end
			)
		);
		$return .= ' ';
		$reset_token = $this->getUser()->getEditToken( 'reset-feedback' );
        $return .= $linkRenderer->makeLink(
            $specialtitle,
			new HtmlArmor( wfMessage( 'loopfeedback-specialpage-confirm-reset-page-link' )->text() ),
			array(
					'class' => 'loopfeedback-button'
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
		
		return $return;
	}
	
	private function printConfirmResetAll ( $page, $period_begin, $period_end ) {
		$return = '';
		
		$title = Title::newFromID( $page );
		
		$return .= wfMessage( 'loopfeedback-specialpage-confirm-reset-all' )->text();
		$return .= '<br/><br/>';
		
		$specialtitle = Title::newFromText( 'LoopFeedback', NS_SPECIAL );
		$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
        $linkRenderer->setForceArticlePath(true); #required for readable links
		$return .= ' ';
		$reset_token = $this->getUser()->getEditToken( 'reset-feedback' );
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
	
	
	
	private function printPage( $page, $period_begin, $period_end) {
		global $wgLoopFeedbackLevel;
	
		if ( $period_begin == '' ) {
			$period_begin = '00000000000000';
		}
		if ( $period_end == '' ) {
			$period_end = '00000000000000';
		}
		$return = '';
	
		$return .= '<h1>'.wfMessage( 'loopfeedback-specialpage-header-detail' )->text().'</h1>';
	
		$title = Title::newFromID ( $page );
		
		
		if ( $wgLoopFeedbackLevel == 'chapter' ) {
			$return .= '<h2>'.wfMessage( 'loopfeedback-specialpage-feedback-for-chapter' )->text() . ' "' . $title->getPrefixedText().'"</h2>';
		} else {
			$return .= '<h2>'.wfMessage( 'loopfeedback-specialpage-feedback-for-module' )->text() . ' "' . $title->getPrefixedText().'"</h2>';
		}		
		
		
		
		$loopFeedback = new LoopFeedback;
		$feedback_periods = $loopFeedback->getPeriods( $page );	

		
		if (count( $feedback_periods)>1) {
			$return .= wfMessage( 'loopfeedback-specialpage-header-period' )->text();
			$specialtitle = Title::newFromText( 'LoopFeedback', NS_SPECIAL );
			foreach ( $feedback_periods as $feedback_period ) {
			
				
				
				// ( $feedback_period[ 'begin' ] == $period_begin) && 
				if ( ( $feedback_period[ 'end' ] == $period_end ) ) {
					$css_class='loopfeedback-period_button_active';
				} else {
					$css_class='loopfeedback-period_button';
				}
				
				$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
                $linkRenderer->setForceArticlePath(true); #required for readable links
                $return .= $linkRenderer->makeLink(
                    $specialtitle,
					new HtmlArmor( $feedback_period[ 'begin_text' ].' - '.$feedback_period[ 'end_text' ] ),
					array(
						'class' => $css_class
					),
					array (
						'view' => 'page',
						'page' => $page,
						'period_begin' => $feedback_period[ 'begin' ],
						'period_end' => $feedback_period[ 'end' ]
					) ).' ';				
			}
			$return .= '<br/><br/>';
		}	
	

		
		
		$feedback_detail = $loopFeedback->getDetails( $page,true,$period_end);	
		
		if ( $wgLoopFeedbackLevel == 'chapter' ) {
			$return .= wfMessage( 'loopfeedback-rating-descr-chapter' )->text().'<br/><br/>';
		} else {
			$return .= wfMessage( 'loopfeedback-rating-descr-module' )->text().'<br/><br/>';
		}
		
		$return .= self::printStars( $feedback_detail[ 'average' ],'article_'.$page.'_rating' ).' &nbsp; ';		
		
		
		
		if ( $feedback_detail[ 'count' ][ 'all' ] > 0) {
			$return .= wfMessage( 'loopfeedback-specialpage-feedback-info-sum-all', $feedback_detail[ 'count' ][ 'all' ])->text().', ';
			$return .= wfMessage( 'loopfeedback-specialpage-feedback-info-average-stars', $feedback_detail[ 'average' ])->text().'<br/>';				
		} else {
			$return .= wfMessage( 'loopfeedback-specialpage-feedback-info-no-feedback' )->text().'<br/>';
		}			
		$return .= '<br/>';
		
		$a= $feedback_detail[ 'count' ][ 'all' ];
		for ( $i=5;$i>=1;$i--) {
			if ( $a > 0) {
				$c= $feedback_detail[ 'count' ][$i];
				$f = ( $c/$a) * 100;
				$e = 100 - ( ( $c/$a) * 100);
			} else {
				$f = 0;
				$e = 100;
			}
			$return .= '<div class="loopfeedback-bar">';
			//$return .= '<div class="loopfeedback-bar_descr">'.wfMessage( 'loopfeedback-specialpage-feedback-info-stars', $i)->text().'</div>';
			$return .= '<div class="loopfeedback-bar_descr">'.self::printStars( $i,'stars_'.$i,wfMessage( 'loopfeedback-specialpage-feedback-info-stars', $i)->text() ).'</div>';
			$return .= '<div title="'.round( $f).'%" class="loopfeedback-bar_full" style="width:'.$f.'px;"></div>';
			$return .= '<div class="loopfeedback-bar_empty" style="width:'.$e.'px;"></div>';
			$return .= '<div class="loopfeedback-bar_info" > ( '.$feedback_detail[ 'count' ][$i].' )</div>';
			$return .= '</div>';
		}
		
		
		$return .= '<br/><br/>';
		if ( $feedback_detail[ 'count' ][ 'comments' ] > 0) {
			$return .= wfMessage( 'loopfeedback-specialpage-feedback-info-count-comments', $feedback_detail[ 'count' ][ 'comments' ])->text();
		} else {
			$return .= wfMessage( 'loopfeedback-specialpage-feedback-info-no-comments' )->text();
		}

		if ( $this->getUser()->isAllowed( 'loopfeedback-view-comments' ) ) {
			foreach ( $feedback_detail[ 'comments' ] as $comment) {
				$return .= '<p><strong>'.$comment[ 'timestamp_text' ].'</strong><br/>'.$comment[ 'comment' ].'</p>';
			}				
		}		

		$return .= '<br/><hr/><br/>';
		$specialtitle = Title::newFromText( 'LoopFeedback', NS_SPECIAL );
		
		if ( $wgLoopFeedbackLevel == 'chapter' ) {
			$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
            $linkRenderer->setForceArticlePath(true); #required for readable links
            $return .= $linkRenderer->makeLink(
                $specialtitle,
				new HtmlArmor( wfMessage( 'loopfeedback-specialpage-all-link' ) ),
				array(
					'class' => 'loopfeedback-button'
				),
				array (
					'view' => 'all',
					'period_begin' => $period_begin,
					'period_end' => $period_end)
			);
			$return .= ' ';
		}
		
		if ( $period_end == '00000000000000' ) {
			// $reset_token = $this->getUser()->getEditToken( 'reset-feedback' );
			
			$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
            $linkRenderer->setForceArticlePath(true); #required for readable links
            $return .= $linkRenderer->makeLink(
                $specialtitle,
                new HtmlArmor( wfMessage( 'loopfeedback-specialpage-reset-page-link' ) ),
				array(
					'class' => 'loopfeedback-button'
				),
				array (
					'page' =>$page,
					'action' => 'reset_page',
					// 'token' => $reset_token,
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
			$condition = 'TocLevel = 0'; #TODO was macht das für einen unterschied?
		} elseif ( $wgLoopFeedbackLevel == 'chapter' ) {
			$condition = 'TocLevel = 1';
		}
        
        
        $loopStructure = new LoopStructure();
        $loopStructureItems = $loopStructure->getStructureItems();
        
/*
        #TODO STRUCTURE
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
                'ParentArticleId'
                ),
                array(
                0 => $condition
                ),
                __METHOD__,
                array(
				'ORDER BY' => 'Sequence ASC'
				)
                );		*/
                
		$n = 0;
		foreach ( $loopStructureItems as $lsi ) {
			$n++;
			$return .= '<tr><td class="pl-2 pr-1">';
			
			$specialtitle = Title::newFromText( 'LoopFeedback', NS_SPECIAL );
			$lf_item_text = '';
			if ( $wgLoopLegacyPageNumbering ) {
				$lf_item_text .= $lsi->tocNumber .' ';
			}
			$lf_item_text .= $lsi->tocText;
			$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
            $linkRenderer->setForceArticlePath(true); #required for readable links

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
			
			$return .= '<td class="pl-2 pr-1">'. self::printStars( $feedback_detail[ 'average' ], 'article_'. $lsi->article .'_rating' ).'</td>';					
			
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
		
		$return .= '</table>';
		$return .= '<br/><hr/>';
		
        $linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
        $linkRenderer->setForceArticlePath(true); #required for readable links
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
	
	
	private function printStars( $rating, $name, $title = '' ) {
		if ( $title == '' ) {
			$title = $rating;
			}
		$rating = round( $rating );
		
	$printreturn = '<div class="lf_rating_wrapper">
<input name="'.$name.'" type="radio" class="loopfeedback-star" title="'.$title.'" value="'.$rating.'" disabled '. ( $rating == 1 ? 'checked' : '' ) .'/>
<input name="'.$name.'" type="radio" class="loopfeedback-star" title="'.$title.'" value="2.0" disabled '. ( $rating == 2 ? 'checked' : '' ) .'/>
<input name="'.$name.'" type="radio" class="loopfeedback-star" title="'.$title.'" value="3.0" disabled '. ( $rating == 3 ? 'checked' : '' ) .'/>
<input name="'.$name.'" type="radio" class="loopfeedback-star" title="'.$title.'" value="4.0" disabled '. ( $rating == 4 ? 'checked' : '' ) .'/>
<input name="'.$name.'" type="radio" class="loopfeedback-star" title="'.$title.'" value="5.0" disabled '. ( $rating == 5 ? 'checked' : '' ) .'/>
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
		
		$dbw = wfGetDB( DB_MASTER );
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
		
		$dbw = wfGetDB( DB_MASTER );
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