<?php

class LoopArea {

	static function onParserInit( Parser $parser ) {
		//global $wgOut;
		$parser->setHook( 'loop_area', 'LoopArea::LoopAreaRender' ); 
		//$wgOut->addModules( 'ext.looparea' );
		return true;
	}
	
	static function LoopAreaRender( $input, array $args, Parser $parser, PPFrame $frame ) {
		
		//default
		$argtype = 'area';
		$iconimg = 'example';
		
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
			$owniconurl = 'http://localhost/index.php/Special:FilePath/' . $args['icon']; // Wegen XAMPP erstmal absol. URL
			$ownicon = 'style="background-image: url(' . $owniconurl . ')"'; 
		}

		$ret = '<div class="looparea ' . $cssrender . '">';
		$ret .= '<div class="looparea-container">';
		$ret .= '<div class="looparea-left">';
		$ret .= '<span class="' . $cssicon . '" ' . $ownicon . '></span>';
		$ret .= '<span class="looparea-left-type">' . $icontext . '</span>';
		$ret .= '</div>';
		$ret .= '<div class="looparea-right">' . $parser->recursiveTagParseFully($input) . '</div>';
		$ret .= '</div>';
		$ret .= '</div>';
		return $ret;
	}

}
