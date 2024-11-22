<?php
/**
  * @description Adds LOOP functions to update/upgrade process
  * @ingroup Extensions
  * @author Dennis Krohn <dennis.krohn@th-luebeck.de>
  */

if ( !defined( 'MEDIAWIKI' ) ) die ( "This file cannot be run standalone.\n" );

use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\SlotRecord;

class LoopUpdater {

	/**
	 * Updates Database
	 *
	 * @param DatabaseUpdater $du
	 * @return bool true
	 */
	public static function onLoadExtensionSchemaUpdates( DatabaseUpdater $updater ) {
		global $IP;
		$schemaPath = $IP . '/extensions/Loop/schema/';

		$updater->addExtensionUpdate(array( 'addTable', 'loop_structure_items', $schemaPath . 'loop_structure_items.sql', true ));
		$updater->addExtensionUpdate(array( 'addTable', 'loop_structure_properties', $schemaPath . 'loop_structure_properties.sql', true ) );
		$updater->addExtensionUpdate(array( 'addTable', 'loop_settings', $schemaPath . 'loop_settings.sql', true ) );
		$updater->addExtensionUpdate(array( 'addTable', 'loop_object_index', $schemaPath . 'loop_object_index.sql', true ) );
		$updater->addExtensionUpdate(array( 'addTable', 'loop_literature_items', $schemaPath . 'loop_literature_items.sql', true ) );
		$updater->addExtensionUpdate(array( 'addTable', 'loop_literature_references', $schemaPath . 'loop_literature_references.sql', true ) );
		$updater->addExtensionUpdate(array( 'addTable', 'loop_index', $schemaPath . 'loop_index.sql', true ) );
		$updater->addExtensionUpdate(array( 'addTable', 'loop_feedback', $schemaPath . 'loop_feedback.sql', true ) );
		$updater->addExtensionUpdate(array( 'addTable', 'loop_progress', $schemaPath . 'loop_progress.sql', true ) );

		if ( $updater->tableExists( 'actor' ) ) {
			$user = User::newFromName( 'LOOP_SYSTEM' );
			if ( $user->getId() == 0 ) {
				$user = User::newSystemUser( 'LOOP_SYSTEM', array( 'steal' => true, 'create'=> true, 'validate' => 'valid' ) );
			}
		}

		if ( $updater->tableExists( 'loop_structure_items' ) ) {
			$systemUser = $user;
			$userGroupManager = MediaWikiServices::getInstance()->getUserGroupManager();
			if ( $systemUser->getId() != 0 ) { #why is system user null sometimes?
				$userGroupManager->addUserToGroup ( $systemUser, "sysop" );
			}
			Loop::setupLoopPages();
		}
		/*
		# LOOP1 to LOOP2 migration process #LOOP1UPGRADE
		if ( $updater->tableExists( 'loop_structure_items' ) && $updater->tableExists( 'loopstructure' )  ) { #sonst bricht der updater ab. updater muss so jetzt zweimal laufen

			if ( isset( $wgLoopAddToSettingsDB ) ) { # update settings DB from LocalSettings
				if ( !empty( $wgLoopAddToSettingsDB )) {
					self::addOldSettingsToDb();
				}
			}

			$updater->addExtensionUpdate(array( 'modifyTable', 'loop_structure_items', $schemaPath . 'loop_structure_items_modify.sql', true ) );
			$updater->addExtensionUpdate(array( 'dropTable', 'loopstructure', $schemaPath . 'loopstructure_delete.sql', true ) );

			self::saveAllWikiPages();
			#self::migrateGlossary();
			#self::migrateLoopTerminology();

		}
		*/
		if ( $updater->tableExists( 'loop_object_index' ) ) { #update for existing LOOPs
			$updater->addExtensionUpdate(array( 'modifyTable', 'loop_object_index', $schemaPath . 'loop_object_index_modify.sql', true ) );
			#self::saveAllWikiPages();
		}


		return true;
	}

	/**
	 * Re-saves every wikipage in main namespace
	 * Rendering will be updated; IDs will be given etc
	 */
	public static function saveAllWikiPages() {

		$hookContainer = MediaWikiServices::getInstance()->getHookContainer();
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
				if ( $tmpFPage->getRevisionRecord() !== null ) {
					$stableRev = $tmpFPage->getRevisionRecord()->getId();
				} else {
					$stableRev = "null";
					error_log("NO REVISION AVAILABLE $row->page_id");
				}
			}
			error_log("Updating page " . $row->page_id . " (rev " . $stableRev .  ")");
			$hookContainer->run( 'LoopUpdateSavePage', array( $title ) );
		}
	}

	/**
	 * Migrates LOOP 1 glossary category and pages to new namespace
	 * Used in LOOP 1 update process only #LOOP1UPGRADE
	 */
	public static function migrateGlossary() {
		/*
        $glossary = Category::newFromName("Glossar");
		$glossaryItems = $glossary->getMembers();

		if ( !empty( $glossaryItems ) ) {
			$user = User::newSystemUser( 'LOOP_SYSTEM', [ 'steal' => true, 'create'=> true, 'validate' => 'valid' ] );
			$user->addGroup("sysop");
			foreach ( $glossaryItems as $title ) {


				$oldWikiPage = MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle ( $title );
				$oldFlaggableWikiPage = new FlaggableWikiPage ( $title );
				$stableRev = $oldFlaggableWikiPage->getStable();
				if ( $stableRev == 0 ) {
					$stableRev = intval( $title->mArticleID );
					$content = $oldWikiPage->getContent ()->getText();
				} else {
					$revision = Revision::newFromId( $stableRev );
					if ( $revision !== null ) {
						$content = $revision->getContent ( SlotRecord::MAIN )->getText();
					} else {
						echo "!!!! ERROR !!!! Page ".$title->mArticleID."  has no revision!\n";
						continue;
					}
				}

				# Fill a new page in NS_GLOSSARY with the same title as before with the old content
				$newGlossaryPage = MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle( Title::newFromText( $title->getText(), NS_GLOSSARY ));
				$newGlossaryPageContent = new WikitextContent( preg_replace( '/(\[\[)(Kategorie){0,1}(Category){0,1}(:Glossar\]\])/i', '', $content ) ); // removes [[Kategorie:Glossar]] and [[Category:Glossar]]
				$newGlossaryPageUpdater = $newGlossaryPage->newPageUpdater( $user );
				$summary = CommentStoreComment::newUnsavedComment( 'LOOP2 glossary migration' );
				$newGlossaryPageUpdater->setContent( "main", $newGlossaryPageContent );
				$newGlossaryPageUpdater->saveRevision ( $summary, EDIT_NEW );

				$lsi = LoopStructureItem::newFromIds( $title->mArticleID );
				if ( !$lsi ) {
					# Redirecting the old page to the new glossary namespace page, if it is not in structure
					$newRedirectContent = new WikitextContent( "#REDIRECT [[" . wfMessage( "loop-glossary-namespace" )->inContentLanguage()->text() . ":" . $title->getText() . "]]" );
					$oldWikiPageUpdater = $oldWikiPage->newPageUpdater( $user );
					$summary = CommentStoreComment::newUnsavedComment( 'LOOP2 glossary migration' );
					$oldWikiPageUpdater->setContent( "main", $newRedirectContent );
					$oldWikiPageUpdater->saveRevision ( $summary, EDIT_UPDATE );
					error_log("Moving and redirecting glossary page " . $title->mArticleID . " (rev " . $stableRev .  ")");
				} else {
					error_log("Copying glossary page " . $title->mArticleID . " (rev " . $stableRev .  ")");
				}

			}
		}

		$glossaryCategoryWikiPage = MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle( Title::newFromText( "Glossar", NS_CATEGORY ));
		$glossaryCategoryWikiPage->doDeleteArticle( 'Moved to Special:Glossary / Spezial:Glossar' );
		*/
	}


	/**
	 * Custom hook called when updating LOOP
	 * - Indexes given LOOP-Objects on page
	 *
	 * @param Title $title
	 */
	public static function onLoopUpdateSavePage( $title ) {

		$userGroupManager = MediaWikiServices::getInstance()->getUserGroupManager();
		$latestRevId = $title->getLatestRevID();
		$wikiPage = MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle($title);
		$fwp = new FlaggableWikiPage ( $title );
		$systemUser = User::newSystemUser( 'LOOP_SYSTEM', [ 'steal' => true, 'create'=> true, 'validate' => 'valid' ] );
		$userGroupManager->addUserToGroup ( $systemUser, "sysop" );

		$wikiPageRev = $wikiPage->getRevisionRecord();
		if ( isset( $wikiPageRev ) ) {
			$wikiPageContent = $wikiPage->getContent( MediaWiki\Revision\RevisionRecord::RAW );
			$wikiPageUpdater = $wikiPage->newPageUpdater( $systemUser );
			$summary = CommentStoreComment::newUnsavedComment( 'LOOP2 Upgrade' );
			$wikiPageUpdater->setContent( "main", $wikiPageContent );
			$wikiPageUpdater->saveRevision ( $summary, EDIT_UPDATE );
		}

		if ( isset( $fwp ) ) {
			$stableRevId = $fwp->getStable();

			if ( $latestRevId == $stableRevId || $stableRevId == null ) { # page is stable or does not have any stable version
				$contentText = null;
			} else {
				$content = $wikiPage->getContent( MediaWiki\Revision\RevisionRecord::RAW );
				$contentText = ContentHandler::getContentText( $content );
			}

			LoopObject::handleObjectItems( $wikiPage, $title, $contentText );
			#self::migrateLiterature( $wikiPage, $title, $contentText, $systemUser );
			#self::migrateLoopZip( $wikiPage, $title, $systemUser, $contentText );
			#self::replaceCommonWikitext( $wikiPage, $title, $contentText, $systemUser );
		}
		return true;
	}

	/**
	 * Migrates common LOOP 1 content that is not compatible to LOOP 2 but easy to fix
	 * - EmbedVideo service "youtubehd" -> "youtube"
	 * - Can be extended
	 * Used in LOOP 1 update process only #LOOP1UPGRADE
	 */
	public static function replaceCommonWikitext ( $wikiPage, $title, $contentText, $systemUser ) {
		/*
		$revision = $wikiPage->getRevisionRecord();
		if ( $contentText == null ) {
			if ( $revision !== null ) {
				$contentText = $revision->getContent( SlotRecord::MAIN )->getText();
			} else {
				echo "!!!! ERROR !!!! Page ".$title->mArticleID."  has no revision!\n";
				return;
			}
		}
		$newContentText = str_replace("#ev:youtubehd", "#ev:youtube", $contentText);

		# LOOP INDEX
		$parserFactory = MediaWikiServices::getInstance()->getParserFactory();
		$parser = $parserFactory->create();
		$index_tags = array ();
		$loopliterature_tags = array ();
		$parser->extractTagsAndParams( array( 'loop_index' ), $newContentText, $index_tags );

		if ( !empty ( $index_tags ) ) {
			foreach ( $index_tags as $index ) {
				#dd($index);
				if ( strpos ( $index[1], "|" ) != false ) {
					$replace = str_replace("|", "</loop_index><loop_index>", $index[3]);
					$newContentText = str_replace( $index[3], $replace, $contentText );
				}
			}
		}

		if ( $newContentText != $contentText ) {
			$editContent = $revision->getContent(SlotRecord::MAIN)->getContentHandler()->unserializeContent( $newContentText );
			$wikiPageUpdater = $wikiPage->newPageUpdater( $systemUser );
			$summary = CommentStoreComment::newUnsavedComment( 'LOOP Upgrade: loop_index and youtubehd' );
			$wikiPageUpdater->setContent( "main", $editContent );
			$wikiPageUpdater->saveRevision ( $summary, EDIT_UPDATE );
		}
		*/
	}

	/**
	 * Migrates LOOP 1 tag biblio into DB
	 * - bibliography page "Literatur" and adds given entries to database
	 * Used in LOOP 1 update process only #LOOP1UPGRADE
	 */
	public static function migrateLiterature( $wikiPage, $title, $contentText, $systemUser ) {
		/*
		$literaturePage = false;
		$bibliographyPageTitle = Title::newFromText( "Bibliographypage", NS_MEDIAWIKI);
		$bibliographyPageWP = MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle( $bibliographyPageTitle );
		$bibliographyPageRev = $bibliographyPageWP->getRevisionRecord();
		if ( $bibliographyPageRev ) {
			$bibliographyPageContentText = $bibliographyPageRev->getContent()->getText();
			if ( $title->getText() == $bibliographyPageContentText ) {
				$literaturePage = true; #this is the bibliography page and will get a redirect
			}
		}
		if ( $contentText == null ) {
			$revision = $wikiPage->getRevisionRecord();
			if ( $revision != null ) {
				$contentText = $revision->getContent(SlotRecord::MAIN)->getText();
			} else {
				return true;
			}
		}
		$parserFactory = MediaWikiServices::getInstance()->getParserFactory();
		$parser = $parserFactory->create();
		$biblio_tags = array ();
		$loopliterature_tags = array ();
		$parser->extractTagsAndParams( array( 'biblio' ), $contentText, $biblio_tags );

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
							$text = str_replace( "'''", "", $text);
							$text = str_replace( "''", "", $text);

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
							$newTagContent .=  "<literature>" . $key . "</literature>\n";

						}
					}
				}
				$loopliterature_tags[] = $newTagContent;
			}
			if ( $literaturePage ) {
				$newRedirectContent = new WikitextContent( "#REDIRECT [[" . wfMessage( "namespace-special" )->inContentLanguage()->text() . ":" . wfMessage( "loopliterature" )->inContentLanguage()->text() . "]]" );
				$wikiPageUpdater = $wikiPage->newPageUpdater( $systemUser );
				$summary = CommentStoreComment::newUnsavedComment( 'Redirect to new bibliography' );
				$wikiPageUpdater->setContent( "main", $newRedirectContent );
				$wikiPageUpdater->saveRevision ( $summary, EDIT_UPDATE );
			}
		}

		return true;
		*/
	}

	/**
	 * Adds scale="true" to loop_zip tags upon update
	 * Used in LOOP 1 update process only #LOOP1UPGRADE
	 */
	public static function migrateLoopZip( $wikiPage, $title, $systemUser, $contentText = null ) {
		/*
		$revision = $wikiPage->getRevisionRecord();

		if ( $contentText == null ) {
			if ( $revision != null ) {
				$contentText = $revision->getContent(SlotRecord::MAIN)->getText();
			} else {
				return true;
			}
		}
		$parserFactory = MediaWikiServices::getInstance()->getParserFactory();
		$parser = $parserFactory->create();
		$zip_tags = array ();
		$loopliterature_tags = array ();
		$parser->extractTagsAndParams( array( 'loop_zip' ), $contentText, $zip_tags );
		$newContentText = $contentText;

		if ( !empty ( $zip_tags ) ) {
			$newContentText = str_replace( "<loop_zip", '<loop_zip scale="true" ', $contentText );

		}
		if ( $newContentText != $contentText ) {
			$editContent = $revision->getContent(SlotRecord::MAIN)->getContentHandler()->unserializeContent( $newContentText );
			$wikiPageUpdater = $wikiPage->newPageUpdater( $systemUser );
			$summary = CommentStoreComment::newUnsavedComment( 'Added scale=true to loop_zip' );
			$wikiPageUpdater->setContent( "main", $editContent );
			$wikiPageUpdater->saveRevision ( $summary, EDIT_UPDATE );
		}
		*/
	}

	/**
	 * Migrate terminology page for Lingo extension
	 */
	public static function migrateLoopTerminology() {
		/*
		$systemUser = User::newSystemUser( 'LOOP_SYSTEM', [ 'steal' => true, 'create'=> true, 'validate' => 'valid' ] );
		$systemUser->addGroup("sysop");

        $terminologyPage = Title::newFromText( "Abkuerzungen" );
		$wikiPage = MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle( $terminologyPage );
		$oldPageContent = $wikiPage->getContent();

		if ( !empty ( $oldPageContent ) ) {
			$newTerminologyPage = Title::newFromText( 'LoopTerminologyPage', NS_MEDIAWIKI );
			$newWikiPage = MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle( $newTerminologyPage );

			# Add old content to new page
			$newWikiPageUpdater = $newWikiPage->newPageUpdater( $systemUser );
			$summary = CommentStoreComment::newUnsavedComment( "Migrated terminology page" );
			$newWikiPageUpdater->setContent( "main", $oldPageContent );
			$newWikiPageUpdater->saveRevision ( $summary, EDIT_UPDATE );

			# Add redirect to old page
			$newRedirectContent = new WikitextContent( "#REDIRECT [[Special:LoopTerminology]]" );
			$wikiPageUpdater = $wikiPage->newPageUpdater( $systemUser );
			$summary = CommentStoreComment::newUnsavedComment( 'Redirect to new terminology page' );
			$wikiPageUpdater->setContent( "main", $newRedirectContent );
			$wikiPageUpdater->saveRevision ( $summary, EDIT_UPDATE );

			error_log("Moving and redirecting terminology page " . $terminologyPage->mArticleID );
		}
		*/
	}

	/**
	 * Migrates LOOP 1 settings from moodalis to LOOP DB
	 * Used in LOOP 1 update process only #LOOP1UPGRADE
	 */
	private static function addOldSettingsToDb() {
		/*
		global $wgLoopAddToSettingsDB;

		$loopSettings = new LoopSettings();
		$loopSettings->loadSettings();

		foreach( $wgLoopAddToSettingsDB as $key => $value ) {
			$loopSettings->$key = $value;
		}

		$loopSettings->addToDatabase();
		*/
	}
}


class SpecialLoopMediaWikiUpdater extends UnlistedSpecialPage {

	function __construct() {
		parent::__construct( 'SpecialLoopMediaWikiUpdater' );
	}

	function execute( $par ) {

		$user = $this->getUser();
		$userGroupManager = MediaWikiServices::getInstance()->getUserGroupManager();

		$out = $this->getOutput();
		$request = $this->getRequest();
		$out->setPageTitle( "MediaWiki Updater" );
		$this->setHeaders();
		$html = '';

		if ( in_array( "sysop", $userGroupManager->getUserGroups( $user ) ) ) {

			$html .= '<h3>MediaWiki Updater</h3>';
			$html .= '<div class="form-row">';
			$html .= '<div class="col-12">';
			$html .= "<p>". $this->msg( 'loopupdater-description-update' ) ."</p>";
			$html .= '<form class="mw-editform mt-3" id="loopupdate-form" method="post" novalidate enctype="multipart/form-data">';

			$html .= '<div><input type="checkbox" name="updatemw" id="updatemw" class="mr-1">';
			$html .= '<label for="updatemw">'.$this->msg( 'loopupdater-warning-update' ).'</label></div>';

			$html .= '<input type="hidden" name="execute" id="execute" value="1"></input>';
			$html .= '<input type="submit" class="mw-htmlform-submit mw-ui-button mw-ui-primary mw-ui-progressive mt-2 d-block" id="submit" value="' . $this->msg( 'loopupdater-submit-update' ) . '"></input>';

			$html .= '</div>';
			$html .= '</div>';
			$html .= '</form>';

			if ( !empty ( $request->getText( 'execute' ) ) && !empty ( $request->getText( 'updatemw' ) ) ) {
				global $IP, $wgServerName;
				$loop = $wgServerName;
				$updcmd = "$IP/maintenance/update.php --quick --wiki $loop 2>&1";
				$updres = shell_exec("php $updcmd");
				$html .= "Update Log:<br>" . str_replace( "\n", "<br>" , $updres);
			}

		} else {
			$html = '<div class="alert alert-warning" role="alert">' . $this->msg( 'specialpage-no-permission' ) . '</div>';
		}
		$out->addHTML( $html );
	}
}

?>
