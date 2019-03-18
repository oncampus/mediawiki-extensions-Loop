<?php 

class LoopUpdater {
	
	
	/**
	 * Updates Database
	 * 
	 * @param DatabaseUpdater $du
	 * @return bool true
	 */
	public static function onLoadExtensionSchemaUpdates( DatabaseUpdater $updater ) {
		
		global $wgScriptPath;

		$updater->addExtensionUpdate(array( 'addTable', 'loop_structure_items', dirname( __FILE__ ) . '/loop_structure_items.sql', true ));
		$updater->addExtensionUpdate(array( 'addTable', 'loop_structure_properties', dirname( __FILE__ ) . '/loop_structure_properties.sql', true ) );
		$updater->addExtensionUpdate(array( 'addTable', 'loop_settings', dirname( __FILE__ ) . '/loop_settings.sql', true ) );

		$extraFooterPage = WikiPage::factory( Title::newFromText( "MediaWiki:ExtraFooter" ));
		$extraFooterContent = new WikitextContent( wfMessage( 'loopsettings-extra-footer-placeholder', $wgScriptPath )->inContentLanguage()->text() );
		$extraFooterPage->doEditContent( $extraFooterContent, '', EDIT_NEW );

		return true;
	}
	
}
?>