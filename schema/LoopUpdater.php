<?php 

class LoopUpdater {
	
	
	/**
	 * Updates Database
	 * 
	 * @param DatabaseUpdater $du
	 * @return bool true
	 */
	public static function onLoadExtensionSchemaUpdates( DatabaseUpdater $updater ) {

		$updater->addExtensionUpdate(array( 'addTable', 'loop_structure_items', dirname( __FILE__ ) . '/loop_structure_items.sql', true ));
		$updater->addExtensionUpdate(array( 'addTable', 'loop_structure_properties', dirname( __FILE__ ) . '/loop_structure_properties.sql', true ) );
		$updater->addExtensionUpdate(array( 'addTable', 'loop_settings', dirname( __FILE__ ) . '/loop_settings.sql', true ) );
		$updater->addExtensionUpdate(array( 'addTable', 'loop_object_index', dirname( __FILE__ ) . '/loop_object_index.sql', true ) );
		
		#Loop::setupLoopPages(); #causes error on update
		#error_log("working");
		
		$dbr = wfGetDB( DB_REPLICA );
		if ( $dbr->tableExists( 'loop_object_index' ) ) { #sonst bricht der updater ab. updater muss so jetzt zweimal laufen #todo
			Loop::setupLoopPages();
			self::saveAllWikiPages();
		}
		return true;
	}

	public static function saveAllWikiPages() {
		
		global $wgOut;
		$dbr = wfGetDB( DB_REPLICA );
		$res = $dbr->select(
			array(
				'page'
			),
			array(
				'page_id',
				'page_namespace'
			),
			array(
				'page_namespace = 0'
			),
			__METHOD__
		);
		
		foreach( $res as $row ) {
			error_log("started " . $row->page_id);
			#todo $wgparser
			$title = Title::newFromId( $row->page_id, NS_MAIN );
			$tmpFPage = new FlaggableWikiPage ( Title::newFromId( $row->page_id, NS_MAIN ) );
			#$content = $tmpFPage->getContent();
			$stableRev = $tmpFPage->getStable();
			if ( $stableRev == 0 ) {
				#$stableRev = $tmpFPage->getRevision()->getId();
				error_log("save unstable" . $stableRev .  " of " . $row->page_id);
			} else {
				error_log("save stable" . $stableRev .  " of " . $row->page_id);
			}
			Hooks::run( 'LoopUpdateSavePage', array( $title ) );
			#error_log($tmpFPage->doEditContent( $tmpFPage->getContent(), '', EDIT_INTERNAL, $stableRev, $wgOut->getUser() )->getMessage());
			#$tmpPage = WikiPage::factory( Title::newFromId( $row->page_id, NS_MAIN ));
			#$tmpPage->doEditContent( $tmpPage->getContent(), '', EDIT_FORCE_BOT, $tmpPage->getRevision()->getId(), $wgOut->getUser() );
			error_log("finished " . $row->page_id);
		}
	}
}
?>