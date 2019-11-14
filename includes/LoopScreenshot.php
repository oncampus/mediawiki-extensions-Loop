<?php 

class LoopScreenshot {
	
	public static function onParserSetup( Parser $parser ) {
		$parser->setHook( 'loop_screenshot', 'LoopScreenshot::renderLoopScreenshot' );
		return true;
	}	
	
	public static function onParserAfterTidy( &$parser, &$text ) {

		$matches = array();
		preg_match_all( '/(<div class="loop_screenshot_begin" id=")(\w*)(">)(.*)(<\/div><div class="loop_screenshot_end"><\/div>)/ius', $text, $matches );

		if ( $matches) {
			$c = count( $matches[0] ); 
			
			for ( $i = 0; $i < $c; $i++ ) {
				$png = LoopScreenshot::html2png( $matches[4][$i], $matches[2][$i] );
			}
			
		}

		return true;
	}	
	
	public static function renderLoopScreenshot( $input, array $args, $parser, $frame ) {
		
		$return = '<div class="loop_screenshot_begin" id="'.$args['id'].'">';
		$return .= $parser->recursiveTagParseFully( $input );
		$return .= '</div><div class="loop_screenshot_end"></div>';		
		
		return $return;

	}
		
	public static function html2png ( $content, $png ) {
		global $wgScreenshotUrl, $wgUploadDirectory, $wgCanonicalServer;
		
		$html = '<!DOCTYPE html>';
		$html .= '<html>';
		$html .= '<head>';
		$html .= '<meta charset="UTF-8" />';
		$html .= '<link rel="stylesheet" href="' . $wgCanonicalServer . '/mediawiki/extensions/Math/modules/ext.math.css">';
		$html .= '<link rel="stylesheet" href="' . $wgCanonicalServer . '/mediawiki/extensions/Math/modules/ext.math.desktop.css">';
		$html .= '<link rel="stylesheet" href="' . $wgCanonicalServer . '/mediawiki/resources/src/mediawiki.legacy/shared.css">';
		$html .= '<script src="' . $wgCanonicalServer . '/mediawiki/extensions/Math/modules/ext.math.js"></script>';			
		$html .= '</head>';
		$html .= '<body>' . $content . '</body></html>';

		$screenshot_dir = $wgUploadDirectory.'/screenshots';
		if (!is_dir( $screenshot_dir) ) {
			@mkdir( $screenshot_dir, 0775, true);
		}
		
		$screenshot_png_file = $screenshot_dir.'/'.$png.'.png';
		
			$ch = curl_init();
			curl_setopt ( $ch, CURLOPT_POST, true );
            curl_setopt ( $ch, CURLOPT_URL, ( $wgScreenshotUrl ) );
			#curl_setopt ( $ch, CURLOPT_HTTPHEADER, array( 'Content-Type: text/html' ) );
			curl_setopt ( $ch, CURLOPT_POSTFIELDS, "$html" );
			curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
			$image_content = curl_exec( $ch );
			curl_close( $ch );
            dd( $image_content );
			$fh = fopen( $screenshot_png_file, 'w+' );
			fwrite ( $fh, $image_content );
			fclose( $fh );
		
		return true;
	}
}

?>