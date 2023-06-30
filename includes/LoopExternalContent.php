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
		$parser->setHook ( 'taskcard', 'LoopExternalContent::renderTaskcard' );
		$parser->setHook ( 'prezi', 'LoopExternalContent::renderPrezi' );
		$parser->setHook ( 'slideshare', 'LoopExternalContent::renderSlideshare' );
		$parser->setHook ( 'quizlet', 'LoopExternalContent::renderQuizlet' );
		return true;
    }

    public static function renderH5P ( $input, array $args, Parser $parser, PPFrame $frame ) {

        global $wgH5PHostUrl, $wgH5PHostUrlAppendix;
        $errors = '';
        $return = '';
        $id = array_key_exists( 'id', $args ) ? $args['id'] : '';
        $width = array_key_exists( 'width', $args ) ? $args['width'] : '800';
        $height = array_key_exists( 'height', $args ) ? $args['height'] : '450';
        $parser->getOutput()->addModules("skins.loop-h5p-resizer.js");

        if ( !empty( $id ) ) {
            $return = Html::rawElement(
                'iframe',
                array(
                    'src' => $wgH5PHostUrl . $id . $wgH5PHostUrlAppendix,
                    'width' => $width,
                    'height' => $height,
                    'data-height' => $height,
                    'data-width' => $width,
                    'allowfullscreen' => 'allowfullscreen',
                    'class' => 'ext-h5p h5p-iframe'
                ),
                ''
            );
        } else {
            $errors .= wfMessage( "loop-error-missingrequired", "H5P", "id")->text() . "<br>";
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
        $width = array_key_exists( 'width', $args ) ? $args['width'] : '800';
        $height = array_key_exists( 'height', $args ) ? $args['height'] : '500';
        $hostUrl = $wgLearningAppUrl;
        $scale = ( array_key_exists( 'scale', $args ) && strtolower( $args['scale'] ) === "true" ) ? true : false;
        $scaleClass = 'responsive-iframe';

        if ( $scale ) {
            $parser->getOutput()->addModules("skins.loop-resizer.js");
            $scaleClass = "scale-frame";
        }

        if ( array_key_exists( 'app', $args ) ) {
            $appId = "app=" . $args["app"];
        } elseif ( array_key_exists( 'privateapp', $args ) ) {
            $appId = "v=" . $args["privateapp"];
        } else {
            $errors .= wfMessage( "loop-error-missingrequired", "LearningApp", "app/privateapp")->text() . "<br>";
        }

        if ( !empty( $appId ) ) {
            $return = Html::rawElement(
                'iframe',
                array(
                    'src' => $hostUrl . $appId,
                    'width' => $width,
                    'height' => $height,
                    'data-height' => $height,
                    'data-width' => $width,
                    'allowfullscreen' => 'allowfullscreen',
                    'class' => 'ext-learningapp ' . $scaleClass
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
		$width = array_key_exists( 'width', $args ) ? $args['width'] : '800';
		$height = array_key_exists( 'height', $args ) ? $args['height'] : '500';
		$hostUrl = $wgPadletUrl;
		$scale = ( array_key_exists( 'scale', $args ) && strtolower( $args['scale'] ) === "true" ) ? true : false;
		$scaleClass = 'responsive-iframe';

		if ( $scale ) {
			$parser->getOutput()->addModules("skins.loop-resizer.js");
			$scaleClass = "scale-frame";
		}

		if ( array_key_exists( 'key', $args ) ) {
			$key = $args["key"];
		} elseif ( array_key_exists( 'id', $args ) ) {
			$key = $args["id"];
		} else {
			$errors .= wfMessage( "loop-error-missingrequired", "Padlet", "id")->text() . "<br>";
		}

		if ( !empty( $key ) ) {
			$return = Html::rawElement(
				'iframe',
				array(
					'src' => $hostUrl . $key,
					'width' => $width,
					'height' => $height,
					'data-height' => $height,
					'data-width' => $width,
					'allowfullscreen' => 'allowfullscreen',
					'class' => 'ext-padlet ' . $scaleClass
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

	public static function renderTaskcard ( $input, array $args, Parser $parser, PPFrame $frame ) {

	global $wgTaskcardUrl;
	$errors = '';
	$return = '';
	$key = '';
	$token = '';
	$width = array_key_exists( 'width', $args ) ? $args['width'] : '800';
	$height = array_key_exists( 'height', $args ) ? $args['height'] : '500';
	$hostUrl = $wgTaskcardUrl;

	$scale = array_key_exists( 'scale', $args ) && strtolower( $args['scale'] ) === "true";
	$scaleClass = 'responsive-iframe';

	if ( $scale ) {
		$parser->getOutput()->addModules("skins.loop-resizer.js");
		$scaleClass = "scale-frame";
	}

	if ( array_key_exists( 'key', $args ) ) {
		$key = $args["key"];
	} elseif ( array_key_exists( 'id', $args ) ) {
		$key = $args["id"];
	} else {
		$errors .= wfMessage( "loop-error-missingrequired", "Taskcard", "id")->text() . "<br>";
	}
	//Added token functionality
	if ( array_key_exists( 'token', $args ) ) {
		$token = "?token=" . $args["token"];
	}else {
		$errors .= wfMessage( "loop-error-missingrequired", "Taskcard", "id")->text() . "<br>";
	}

	if ( !empty( $key ) ) {
		$return = Html::rawElement(
			'iframe',
			array(
				'src' => $hostUrl . $key .$token,
				'width' => $width,
				'height' => $height,
				'data-height' => $height,
				'data-width' => $width,
				'allowfullscreen' => 'allowfullscreen',
				'class' => 'ext-taskcard ' . $scaleClass
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
        $height = array_key_exists( 'height', $args ) ? $args['height'] : '450';
        $controls = ( array_key_exists( 'control', $args ) && $args["control"] == strtolower("simple") ) ? '1' : '0';
        $title = array_key_exists( 'title', $args ) ? $args['title'] : '';
        $hostUrl = $wgPreziUrl;
        $scale = ( array_key_exists( 'scale', $args ) && strtolower( $args['scale'] ) === "true" ) ? true : false;
        $scaleClass = 'responsive-iframe';

        if ( $scale ) {
            $parser->getOutput()->addModules("skins.loop-resizer.js");
            $scaleClass = "scale-frame";
        }

        if ( array_key_exists( 'id', $args ) ) {
            $id = $args["id"];
        } else {
            $errors .= wfMessage( "loop-error-missingrequired", "Prezi", "id")->text() . "<br>";
        }

        if ( !empty( $id ) ) {
            $return = Html::openElement( 'div', array( 'class' => 'prezi-player', 'style' => 'width:"'.$width.'";') );
            $return .= Html::rawElement(
                'iframe',
                array(
                    'src' => $hostUrl . $id . '/?bgcolor=ffffff&amp;lock_to_path='.$controls.'&amp;autoplay=0&amp;autohide_ctrls=0',
                    'width' => $width,
                    'height' => $height,
                    'data-height' => $height,
                    'data-width' => $width,
                    'allowfullscreen' => 'allowfullscreen',
                    'class' => 'ext-prezi ' . $scaleClass
                ),
                ''
            );
            $return .= Html::rawElement(
                'div',
                array(
                    'class' => 'prezi-player-links text-center'
                ),
                '<p style="width:'.$width.'px;"><a class="external-link" target="_blank" href="https://prezi.com/'.$id.'/">'.$title.'</a> '.wfMessage( "loopexternalcontent-prezi-on" )->text().' <a class="external-link" target="_blank" href="https://prezi.com">Prezi</a></p>'
            );
            $return .= Html::closeElement( 'div' );
        }

        if ( !empty ( $errors ) ) {
            $return .= new LoopException( $errors );
            $parser->addTrackingCategory( 'loop-tracking-category-error' );
        }
        #dd($input, $args, $return);

        return $return;
    }

    public static function renderSlideshare ( $input, array $args, Parser $parser, PPFrame $frame ) {

        global $wgSlideshareUrl;
        $errors = '';
        $return = '';
        $key = '';
        $width = array_key_exists( 'width', $args ) ? $args['width'] : '800';
        $height = array_key_exists( 'height', $args ) ? $args['height'] : '500';
        $hostUrl = $wgSlideshareUrl;
        $scale = ( array_key_exists( 'scale', $args ) && strtolower( $args['scale'] ) === "true" ) ? true : false;
        $scaleClass = 'responsive-iframe';

        if ( $scale ) {
            $parser->getOutput()->addModules("skins.loop-resizer.js");
            $scaleClass = "scale-frame";
        }

        if ( array_key_exists( 'key', $args ) ) {
            $key = $args["key"];
        } elseif ( array_key_exists( 'id', $args ) ) {
            $key = $args["id"];
        } else {
            $errors .= wfMessage( "loop-error-missingrequired", "Slideshare", "id")->text() . "<br>";
        }

        if ( !empty( $key ) ) {
            $return = Html::rawElement(
                'iframe',
                array(
                    'src' => $hostUrl . $key,
                    'width' => $width,
                    'height' => $height,
                    'data-height' => $height,
                    'data-width' => $width,
                    'allowfullscreen' => 'allowfullscreen',
                    'class' => 'ext-slideshare ' . $scaleClass
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

    public static function renderQuizlet ( $input, array $args, Parser $parser, PPFrame $frame ) {

        global $wgQuizletUrl;
        $errors = '';
        $return = '';
        $id = '';
        $allowed_modes = array( 'flashcards', 'learn', 'scatter', 'speller', 'test', 'spacerace' );
        $width = array_key_exists( 'width', $args ) ? $args['width'] : '800';
        $height = array_key_exists( 'height', $args ) ? $args['height'] : '500';
        $mode = ( array_key_exists( 'mode', $args ) && array_key_exists( strtolower( $args['mode'] ), $allowed_modes ) ) ? $args['mode'] : 'flashcards';
        $hostUrl = $wgQuizletUrl;
        $scale = ( array_key_exists( 'scale', $args ) && strtolower( $args['scale'] ) === "true" ) ? true : false;
        $scaleClass = 'responsive-iframe';

        if ( $scale ) {
            $parser->getOutput()->addModules("skins.loop-resizer.js");
            $scaleClass = "scale-frame";
        }

        if ( array_key_exists( 'quiz', $args ) ) {
            $id = $args["quiz"];
        } elseif ( array_key_exists( 'id', $args ) ) {
            $id = $args["id"];
        } else {
            $errors .= wfMessage( "loop-error-missingrequired", "Quizlet", "id")->text() . "<br>";
        }

        if ( !empty( $id ) ) {
            $return = Html::rawElement(
                'iframe',
                array(
                    'src' => $hostUrl .  $id . '/' . $mode . '/embedv2',
                    'width' => $width,
                    'height' => $height,
                    'data-height' => $height,
                    'data-width' => $width,
                    'allowfullscreen' => 'allowfullscreen',
                    'class' => 'ext-quizlet ' . $scaleClass
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
