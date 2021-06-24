<?php
#TODO MW 1.35 DEPRECATION
/**
 * A parser extension that adds the tags <loop_video> and <loop_audio>
 *
 * @author Dennis Krohn <dennis.krohn@th-luebeck.de>
 * @ingroup Extensions
 *
 */
class LoopMediaHandler {

	static function onParserSetup( Parser $parser ) {
		$parser->setHook( 'loop_video', 'LoopMediaHandler::renderLoopVideo' );
		$parser->setHook( 'loop_audio', 'LoopMediaHandler::renderLoopAudio' );
		return true;
	}

	static function renderLoopVideo( $input, array $args, Parser $parser, PPFrame $frame ) {
        $html = '';
        $width = "100%";
        $height = "auto";

		if ( array_key_exists( 'width', $args ) ) {
			if ( !empty ( $args["width"] ) ) {
				$width = $args["width"];
			}
		}

		if ( array_key_exists( 'height', $args ) ) {
			if ( !empty ( $args["height"] ) ) {
				$height = $args["height"];
			}
		}

        try {
            if ( array_key_exists( 'source', $args ) ) {
                if ( !empty ( $args["source"] ) ) {
                    $file = wfLocalFile( $args["source"] );
                    if ( is_object( $file ) && $file->exists() ) {
                        $source = $file->getFullUrl();
                    } else {
                        throw new LoopException( wfMessage( "loop-error-missingfile", "loop_video", $args["source"], 0 )->text() );
                    }
                } else {
                    throw new LoopException( wfMessage( "loop-error-missingrequired", "loop_video", "source" )->text() );
                }
            } else {
                throw new LoopException( wfMessage( "loop-error-missingrequired", "loop_video", "source" )->text() );
            }

            if ( array_key_exists('image', $args ) ) {
                if ( !empty ( $args["image"] ) ) {
                    $file = wfLocalFile( $args["image"] );
                    if ( is_object( $file ) && $file->exists() ) {
                        $image = $file->getFullUrl();
                    } else {
                        throw new LoopException( wfMessage( "loop-error-missingfile", "loop_video", $args["image"], 1 )->text() );
                    }
                }
            }
        } catch ( LoopException $e ) {
			$parser->addTrackingCategory( 'loop-tracking-category-error' );
			$html = $e;
        }

        if ( isset( $source ) ) {
            $html .= '<video controls class="responsive-video" width="' . $width . '" height="' . $height . '"';
            if ( isset ( $image ) ) {
                $html .= ' poster="' . $image . '" ';
            }
            $html .= ' src="' . $source . '"></video>';
        }
		return $html;
    }

    static function renderLoopAudio( $input, array $args, Parser $parser, PPFrame $frame ) {
        $html = '';
        $width = "100%";
        $height = "auto";

		if ( array_key_exists( 'width', $args ) ) {
			if ( !empty ( $args["width"] ) ) {
				$width = $args["width"];
			}
		}

		if ( array_key_exists( 'height', $args ) ) {
			if ( !empty ( $args["height"] ) ) {
				$height = $args["height"];
			}
		}

        try {
            if ( array_key_exists( 'source', $args ) ) {
                if ( !empty ( $args["source"] ) ) {
                    $file = wfLocalFile( $args["source"] );
                    if ( is_object( $file ) && $file->exists() ) {
                        $source = $file->getFullUrl();
                    } else {
                        throw new LoopException( wfMessage( "loop-error-missingfile", "loop_audio", $args["source"], 0 )->text() );
                    }
                } else {
                    throw new LoopException( wfMessage( "loop-error-missingrequired", "loop_audio", "source" )->text() );
                }
            } else {
                throw new LoopException( wfMessage( "loop-error-missingrequired", "loop_audio", "source" )->text() );
            }
        } catch ( LoopException $e ) {
			$parser->addTrackingCategory( 'loop-tracking-category-error' );
			$html = $e;
        }

        if ( isset( $source ) ) {
            $html .= '<audio controls class="responsive-audio" width="' . $width . '" height="' . $height . '"';
            $html .= ' src="' . $source . '"></audio>';
        }
		return $html;
	}

}

# WILL BE REMOVED UPON 02/2021
# TEMPORARY SOLUTION ONLY
class LoopSwfHandler  {
    public static function onImageBeforeProduceHTML( DummyLinker &$linker, Title &$title, &$file, array &$frameParams, array &$handlerParams, &$time, &$result, Parser $parser, string &$query, &$widthOption ) {
        if ( is_object( $file ) && $file->getMimeType() == "application/x-shockwave-flash" ) {
            global $wgOut, $wgLanguageCode;

            $user = $wgOut->getUser();
            $editMode = $user->getOption( 'LoopEditMode', false, true );
            $error = "";
            if ( $editMode ) {
                $msg = $wgLanguageCode == "de" || $wgLanguageCode == "de-formal" ? "Achtung: Flash Elemente werden nur noch bis Februar 2021 unterstützt." : "Warning: Flash will no longer be supported after February 2021.";
                $error = new LoopException( $msg ).'<br>';
            }

            $url = $file->getFullUrl();
            $s = $error . '<object class="" style="min-width: 100%; min-height: 500px;">
                <param name="wmode" value="transparent">
                <param name="movie" value="'. $url .'">
                <embed src="'. $url .'" type="application/x-shockwave-flash" style="min-width: 100%; min-height: 500px;">
            </object>';

			$parser->addTrackingCategory( 'loop-tracking-category-error' );
            $result = str_replace("\n", ' ', $s);
            return false;
        }
        return true;
    }
}
