<?php
/**
 * @description Adds support for <loop_area> Tags
 * @ingroup Extensions
 * @author Dustin Neß @krohnden <dustin.ness@th-luebeck.de>
 */
if ( !defined( 'MEDIAWIKI' ) ) {
    die( "This file cannot be run standalone.\n" );
}

class LoopArea {

	static function onParserSetup( Parser $parser ) {
		$parser->setHook( 'loop_area', 'LoopArea::renderLoopArea' ); 
		return true;
	}
	
	public static $typeOptions = [
		'learningobjectives',
		'markedsentence',
		'timerequirement',
		'indentation',
		'arrangement',
		'definition',
		'annotation',
		'experiment',
		'reflection',
		'sourcecode',
		'important',
		'websource',
		'exercise',
		'citation',
		'practice',
		'question',
		'practice',
		'question',
		'summary',
		'example',
		'formula',
		'notice',
		'norm',
		'task',
		'area',
		'law'
	];

	public static $renderOptions = [
		'marked',
		'icon',
		'none'
	];

	static function renderLoopArea( $input, array $args, Parser $parser, PPFrame $frame ) {
		try {
			// if type attribute exists ...
			if( isset( $args['type'] ) ) {
				
				// ... check if attribute contains allowed type
				if( in_array( $args['type'], self::$typeOptions ) ) {
					$argtype = $args['type'];
					$iconimg = $argtype;

					// catch special cases
					if ( $argtype === 'websource' ) { // because icon with this name does not exist, using 'link-external' icon
						$iconimg = 'link-external';
					} elseif ( $argtype === 'indentation' ) { // because icon with this name does not exist, using 'watch' icon
						$iconimg = 'watch';
					}
				} else {	
					$argtype = 'area';
					$iconimg = $argtype;
					throw new LoopException( wfMessage( 'looparea-error-unknown-type-attribute', $args['type'], implode( ', ', self::$typeOptions ) )->text() );
				}
			} else {
				// ... set default type
				$argtype = 'area';
				$iconimg = $argtype;
			}
		} catch ( LoopException $e) {
			$parser->addTrackingCategory( 'loop-tracking-category-error' );
			$error = $e;
		}

		// Determine how the box renders, 'rendermarked' is default
		$cssrender = 'rendermarked';

		try {
			if( array_key_exists( 'render', $args ) ) { // array_key_exists() because code convention forbids isset()
				if ( $args['render'] === 'none' ) {
					$cssrender = 'renderhide d-none';
				} elseif ( $args['render'] === 'icon' ) {
					$cssrender = 'rendericon';
				} elseif ( $args['render'] === 'marked') {
					$cssrender = 'rendermarked';
				} else {
					throw new LoopException( wfMessage( 'looparea-error-unknown-render-attribute', $args['render'], implode( ', ', self::$renderOptions ) )->text() );
				}
			}
			
		} catch ( LoopException $e) {
			$parser->addTrackingCategory( 'loop-tracking-category-error' );
			$error = $e;
		}

		$icontext = wfMessage( 'looparea-icon-text-' . $argtype )->text();

		// Allow overwriting icon text when parameter 'icontext' is used
		if( array_key_exists( 'icontext', $args ) ) { // array_key_exists() because code convention forbids isset()
			$icontext = strtolower( $args['icontext'] );
		}
				
		$cssicon = 'ic ic-' . $iconimg;
		$ownicon = '';
		
		// Allow overwriting icon image when parameter 'icon' is used
		if( array_key_exists( 'icon', $args ) ) { // array_key_exists() because code convention forbids isset()

			try {
				if( file_exists( wfLocalFile( $args['icon'] )->getLocalRefPath() ) ) {
					
				    global $wgOut, $wgDefaultUserOptions, $wgUploadDirectory;
					$user = $wgOut->getUser();
					$renderMode = $user->getOption( 'LoopRenderMode', $wgDefaultUserOptions['LoopRenderMode'], true );
					
					if ( $renderMode == "offline" ) {
					    $loopHtml = new LoopHtml();
					    $fileData = array();
					    preg_match('/(.*)(\.{1})(.*)/', $args['icon'], $fileData);
					    $fileName = $loopHtml->resolveUrl($fileData[1], '.'.$fileData[3]);
					    $owniconurl = "resources/images/".$fileName;
					    $fileUrl = wfLocalFile( $args['icon'] )->getCanonicalURL();;
					    $fileContent = $loopHtml->requestContent(array($fileUrl));
					    #dd($fileUrl, $fileName);
					    $loopHtml->writeFile( $wgUploadDirectory . '/export/html/0/files/resources/images/', $fileName, $fileContent );
					    
					} else {
					    $owniconurl = wfLocalFile( $args['icon'] )->getCanonicalURL();
					}
					$cssicon = 'ownicon d-block';
					$ownicon = 'style="background-image: url(' . $owniconurl . ')"'; 
					#$owniconurl = LoopHtml::getInstance()->resolveUrl($title->mUrlform, '.html');;
					#dd( , $renderMode);
				} else {
					throw new LoopException( wfMessage( "loop-error-missingfile", "loop_area", $args['icon'], 0 )->text() );
				}
			} catch( Exception $e) {
				$parser->addTrackingCategory( 'loop-tracking-category-error' );
				$error = $e;
			}
		}

		$ret = '';
		if ( isset( $error ) ) {
			$ret .= $error;
		} 
		$ret .= '<div class="looparea position-relative ' . $cssrender . ' looparea-'. $iconimg .'">';
		$ret .= '<div class="looparea-container mb-2 d-block d-lg-flex">';
		$ret .= '<div class="looparea-left position-relative pl-1 pr-1 pt-2 pt-lg-0">';
		$ret .= '<span class="' . $cssicon . '" ' . $ownicon . '></span>';
		$ret .= '<span class="looparea-left-type d-block font-weight-bold">' . $icontext . '</span>';
		$ret .= '</div>';
		$ret .= '<div class="looparea-right pl-3 pr-3 pt-1 pt-md-2 pb-1 pb-lg-0">' . $parser->recursiveTagParseFully( $input ) . '</div>';
		$ret .= '</div>';
		$ret .= '</div>';
		return $ret;
	}

}
