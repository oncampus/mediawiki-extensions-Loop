<?php

class LoopArea {

	static function onParserInit( Parser $parser ) {
		//global $wgOut;
		$parser->setHook( 'loop_area', 'LoopArea::renderLoopArea' ); 
		//$wgOut->addModules( 'ext.looparea' );
		return true;
	}
	
	static function renderLoopArea( $input, array $args, Parser $parser, PPFrame $frame ) {
		
		//default
		$argtype = 'area';
		$iconimg = 'example';
		
		$allowed_attr = ['type', 'icontext', 'icon']; // allowed attributes for loop_area

		$arraydiff = array_diff(array_keys($args), $allowed_attr);

		// todo: check if no attribute is defined


		if($arraydiff) {
			//$e = new LoopException( wfMessage( 'looparea-error-unknown-attribute' ));
			//$this->getParser()->addTrackingCategory( 'loop-tracking-category-error' );
			//$this->error = $e;
			print_r("error"); //temp
		}



		if( !empty( $args['type'] ) ) {
			$argtype = $args['type'];
			$iconimg = $argtype;
		}

		// catch special cases
		if ( $argtype === 'websource' ) { // because icon with this name does not exist, using 'link-external' icon
			$iconimg = 'link-external';
		} elseif ( $argtype === 'indentation' ) { // because icon with this name does not exist, using 'watch' icon
			$iconimg = 'watch';
		}

		// Determine how the box renders, 'rendermarked' is default
		$cssrender = 'rendermarked';

		if( array_key_exists( 'render', $args ) ) { // array_key_exists() because code convention forbids isset()
			if ( $args['render'] === 'none' ) {
				$cssrender = 'renderhide';
			} elseif ( $args['render'] === 'icon') {
				$cssrender = 'rendericon';
			} elseif ( $args['render'] === 'marked') {
				$cssrender = 'rendermarked';
			}
		}

		$icontext = wfMessage( 'looparea-icon-text-'.$argtype );

		// Allow overwriting icon text when parameter 'icontext' is used
		if( array_key_exists( 'icontext', $args ) ) { // array_key_exists() because code convention forbids isset()
			$icontext = $args['icontext'];
		}
		
		// Allow overwriting icon image when parameter 'icon' is used
		$cssicon = 'ic ic-' . $iconimg;
		$ownicon = '';
		if( array_key_exists( 'icon', $args ) ) { // array_key_exists() because code convention forbids isset()
			$cssicon = 'ownicon';
			$owniconurl = wfLocalFile( $args['icon'] )->getCanonicalURL();
			$ownicon = 'style="background-image: url(' . $owniconurl . ')"'; 
		}

		$ret = '<div class="looparea ' . $cssrender . ' looparea-'. $iconimg .'">';
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
