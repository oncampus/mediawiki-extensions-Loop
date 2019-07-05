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
		$updater->addExtensionUpdate(array( 'addTable', 'loop_literature_items', dirname( __FILE__ ) . '/loop_literature_items.sql', true ) );
		$updater->addExtensionUpdate(array( 'addTable', 'loop_literature_references', dirname( __FILE__ ) . '/loop_literature_references.sql', true ) );
		
		$dbr = wfGetDB( DB_REPLICA );

		if ( $dbr->tableExists( 'loop_structure_items' ) ) {
			Loop::setupLoopPages();
		}

		# LOOP1 to LOOP2 migration process
		if ( $dbr->tableExists( 'loop_object_index' ) && $dbr->tableExists( 'loopstructure' )  ) { #sonst bricht der updater ab. updater muss so jetzt zweimal laufen #todo
			self::saveAllWikiPages();
			$updater->addExtensionUpdate(array( 'dropTable', 'loopstructure', dirname( __FILE__ ) . '/loopstructure_delete.sql', true ) );
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
			$title = Title::newFromId( $row->page_id, NS_MAIN );
			$tmpFPage = new FlaggableWikiPage ( Title::newFromId( $row->page_id, NS_MAIN ) );
			$stableRev = $tmpFPage->getStable();
			if ( $stableRev == 0 ) {
				$stableRev = $tmpFPage->getRevision()->getId();
			} 
			error_log("Updating page " . $row->page_id . " (revision " . $stableRev .  ")");
			Hooks::run( 'LoopUpdateSavePage', array( $title ) );
		}
	}
}
?>