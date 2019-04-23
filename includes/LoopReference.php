<?php
class LoopReference {

public static function refreshReferences( $text ) {
	
		
    $dom = new DOMDocument;
    @$dom->loadHTML( $textb, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
    
    $xpath = new DOMXPath( $dom );
    
    $autoIdTags = array();
    foreach (LoopObject::$mObjectTypes as $autoIdTag) {
        $autoIdTags[] = '//'.$autoIdTag;
    }
    $query = implode(' | ', $autoIdTags);
    
    $nodes = $xpath->query( $query );
    
    $references = array();
    
    foreach ( $nodes as $node ) {
        $referenceId = $node->getAttribute( 'id' );
        if ($referenceId) {
            $references[] = $referenceId;
        }
    }		
    
    if ($references) {
    
        $dbr = wfGetDB( DB_SLAVE );
        
            
        $cond = array();
        foreach ($references as $ref) {
            $cond[] = "(old_text LIKE '%".$ref."%')";
        }
        $condition = "(".implode(" or ", $cond).")";
        
        // get pages with references to $oldReferenceId
        $dbResult = $dbr -> select (
                array (
                        'page',
                        'revision',
                        'text'
                ),
                array (
                        'page.page_id',
                        'text.old_text',
                        'text.old_id',
                        'revision.rev_text_id'
                ),
                array (
                        // "old_text LIKE '%id=\"".$referenceId."\"%'",
                        $condition,
                        "page.page_latest=revision.rev_id",
                        "revision.rev_text_id=text.old_id",
                ),
                __METHOD__
                );
            
            
        $dbw = wfGetDB( DB_MASTER );
        foreach ( $dbResult as $row ) {
        
            $pageId = $row->page_id;
        
            $dbPageTouchedResult = $dbw -> update(
                    'page',
                    ['page_touched' => $dbw->timestamp()],
                    ['page_id="'.$pageId.'"'],
                    __METHOD__
                    );
        }		
    }	
    

    return true;
        
}

}