<?php 

/**
 * @description Screenshot tag. Content will have a screenshot taken to be displayed in PDF instead of the otherwise regularily rendered content
 * @author Dennis Krohn @krohnden <dennis.krohn@th-luebeck.de>
 */

if ( !defined( 'MEDIAWIKI' ) ) {
    die( "This file cannot be run standalone.\n" );
}

class LoopScreenshot {
	
	public static function onParserSetup( Parser $parser ) {
		$parser->setHook( 'loop_screenshot', 'LoopScreenshot::renderLoopScreenshot' );
		return true;
	}	
	
	public static function onParserAfterTidy( &$parser, &$text ) {

		$matches = array();
		preg_match_all( '/(<div class="loop_screenshot_begin" id=")(\w*)(" data-width=")(\d*)(" data-height=")(\d*)(">)(.*)(<\/div><div class="loop_screenshot_end"><\/div>)/iUs', $text, $matches );
		$title = $parser->getTitle();
		$articleId = $title->getArticleID();

		if ( $matches ) {
			$c = count( $matches[0] ); 
			for ( $i = 0; $i < $c; $i++ ) {
				$png = LoopScreenshot::html2png( $title, $matches[8][$i], $matches[2][$i], $articleId, $matches[4][$i], $matches[6][$i] );
			}
		}

		return true;
	}	
	
	public static function renderLoopScreenshot( $input, array $args, $parser, $frame ) {
		
		$title = $parser->getTitle();
		$fwp = new FlaggableWikiPage ( $title );
		$stableRevId = $fwp->getStable();

		if ( array_key_exists( "width", $args ) ) {
			$width = intval($args["width"]);
			$orig_width = $width;
			if ( $width < 600 ) {
				$width = 600;
			}
		} else {
			$width = 700;
			$orig_width = $width;
		}
		if ( array_key_exists( "height", $args ) ) {
			$height = intval( $args["height"]);
		} else {
			$height = 500;
		}
		$refId = $stableRevId ."_". $args["id"];
		$return = '';

		$user = $parser->getUser();
		$articleId = $parser->getTitle()->getArticleID();
		$loopeditmode = $user->getOption( 'LoopEditMode', false, true );	

		$return .= '<div class="loop_screenshot_begin" id="'.$refId.'" data-width="'.$width.'" data-height="'.$height.'">';
		$return .= $parser->recursiveTagParseFully( $input );
		$return .= '</div><div class="loop_screenshot_end"></div>';		

		if ( $loopeditmode ) {
			global $wgCanonicalServer, $wgUploadPath;
			$parser->getOutput()->addModules( 'loop.spoiler.js' );
			$tmpId = uniqid();
			$return .= '<div class="loopspoiler-container">';
			$return .= '<span class="btn loopspoiler loopspoiler_type_in_text '. $tmpId .'">' . wfMessage( "loopscreenshot" )->text() . '</span>';
			$return .= '<div id="'. $tmpId . '" class="loopspoiler_content_wrapper loopspoiler_type_in_text">';
			$return .= '<div class="loopspoiler_content">';
			$screenshotUrl = $wgCanonicalServer . $wgUploadPath."/screenshots/$articleId/$refId.png";
			#$return .= wfMessage( "loopscreenshot-hint", $orig_width, $height )->text() . "<br/><hr/>";
			$return .= '<img class="responsive-image" src="'.$screenshotUrl.'"/>';
			$return .= "\n</div></div></div>";
		}
		
		
		return $return;

	}
		
	public static function html2png ( $title, $content, $id, $articleId, $width, $height ) {

		global $wgScreenshotUrl, $wgUploadDirectory, $wgCanonicalServer, $wgLanguageCode, $wgUploadPath, $wgScriptPath, $wgDefaultUserOptions;

		if ( !empty ( $wgScreenshotUrl ) ) {

			$wikiPage = WikiPage::factory( $title );
			$fwp = new FlaggableWikiPage ( $title );
			
			$rev = $wikiPage->getRevision();
			$revId = $rev->getId();
			$stableRevId = $fwp->getStable();
			
			if ( isset( $fwp ) ) {
				
				$html = '<!DOCTYPE html>';
				$html .= '<html>';
				$html .= '<head>';
				$html .= '<meta charset="UTF-8" />';
				$html .= '<link rel="stylesheet" href="' . $wgCanonicalServer . $wgScriptPath . '/resources/src/mediawiki.legacy/shared.css">';
				$html .= '<link rel="stylesheet" href="' . $wgCanonicalServer . $wgScriptPath . "/load.php?lang=" . $wgLanguageCode . "&amp;modules=skins.loop-bootstrap%2C".$wgDefaultUserOptions["LoopSkinStyle"]."%2Cloop-icons%2Cloop-plyr&amp;only=styles&amp;skin=loop" . '">';
				$html .= '<style>.screenshotviewport{ transform: scale(4); transform-origin: 0 0;}</style>';
				$html .= '</head>';
				$html .= '<body><div class="screenshotviewport">' . $content . '</div></body></html>';

				$screenshotDir = $wgUploadDirectory.'/screenshots';
				if ( !is_dir( $screenshotDir ) ) {
					@mkdir( $screenshotDir, 0774, true );
				}
				$screenshotPageDir = $screenshotDir.'/'.$articleId;
				if ( !is_dir( $screenshotPageDir ) ) {
					@mkdir( $screenshotPageDir, 0774, true );
				}
				
				$screenshotHtmlFile = $screenshotPageDir.'/'.$id.'.html';
				$canonicalHtmlUrl = $wgCanonicalServer.$wgUploadPath.'/screenshots/'.$articleId."/".$id.'.html';
				$screenshotPngFile = $screenshotPageDir.'/'.$id.'.png';
				
				if ( !file_exists( $screenshotPngFile ) ) {

					$fh = fopen( $screenshotHtmlFile, 'w+' );
					fwrite ( $fh, $html );
					fclose( $fh );
					chmod( $screenshotHtmlFile, 0774);
					$ch = curl_init();
					curl_setopt ( $ch, CURLOPT_POST, true );
					curl_setopt ( $ch, CURLOPT_URL, $wgScreenshotUrl );
					curl_setopt ( $ch, CURLOPT_POSTFIELDS, "url=".$canonicalHtmlUrl."&width=".$width."&height=".$height );
					curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
					$imageContent = curl_exec( $ch );
					curl_close( $ch );

					if ( !empty( $imageContent ) ) {
						$fh = fopen( $screenshotPngFile, 'w+' );
						fwrite ( $fh, $imageContent );
						fclose( $fh );
						chmod( $screenshotPngFile, 0774);
					}
					if ( file_exists( $screenshotHtmlFile ) ) {
						unlink( $screenshotHtmlFile );
					}
				}
			}
		}

		return true;
	}

	public static function onPageContentSaveComplete( $wikiPage, $user, $mainContent, $summaryText, $isMinor, $isWatch, $section, &$flags, $revision, $status, $originalRevId, $undidRevId ) {

		global $wgUploadDirectory;
		$title = $wikiPage->getTitle();
		$articleId = $title->getArticleID();

		$fwp = new FlaggableWikiPage ( $title );
		$rev = $wikiPage->getRevision();
		$revId = $rev->getId();
		$stableRevId = $fwp->getStable();
		
		if ( isset( $fwp ) && $revId == $stableRevId ) {
			$screenshotPath = $wgUploadDirectory . "/screenshots/$articleId/";
			SpecialPurgeCache::deleteAll($screenshotPath); # delete all images of a page before saving new ones
		}

		return true;
	}
}