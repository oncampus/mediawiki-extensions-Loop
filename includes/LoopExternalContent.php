<?php
/**
 * @description Renders External contents from for example H5P, LearningApp, ...
 * @ingroup Extensions
 * @author Dennis Krohn @krohnden <dennis.krohn@th-luebeck.de>
 */

if ( !defined( 'MEDIAWIKI' ) ) {
    die( "This file cannot be run standalone.\n" );
}

use MediaWiki\MediaWikiServices;

class LoopExternalContent {

	/**
	 * Register the tags hook
	 * @param Parser $parser
	 * @return boolean
	 */
	public static function onParserSetup( Parser $parser ) {
		$parser->setHook ( 'h5p', 'LoopExternalContent::renderH5P' );
		return true;
    }	
    
    public static function renderH5P ( $input, array $args, Parser $parser, PPFrame $frame ) { 

        global $wgH5PHostUrl;
        $errors = '';
        $return = '';
        $id = array_key_exists( 'id', $args ) ? $args['id'] : '';
        $width = array_key_exists( 'width', $args ) ? $args['width'] : '700';
        $height = array_key_exists( 'height', $args ) ? $args['height'] : '450';
        $hostUrl = $wgH5PHostUrl;

        if ( array_key_exists( 'host', $args ) ) {
            if ( strtolower( $args['host'] ) == "oncampus" || strtolower( $args['host'] ) == "custom" ) {
                global $wgH5PCustomHostUrl;
                if ( !empty ( $wgH5PCustomHostUrl ) ) {
                    $hostUrl = $wgH5PCustomHostUrl;
                } else {
                    $errors .= wfMessage('loopexternalcontent-h5p-error-nocustomhost')->text() . "<br>";
                }
            } elseif ( strtolower( $args['host'] ) != "h5p" ) {
                $errors .= wfMessage('loopexternalcontent-h5p-error-unknownhost')->text() . "<br>";
            }
        }

        if ( !empty( $id ) ) {
            $return = Html::rawElement(
                'iframe',
                array(
                    'src' => $hostUrl . $id,
                    'width' => $width,
                    'height' => $height,
                    'frameborder' => '0',
                    'allowfullscreen' => 'allowfullscreen',
                    'class' => 'responsive-iframe' # TODO RESPONSIVE
                ),
                ''
            );
        } else {
            $errors .= wfMessage('loopexternalcontent-h5p-error-noid')->text();
        }
        if ( !empty ( $errors ) ) {
            $return .= new LoopException( $errors );
            $parser->addTrackingCategory( 'loop-tracking-category-error' );
			
        }

        return $return;
    }

    

}