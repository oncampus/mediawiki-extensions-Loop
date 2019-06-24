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
		
		#Loop::setupLoopPages();
		error_log("working");
		
		$dbr = wfGetDB( DB_REPLICA );
		if ( $dbr->tableExists( 'loop_object_index' ) ) { #sonst bricht der updater ab. updater muss so jetzt zweimal laufen #todo
		
			self::saveAllWikiPages();
		}
		return true;
	}

	private static function saveAllWikiPages() {
		
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
			error_log("working " . $row->page_id);
			#todo $wgparser
			$tmpFPage = new FlaggableWikiPage ( Title::newFromId( $row->page_id, NS_MAIN ) );
			$stableRev = $tmpFPage->getStable();
			if ( $stableRev == 0 ) {
				$stableRev = $tmpFPage->getRevision()->getId();
			}
			$tmpFPage->doEditContent( $tmpFPage->getContent(), '', EDIT_FORCE_BOT, $stableRev, $wgOut->getUser() );
			#$tmpPage = WikiPage::factory( Title::newFromId( $row->page_id, NS_MAIN ));
			#$tmpPage->doEditContent( $tmpPage->getContent(), '', EDIT_FORCE_BOT, $tmpPage->getRevision()->getId(), $wgOut->getUser() );
		}
	}
}
?>