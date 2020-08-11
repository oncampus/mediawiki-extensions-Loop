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
	
	public static function onParserAfterTidy( &$parser, &$text ) { # for rendering when the screenshot is not present upon calling from PDF

		$title = $parser->getTitle();
		self::checkForScreenshots( $text, $title );
		return true;

	}	
	
	public static function onBeforePageDisplay( $out, $skin ) { # for rendering when the screenshot is not present upon opening the page

		$title = $out->getTitle();
		$text = $out->mBodytext;
		self::checkForScreenshots( $text, $title );
		return true;
		
	}	

	public static function checkForScreenshots( $text, $title ) {

		$matches = array();
		preg_match_all( '/(<div class="loop_screenshot_begin" id=")(\w*)(" data-width=")(\d*)(" data-height=")(\d*)(">)(.*)(<\/div><div class="loop_screenshot_end"><\/div>)/iUs', $text, $matches );
		$articleId = $title->getArticleID();

		if ( !empty ( $matches[0] ) ) {
			$c = count( $matches[0] ); 
			for ( $i = 0; $i < $c; $i++ ) {
				$png = LoopScreenshot::html2png( $title, $matches[8][$i], $matches[2][$i], $articleId, $matches[4][$i], $matches[6][$i] );
			}
		}

		return true;
	}
	
	public static function renderLoopScreenshot( $input, array $args, $parser, $frame ) {
		
		global $wgLoopScreenshotUrl;
		$title = $parser->getTitle();
		$fwp = new FlaggableWikiPage ( $title );
		$stableRevId = $fwp->getStable();
		
		if ( !empty ( $wgLoopScreenshotUrl ) ) {

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
			$html = '';

			$user = $parser->getUser();
			$articleId = $parser->getTitle()->getArticleID();
			$loopeditmode = $user->getOption( 'LoopEditMode', false, true );	

			$html .= '<div class="loop_screenshot_begin" id="'.$refId.'" data-width="'.$width.'" data-height="'.$height.'">';
			$html .= $parser->recursiveTagParseFully( $input );
			$html .= '</div><div class="loop_screenshot_end"></div>';		

			if ( $loopeditmode ) {
				global $wgCanonicalServer, $wgUploadPath;

				$screenshotUrl = $wgCanonicalServer . $wgUploadPath."/screenshots/$articleId/$refId.png";
				
				$btnId = uniqid();
				$btnIcon = '<span class="ic ic-print-area float-none"></span>';
				$editModeClass = $loopeditmode ? " loopeditmode-hint" : "";
				
				$html .= '<div class="loopprint-container loopprint-button">';
				$html .= '<input id="'. $btnId .'" type="checkbox">';
				$html .= '<label for="'. $btnId .'" class="mb-0"><span data-title="'.wfMessage('loopscreenshot')->text().'" class="loopprint-tag '. $btnId;
				$html .= ( $loopeditmode ) ? ' loopeditmode-hint" data-original-title="'.wfMessage('loop-editmode-hint')->text().'"' : '"';
				$html .= '>' . $btnIcon . '<span class="loopprint-button-text pl-1">'.wfMessage('loopscreenshot')->text().'</span></span></label>';
				$html .= '<div class="loopprint-content pb-1"><img class="responsive-image" src="'.$screenshotUrl.'"/></div>';
				$html .= '</div>';	
			}
		} else {
			$html = new LoopException( wfMessage( 'loopscreenshot-error-noservice', $input )->text() );
			$parser->addTrackingCategory( 'loop-tracking-category-error' );
		}
		
		return $html;

	}
		
	public static function html2png ( $title, $content, $id, $articleId, $width, $height ) {

		global $wgLoopScreenshotUrl, $wgUploadDirectory, $wgCanonicalServer, $wgLanguageCode, $wgUploadPath, $wgScriptPath, $wgDefaultUserOptions;

		if ( !empty ( $wgLoopScreenshotUrl ) ) {

			$screenshotDir = $wgUploadDirectory.'/screenshots';
			$screenshotPageDir = $screenshotDir.'/'.$articleId;
			$screenshotPngFile = $screenshotPageDir.'/'.$id.'.png';

			if ( !file_exists( $screenshotPngFile ) ) {

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

					if ( !is_dir( $screenshotDir ) ) {
						@mkdir( $screenshotDir, 0774, true );
					}
					if ( !is_dir( $screenshotPageDir ) ) {
						@mkdir( $screenshotPageDir, 0774, true );
					}
					
					$screenshotHtmlFile = $screenshotPageDir.'/'.$id.'.html';
					$canonicalHtmlUrl = $wgCanonicalServer.$wgUploadPath.'/screenshots/'.$articleId."/".$id.'.html';
					
					if ( !file_exists( $screenshotPngFile ) ) {

						$fh = fopen( $screenshotHtmlFile, 'w+' );
						fwrite ( $fh, $html );
						fclose( $fh );
						chmod( $screenshotHtmlFile, 0774);
						$ch = curl_init();
						curl_setopt ( $ch, CURLOPT_POST, true );
						curl_setopt ( $ch, CURLOPT_URL, $wgLoopScreenshotUrl );
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
		} 

		return true;
	}

}