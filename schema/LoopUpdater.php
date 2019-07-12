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
			
			$systemUser = User::newSystemUser( 'LOOP_SYSTEM', [ 'steal' => false, 'create'=> true, 'validate' => true ] ); #beobachten, ob das Anlegen hier ausreicht
			$systemUser->addGroup("sysop");
			self::saveAllWikiPages();
			self::migrateGlossary();
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
			error_log("Updating page " . $row->page_id . " (rev " . $stableRev .  ")");
			Hooks::run( 'LoopUpdateSavePage', array( $title ) );
		}
	}

	public static function migrateGlossary() {

        $glossary = Category::newFromName("Glossar");
		$glossaryItems = $glossary->getMembers();

		if ( !empty( $glossaryItems ) ) {
			foreach ( $glossaryItems as $title ) {

				$user = User::newSystemUser( 'LOOP_SYSTEM', [ 'steal' => true, 'create'=> false, 'validate' => true ] );

				$oldWikiPage = WikiPage::factory ( $title );
				$oldFlaggableWikiPage = new FlaggableWikiPage ( $title );
				$stableRev = $oldFlaggableWikiPage->getStable();
				if ( $stableRev == 0 ) {
					$stableRev = intval( $title->mArticleID );
					$content = $oldWikiPage->getContent ()->getNativeData ();
				} else {
					$content = Revision::newFromId( $stableRev )->getContent ()->getNativeData ();
				}
				
				# Fill a new page in NS_GLOSSARY with the same title as before with the old content
				$newGlossaryPage = WikiPage::factory( Title::newFromText( $title->mTextform, NS_GLOSSARY ));
				$newGlossaryPageContent = new WikitextContent( preg_replace( '/(\[\[)(Kategorie){0,1}(Category){0,1}(:Glossar\]\])/i', '', $content ) ); // removes [[Kategorie:Glossar]] and [[Category:Glossar]] 
				$newGlossaryPage->doEditContent( $newGlossaryPageContent, '', EDIT_NEW, false, $user );

				$lsi = LoopStructureItem::newFromIds( $title->mArticleID );
				if ( !$lsi ) {
					# Redirecting the old page to the new glossary namespace page, if it is not in structure
					$newRedirectContent = new WikitextContent( "#REDIRECT [[" . wfMessage( "loop-glossary-namespace" )->inContentLanguage()->text() . ":" . $title->mTextform . "]]" );
					$oldWikiPage->doEditContent( $newRedirectContent, 'Redirect', EDIT_UPDATE, false, $user );
					error_log("Moving and redirecting glossary page " . $title->mArticleID . " (rev " . $stableRev .  ")");
				} else {
					error_log("Copying glossary page " . $title->mArticleID . " (rev " . $stableRev .  ")");
				}
				
			}
		}
		
		$glossaryCategoryWikiPage = WikiPage::factory( Title::newFromText( "Glossar", NS_CATEGORY ));
		$glossaryCategoryWikiPage->doDeleteArticle( 'Moved to Special:Glossary' );
	}
}
?>