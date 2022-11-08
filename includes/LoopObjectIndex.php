<?php

if ( !defined( 'MEDIAWIKI' ) ) die ( "This file cannot be run standalone.\n" );

class LoopObjectIndex {

    public $id; // id of the indexed item
	public $pageId; // article id of the page the item is on
	public $refId; // reference id of the item
	public $index; // the index an item is displayed in (figure, table, etc)
	public $nthItem; // the n-th item of that index on that page
	public $itemType; // the item's type (only for loop_media: rollover, animation, etc)
	public $itemTitle; // the entered title
	public $itemDescription; // the entered description
	public $itemThumb; // preview image (only for loop_figure)

	/**
	 * Add indexable item to the database
	 * @return bool true
	 */
	public function addToDatabase() {
        $dbw = wfGetDB( DB_PRIMARY );

        $dbw->insert(
            'loop_object_index',
            array(
                'loi_pageid' => $this->pageId,
                'loi_refid' => $this->refId,
                'loi_index' => $this->index,
                'loi_nthoftype' => $this->nthItem,
                'loi_itemtype' => $this->itemType,
                'loi_itemtitle' => $this->itemTitle,
                'loi_itemdesc' => $this->itemDescription,
                'loi_itemthumb' => $this->itemThumb
            ),
            __METHOD__
        );
        $this->id = $dbw->insertId();
        return true;

    }

	// deletes all objects of a page
    public static function removeAllPageItemsFromDb ( $article ) {

		$dbr = wfGetDB( DB_PRIMARY );
		$dbr->delete(
			'loop_object_index',
			'loi_pageid = ' . $article,
			__METHOD__
		);

        return true;
    }

    // returns ALL objects of a type in the wiki.
    public static function getObjectsOfType ( $type ) {

        $dbr = wfGetDB( DB_REPLICA );

        $res = $dbr->select(
            'loop_object_index',
            array(
                'loi_pageid',
                'loi_refid',
                'loi_index',
                'loi_nthoftype',
                'loi_itemtype',
                'loi_itemtitle',
                'loi_itemdesc',
                'loi_itemthumb'
            ),
            array(
                'loi_index = "' . $type .'"'
            ),
            __METHOD__
            );

        $objects = array(  );
        foreach( $res as $row ) {
            $objects[$row->loi_pageid][$row->loi_nthoftype] = array(
                "args" => array("id" => $row->loi_refid,
                    "title" => $row->loi_itemtitle,
                    "description" => $row->loi_itemdesc,
                    "type" => $row->loi_itemtype,
                    "id" => $row->loi_refid
                ),
                "thumb" => $row->loi_itemthumb,
                "nthoftype" => $row->loi_nthoftype
            );
        }
        return $objects;
    }

    // returns structure objects with numberings in the table
    public static function getAllObjects ( $loopStructure ) {

        global $wgLoopObjectNumbering;

        $dbr = wfGetDB( DB_REPLICA );

        $res = $dbr->select(
            'loop_object_index',
            array(
                'loi_pageid',
                'loi_refid',
                'loi_index',
                'loi_nthoftype',
                'loi_itemtype',
                'loi_itemtitle',
                'loi_itemdesc',
                'loi_itemthumb'
            ),
            array(
            ),
            __METHOD__
            );

        $objects = array(  );

        $loopStructureItems = $loopStructure->getStructureItems();

        foreach ( $loopStructureItems as $loopStructureItem ) {
            $previousObjects[ $loopStructureItem->article ] = self::getObjectNumberingsForPage( $loopStructureItem, $loopStructure );
        }

		$glossaryItems = LoopGlossary::getGlossaryPages("idArray");
        foreach ( $glossaryItems as $glossaryItem ) {
            $previousObjects[ $glossaryItem ] = self::getObjectNumberingsForGlossaryPage( $glossaryItem );
        }
        #dd($previousObjects);
        foreach( $res as $row ) {

            $numberText = '';

            if ( $wgLoopObjectNumbering == true ) {

                $objectData = array(
                    "refId" => $row->loi_refid,
                    "index" => $row->loi_index,
                    "nthoftype" => $row->loi_nthoftype
                );

                $lsi = LoopStructureItem::newFromIds($row->loi_pageid);
                if ( array_key_exists( $row->loi_pageid, $previousObjects ) ) {
                    if ( $lsi ) {
                        $pageData = array( "structure", $lsi, $loopStructure );
                        $numberText = LoopObject::getObjectNumberingOutput( $row->loi_refid, $pageData, $previousObjects[ $row->loi_pageid ], $objectData );
                    } elseif ( isset ( $previousObjects[ $row->loi_pageid ] ) ) {
                        $pageData = array( "glossary", $row->loi_pageid );
                        $numberText = LoopObject::getObjectNumberingOutput( $row->loi_refid, $pageData, $previousObjects[ $row->loi_pageid ], $objectData );
                    }
                }

            }

            $objects[$row->loi_refid] = array(
                "pageid" => $row->loi_pageid,
                "id" => $row->loi_refid,
                "title" => $row->loi_itemtitle,
                "description" => $row->loi_itemdesc,
                "index" => $row->loi_index,
                "type" => $row->loi_itemtype,
                "id" => $row->loi_refid,
                #"thumb" => $row->loi_itemthumb,
                "nthoftype" => $row->loi_nthoftype,
                "objectnumber" => $numberText
            );
        }
        #dd($objects);
        return $objects;
    }

	// returns number of objects in structure before the given structureItem
    public static function getObjectNumberingsForPage ( LoopStructureItem $lsi, LoopStructure $loopStructure ) {

		global $wgLoopNumberingType;

		if ( $wgLoopNumberingType == "chapter" ) {

			$lsiTocNumberArray = array();
			$lsiTocNumber = '';
			preg_match('/(\d+)\.{0,1}/', $lsi->tocNumber, $lsiTocNumberArray);

			if (isset($lsiTocNumberArray[1])) {
				#dd($lsiTocNumber);
				$lsiTocNumber = $lsiTocNumberArray[1];
			}
		}

		$objects = array();
		foreach (LoopObject::$mObjectTypes as $objectType) {
			$objects[$objectType] = array();
			$return[$objectType] = 0;
		}

		$dbr = wfGetDB( DB_REPLICA );

		$res = $dbr->select(
			'loop_object_index',
			array(
                'loi_pageid',
                'loi_refid',
                'loi_index'
			),
			"*",
			__METHOD__
		);
		foreach( $res as $row ) {
			$objects[$row->loi_index][$row->loi_pageid][] = $row->loi_refid;
		}

		$structureItems = $loopStructure->getStructureItems();
		if ( $wgLoopNumberingType == "ongoing" ) {
			foreach ( $structureItems as $item ) {
				$tmpId = $item->article;
				if (  $item->sequence < $lsi->sequence  ) {
					foreach( $objects as $objectType => $page ) {
						if ( isset( $page[$tmpId] ) ) {
							$return[$objectType] += sizeof($page[$tmpId]);
						}
					}
				}
			}
		} elseif ( $wgLoopNumberingType == "chapter" ) {
			foreach ( $structureItems as $item ) {
				$tmpId = $item->article;
				$tocNumber = array();
				preg_match('/(\d+)\.{0,1}/', $item->tocNumber, $tocNumber);

				if ( isset( $tocNumber[1] ) && $tocNumber[1] == $lsiTocNumber ) {
					if (  $item->sequence < $lsi->sequence  ) {
						foreach( $objects as $objectType => $page ) {
							if ( isset( $page[$tmpId] ) ) {
								$return[$objectType] += sizeof($page[$tmpId]);
							}
						}
					}
				}
			}
		}
        return $return;
    }

	// returns number of objects in glossary pages before current glossary page
    public static function getObjectNumberingsForGlossaryPage ( $articleId ) {
        $glossaryItems = LoopGlossary::getGlossaryPages();
        $data = array();
        $return = array();
        $pageHasObjects = false;
        if ( !empty ($glossaryItems) ) {
            foreach ( $glossaryItems as $sequence => $item ) {
                $tmpArticleId = $item->getArticleID();
                $data[$sequence] = array( $tmpArticleId );
                if ( $tmpArticleId == $articleId ) {
                    $pageHasObjects = true;
                    break;
                }
            }
        }
        if ( $pageHasObjects ) {

            $objects = array();
            foreach ( LoopObject::$mObjectTypes as $objectType ) {
                $objects[$objectType] = array();
                $return[$objectType] = 0;
            }

            $dbr = wfGetDB( DB_REPLICA );

            $res = $dbr->select(
                'loop_object_index',
                array(
                    'loi_pageid',
                    'loi_refid',
                    'loi_index'
                ),
                "*",
                __METHOD__
            );
            foreach ( $res as $row ) {
                $objects[$row->loi_index][$row->loi_pageid][] = $row->loi_refid;
            }

            foreach ( $data as $pos => $tmpId ) {
                foreach( $objects as $objectType => $page ) {
                    if ( $tmpId[0] != $articleId ) {
                        if ( array_key_exists( $tmpId[0], $page ) ) {
                            $return[$objectType] += sizeof($page[$tmpId[0]]);
                        }
                    }
                }
            }
        }
        return $return;
    }

	public function checkDublicates( $refId ) {

		$dbr = wfGetDB( DB_REPLICA );
		$res = $dbr->select(
			'loop_object_index',
			array(
                'loi_refid'
			),
			array(
				'loi_refid = "' . $refId .'"'
			),
			__METHOD__
		);

		foreach( $res as $row ) {
            # if res has rows,
			# given refId is already in use.
			return false;

		}
		# id is unique in index
		return true;
    }

    public static function getObjectData( $refId ) {

        $dbr = wfGetDB( DB_REPLICA );
		$res = $dbr->select(
			'loop_object_index',
			array(
                'loi_pageid',
                'loi_refid',
                'loi_index',
                'loi_itemtype',
                'loi_itemtitle',
                'loi_itemdesc',
                'loi_nthoftype'
			),
			array(
				'loi_refid = "' . $refId .'"'
			),
			__METHOD__
		);
		foreach( $res as $row ) {

            #$lsi = LoopStructureItem::newFromIds ( $row->loi_pageid );
            $return = array(
                'refId' => $row->loi_refid,
                'articleId' => $row->loi_pageid,
                'index' => $row->loi_index,
                'title' => $row->loi_itemtitle,
                'description' => $row->loi_itemdesc,
                'type' => $row->loi_itemtype,
                'nthoftype' => $row->loi_nthoftype,
            );

			return $return;

		}
		# id unknown
		return false;

    }

}
