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

		# LOOP1 to LOOP2 migration process #LOOP1UPGRADE
		if ( $dbr->tableExists( 'loop_object_index' ) && $dbr->tableExists( 'loopstructure' )  ) { #sonst bricht der updater ab. updater muss so jetzt zweimal laufen #todo
			
			$systemUser = User::newSystemUser( 'LOOP_SYSTEM', [ 'steal' => false, 'create'=> true, 'validate' => true ] ); #beobachten, ob das Anlegen hier ausreicht
			$systemUser->addGroup("sysop");
			self::saveAllWikiPages();
			self::migrateGlossary();
			$updater->addExtensionUpdate(array( 'dropTable', 'loopstructure', dirname( __FILE__ ) . '/loopstructure_delete.sql', true ) );
		}
		
		return true;
	}

	/**
	 * Re-saves every wikipage in main namespace
	 * Rendering will be updated; IDs will be given etc
	 */
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

	/**
	 * Migrates LOOP 1 glossary category and pages to new namespace 
	 * Used in LOOP 1 update process only #LOOP1UPGRADE
	 */
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
		$glossaryCategoryWikiPage->doDeleteArticle( 'Moved to Special:Glossary / Spezial:Glossar' );
	}

	
	/**
	 * Custom hook called when updating LOOP
	 * - Indexes given LOOP-Objects on page
	 * 
	 * @param Title $title
	 */
	public static function onLoopUpdateSavePage( $title ) {
		
		$latestRevId = $title->getLatestRevID();
		$wikiPage = WikiPage::factory($title);
		$fwp = new FlaggableWikiPage ( $title );
		$systemUser = User::newSystemUser( 'LOOP_SYSTEM', [ 'steal' => true, 'create'=> false, 'validate' => true ] );
		
		if ( isset( $fwp ) ) {
			$stableRevId = $fwp->getStable();

			if ( $latestRevId == $stableRevId || $stableRevId == null ) { # page is stable or does not have any stable version
				$content = null;
				$contentText = null;
			} else {
				$revision = $wikiPage->getRevision();
				$content = $revision->getContent();
				$contentText = $revision->getContent()->getNativeData ();
			}
			LoopObject::doIndexLoopObjects( $wikiPage, $title, $content, $systemUser );
			self::migrateLiterature( $wikiPage, $title, $contentText, $systemUser );
		}
		return true;
	}

	
	/**
	 * Migrates LOOP 1 tag biblio
	 * - bibliography page "Literatur" and adds given entries to database
	 * Used in LOOP 1 update process only #LOOP1UPGRADE
	 */
	public static function migrateLiterature( $wikiPage, $title, $contentText, $systemUser ) {
		$literaturePage = false;
		$stableRev = false;
		if ( $title->mTextform == "Literatur" ) {
			$literaturePage = true;
		}
		if ( $contentText == null ) {
			$revision = $wikiPage->getRevision();
			$contentText = $revision->getContent()->getNativeData ();
		}
		$parser = new Parser();
		$biblio_tags = array ();
		$loopliterature_tags = array ();
		$parser->extractTagsAndParams( array( 'biblio' ), $contentText, $biblio_tags );
		#dd($biblio_tags);
		if ( !empty ( $biblio_tags ) ) {
			error_log("Migrating bibliography");
			foreach ( $biblio_tags as $biblio ) {
				$newTagContent = "\n"; 
				$rows = explode( "\n", $biblio[1] );
				
				foreach ( $rows as $row ) {
					if ( !empty ( $row ) ) {
						$output_array = array();
						preg_match('/(#{1})(.{1,})(\s{1,})(.*\z)/U', $row, $output_array);
						if ( isset ( $output_array[2] ) && isset ( $output_array[4] ) ) {
							$key = $output_array[2];
							$text = $output_array[4];
							$text = str_replace( "isbn=", "ISBN: ", $text);

							$existingLiterature = new LoopLiterature();
							$existingLiterature->loadLiteratureItem( $key );
							if ( empty( $existingLiterature->itemKey ) ) {
								$li = new LoopLiterature();
								$li->itemKey = $key;
								$li->itemType = "LOOP1";
								$li->author = str_replace( "+", " ", $key);
								$li->note = $text;
								$li->addToDatabase();
							}
							$newTagContent .= $key . "\n";
							
						}
					}
				}
				$loopliterature_tags[] = $newTagContent;
			}
			if ( $literaturePage ) {
				$newRedirectContent = new WikitextContent( "#REDIRECT [[" . wfMessage( "namespace-special" )->inContentLanguage()->text() . ":" . wfMessage( "loopliterature" )->inContentLanguage()->text() . "]]" );
				$wikiPage->doEditContent( $newRedirectContent, 'Redirect to new bibliography', EDIT_UPDATE, false, $user );	
			} else {
				$xmlText = mb_convert_encoding("<?xml version='1.0' encoding='utf-8'?>\n<div>" .$contentText.'</div>', 'HTML-ENTITIES', 'UTF-8');
				$dom = new DOMDocument("1.0", 'utf-8');
				@$dom->loadHTML( $xmlText, LIBXML_HTML_NODEFDTD );
				
				$bibliotags = $dom->getElementsByTagName('biblio');

				$i = 0;
				foreach ( $bibliotags as $element ) {
					$newElement = $dom->createElement('loop_literature');
					$textNode = $dom->createTextNode( $loopliterature_tags[$i] );
					$newElement->appendChild( $textNode );
					$element->parentNode->replaceChild($newElement, $element);
					$i++;
				}
				$changedText = mb_substr($dom->saveHTML(), 55, -21);
				$decodedText = html_entity_decode($changedText);
				
				$content = $wikiPage->getContent();
				$content = $content->getContentHandler()->unserializeContent( $decodedText );
				$summary = 'Replaced biblio tag';
				$wikiPage->doEditContent ( $content, $summary, EDIT_UPDATE, $stableRev, $systemUser );
			}
		}

		return true;
	}

}
?>