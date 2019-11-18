<?php 

class LoopScreenshot {
	
	public static function onParserSetup( Parser $parser ) {
		$parser->setHook( 'loop_screenshot', 'LoopScreenshot::renderLoopScreenshot' );
		return true;
	}	
	
	public static function onParserAfterTidy( &$parser, &$text ) {

		$matches = array();
		preg_match_all( '/(<div class="loop_screenshot_begin" id=")(\w*)(" data-width=")(\d*)(" data-height=")(\d*)(">)(.*)(<\/div><div class="loop_screenshot_end"><\/div>)/iUs', $text, $matches );
		$articleId = $parser->getTitle()->getArticleID();
		#dd($matches, $text);

		if ( $matches ) {
			$c = count( $matches[0] ); 
			
			for ( $i = 0; $i < $c; $i++ ) {
				$png = LoopScreenshot::html2png( $matches[8][$i], $matches[2][$i], $articleId, $matches[4][$i], $matches[6][$i] );
			}
			
		}

		return true;
	}	
	
	public static function renderLoopScreenshot( $input, array $args, $parser, $frame ) {
		
		if ( array_key_exists( "width", $args ) ) {
			$width = intval($args["width"]);
			if ( $width < 600 ) {
				$width = 600;
			}
		} else {
			$width = 700;
		}
		if ( array_key_exists( "height", $args ) ) {
			$height = intval( $args["height"]);
		} else {
			$height = 600;
		}
		$refId = $args["id"];

		$user = $parser->getUser();
		$articleId = $parser->getTitle()->getArticleID();
		$loopeditmode = $user->getOption( 'LoopEditMode', false, true );	
		$return = '';

		$return .= '<div class="loop_screenshot_begin" id="'.$args['id'].'" data-width="'.$width.'" data-height="'.$height.'">';
		$return .= $parser->recursiveTagParseFully( $input );
		$return .= '</div><div class="loop_screenshot_end"></div>';		

		if ( $loopeditmode ) {
			global $wgCanonicalServer;
			$parser->getOutput()->addModules( 'loop.spoiler.js' );
			$tmpId = uniqid();
			$return .= '<div class="loopspoiler-container">';
			$return .= '<span class="btn loopspoiler loopspoiler_type_in_text '. $tmpId .'">' . "Screenshot" . '</span>';
			$return .= '<div id="'. $tmpId . '" class="loopspoiler_content_wrapper loopspoiler_type_in_text">';
			$return .= '<div class="loopspoiler_content">';
			$screenshotUrl = $wgCanonicalServer . "/mediawiki/images/screenshots/$articleId/$refId.png";
			$return .= '<img class="responsive-image" src="'.$screenshotUrl.'"/>';
			$return .= "\n</div></div></div>";
		}
		return $return;

	}
		
	public static function html2png ( $content, $id, $articleId, $width, $height ) {

		global $wgScreenshotUrl, $wgUploadDirectory, $wgCanonicalServer, $wgLanguageCode;
		#dd($content);
		if ( !empty ( $wgScreenshotUrl ) ) {

			$html = '<!DOCTYPE html>';
			$html .= '<html>';
			$html .= '<head>';
			$html .= '<meta charset="UTF-8" />';
			$html .= '<link rel="stylesheet" href="'.$wgCanonicalServer.'/mediawiki/resources/src/mediawiki.legacy/shared.css">';
			$html .= '<link rel="stylesheet" href="' . $wgCanonicalServer . "/mediawiki/load.php?lang=". $wgLanguageCode ."&amp;modules=skins.loop-bootstrap%2Cloop-common%2Cloop-icons%2Cloop-plyr&amp;only=styles&amp;skin=loop" . '">';
			$html .= '<style>.screenshotviewport{
				transform: scale(4);
				transform-origin: 0 0;}</style>';
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
			$canonicalHtmlUrl = $wgCanonicalServer."/mediawiki/images/screenshots/".$articleId."/".$id.'.html';
			$screenshotPngFile = $screenshotPageDir.'/'.$id.'.png';
			#dd($wgCanonicalServer."/mediawiki/images/screenshot/".$articleId."/".$id.'.html');
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
			#dd($image_content);
		}

		return true;
	}
}

?>