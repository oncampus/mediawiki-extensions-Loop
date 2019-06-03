<?php

class LoopArea {

	static function onParserInit( Parser $parser ) {
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
			$argtype = 'area';
			$iconimg = $argtype;
		}

		// Determine how the box renders, 'rendermarked' is default
		$cssrender = 'rendermarked';

		try {
			if( array_key_exists( 'render', $args ) ) { // array_key_exists() because code convention forbids isset()
				if ( $args['render'] === 'none' ) {
					$cssrender = 'renderhide';
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
					$owniconurl = wfLocalFile( $args['icon'] )->getCanonicalURL();
					$cssicon = 'ownicon';
					$ownicon = 'style="background-image: url(' . $owniconurl . ')"'; 
				} else {
					throw new LoopException( wfMessage( 'looparea-error-imagenotfound', $args['icon'] )->text() );
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
		$ret .= '<div class="looparea ' . $cssrender . ' looparea-'. $iconimg .'">';
		$ret .= '<div class="looparea-container mb-3">';
		$ret .= '<div class="looparea-left pt-3 px-1">';
		$ret .= '<span class="' . $cssicon . '" ' . $ownicon . '></span>';
		$ret .= '<span class="looparea-left-type">' . $icontext . '</span>';
		$ret .= '</div>';
		$ret .= '<div class="looparea-right p-3">' . $parser->recursiveTagParseFully( $input ) . '</div>';
		$ret .= '</div>';
		$ret .= '</div>';
		return $ret;
	}

}
