<?php
class LoopIndex {

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
	function addToDatabase() {

        $dbw = wfGetDB( DB_MASTER );
        
        $dbw->insert(
            'loop_structure_items',
            array(
                'li_id' => $this->id,
                'li_pageid' => $this->pageId,
                'li_refid' => $this->refId,
                'li_index' => $this->index,
                'li_nthoftype' => $this->nthItem,
                'li_itemtype' => $this->itemType,
                'li_itemtitle' => $this->itemTitle,
                'li_itemdesc' => $this->itemDescription,
                'li_thumb' => $this->itemThumb
            ),
            __METHOD__
        );
        #$this->id = $dbw->insertId();
    
        return true;

    }

    public function getIndexedItemsFromPage ( $article, $index ) {

		$dbr = wfGetDB( DB_SLAVE );
		$prev_id =  $dbr->select(
            'loop_index',
			'lsi_article',
			array(
				0 => "lsi_sequence < '".$this->sequence."'",
				1 => "lsi_structure = '".$this->structure."'",
				2 => "lsi_toc_level = 1"
			),
			__METHOD__,
			array(
				'ORDER BY' => 'lsi_sequence DESC'
			)
		);

		if( ! empty( $prev_id )) {
			return LoopStructureItem::newFromIds( $prev_id );
		} else {
			return false;
		}

	}
    
    public function removeAllPageItemsFromDb ( $article ) {

        return true;
    }

    public function getItemNumber ( $refId ) {

        return true;
    }

}