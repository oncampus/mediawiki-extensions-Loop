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
		$parser->setHook ( 'learningapp', 'LoopExternalContent::renderLearningApp' );
		$parser->setHook ( 'padlet', 'LoopExternalContent::renderPadlet' );
		$parser->setHook ( 'prezi', 'LoopExternalContent::renderPrezi' );
		$parser->setHook ( 'slideshare', 'LoopExternalContent::renderSlideshare' );
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
                    'allowfullscreen' => 'allowfullscreen',
                    'class' => 'ext-h5p responsive-iframe'
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

    
    public static function renderLearningApp ( $input, array $args, Parser $parser, PPFrame $frame ) { 

        global $wgLearningAppUrl;
        $errors = '';
        $return = '';
        $appId = '';
        $width = array_key_exists( 'width', $args ) ? $args['width'] : '700';
        $height = array_key_exists( 'height', $args ) ? $args['height'] : '500';
        $hostUrl = $wgLearningAppUrl;

        if ( array_key_exists( 'app', $args ) ) {
            $appId = "app=" . $args["app"];
        } elseif ( array_key_exists( 'privateapp', $args ) ) {
            $appId = "v=" . $args["privateapp"];
        } else {
            $errors .= wfMessage('loopexternalcontent-learningapp-error-noid')->text() . "<br>";
        }

        if ( !empty( $appId ) ) {
            $return = Html::rawElement(
                'iframe',
                array(
                    'src' => $hostUrl . $appId,
                    'width' => $width,
                    'height' => $height,
                    'allowfullscreen' => 'allowfullscreen',
                    'class' => 'ext-learningapp responsive-iframe'
                ),
                ''
            );
        } 
        
        if ( !empty ( $errors ) ) {
            $return .= new LoopException( $errors );
            $parser->addTrackingCategory( 'loop-tracking-category-error' );
			
        }

        return $return;
    }

    public static function renderPadlet ( $input, array $args, Parser $parser, PPFrame $frame ) { 

        global $wgPadletUrl;
        $errors = '';
        $return = '';
        $key = '';
        $width = array_key_exists( 'width', $args ) ? $args['width'] : '700';
        $height = array_key_exists( 'height', $args ) ? $args['height'] : '500';
        $hostUrl = $wgPadletUrl;

        if ( array_key_exists( 'key', $args ) ) {
            $key = $args["key"];
        } elseif ( array_key_exists( 'id', $args ) ) {
            $key = $args["id"];
        } else {
            $errors .= wfMessage('loopexternalcontent-padlet-error-noid')->text() . "<br>";
        }

        if ( !empty( $key ) ) {
            $return = Html::rawElement(
                'iframe',
                array(
                    'src' => $hostUrl . $key,
                    'width' => $width,
                    'height' => $height,
                    'allowfullscreen' => 'allowfullscreen',
                    'class' => 'ext-padlet responsive-iframe'
                ),
                ''
            );
        } 
        
        if ( !empty ( $errors ) ) {
            $return .= new LoopException( $errors );
            $parser->addTrackingCategory( 'loop-tracking-category-error' );
			
        }

        return $return;
    }
    
    public static function renderPrezi ( $input, array $args, Parser $parser, PPFrame $frame ) { 

        global $wgPreziUrl;
        $errors = '';
        $return = '';
        $id = '';
        $width = array_key_exists( 'width', $args ) ? $args['width'] : '550';
        $height = array_key_exists( 'height', $args ) ? $args['height'] : '500';
        $controls = ( array_key_exists( 'control', $args ) && $args["control"] == strtolower("simple") ) ? '1' : '0';
        $title = array_key_exists( 'title', $args ) ? $args['title'] : '';
        $hostUrl = $wgPadletUrl;

        if ( array_key_exists( 'id', $args ) ) {
            $id = $args["id"];
        } else {
            $errors .= wfMessage('loopexternalcontent-prezi-error-noid')->text() . "<br>";
        }

        if ( !empty( $id ) ) {
            $return = Html::openElement( 'div', array( 'class' => 'prezi-player', '') );
            $return .= Html::rawElement(
                'iframe',
                array(
                    'src' => $hostUrl . $id . '/?bgcolor=ffffff&amp;lock_to_path='.$controls.'&amp;autoplay=0&amp;autohide_ctrls=0',
                    'width' => $width,
                    'height' => $height,
                    'allowfullscreen' => 'allowfullscreen',
                    'class' => 'ext-prezi responsive-iframe'
                ),
                ''
            );
            $return = Html::rawElement( 
                'div', 
                array( 
                    'class' => 'prezi-player-links text-center' 
                ), 
                '<p><a class="external-link" target="_blank" href="https://prezi.com/'.$id.'/">'.$title.'</a> '.wfMessage( "loopexternalcontent-prezi-on" )->text().' <a class="external-link" target="_blank" href="https://prezi.com">Prezi</a></p>'
            );
            $return = Html::closeElement( 'div' );
        } 
        
        if ( !empty ( $errors ) ) {
            $return .= new LoopException( $errors );
            $parser->addTrackingCategory( 'loop-tracking-category-error' );
			
        }

        return $return;
    }
    
    public static function renderSlideshare ( $input, array $args, Parser $parser, PPFrame $frame ) { 

        global $wgSlideshareUrl;
        $errors = '';
        $return = '';
        $key = '';
        $width = array_key_exists( 'width', $args ) ? $args['width'] : '700';
        $height = array_key_exists( 'height', $args ) ? $args['height'] : '500';
        $hostUrl = $wgSlideshareUrl;

        if ( array_key_exists( 'key', $args ) ) {
            $key = $args["key"];
        } elseif ( array_key_exists( 'id', $args ) ) {
            $key = $args["id"];
        } else {
            $errors .= wfMessage('loopexternalcontent-slideshare-error-noid')->text() . "<br>";
        }

        if ( !empty( $key ) ) {
            $return = Html::rawElement(
                'iframe',
                array(
                    'src' => $hostUrl . $key,
                    'width' => $width,
                    'height' => $height,
                    'allowfullscreen' => 'allowfullscreen',
                    'class' => 'ext-slideshare responsive-iframe'
                ),
                ''
            );
        } 
        
        if ( !empty ( $errors ) ) {
            $return .= new LoopException( $errors );
            $parser->addTrackingCategory( 'loop-tracking-category-error' );
			
        }

        return $return;
    }

}