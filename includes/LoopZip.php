<?php

if ( !defined( 'MEDIAWIKI' ) ) die ( "This file cannot be run standalone.\n" );

use MediaWiki\MediaWikiServices;

class LoopZip {

	var $file='';
	var $start='';
	var $width='800px';
	var $height='550px';
	var $input='';
	var $scale = false;
	var $args = array();

	public static function onParserSetup( Parser $parser ) {
		$parser->setHook ( 'loop_zip', 'LoopZip::handleLoopZip' );

		return true;
	}

	public static function handleLoopZip( $input, array $args, Parser $parser, PPFrame $frame ) {

        global $wgUploadDirectory, $wgUploadPath;

        $loopzip = new LoopZip( $input, $args );
        $return = '';

		if ( ! empty( $loopzip->file ) && ! empty( $loopzip->start ) ) {
			$filetitle = Title::newFromText( $loopzip->file, NS_FILE );
            $localfile = MediaWikiServices::getInstance()->getRepoGroup()->getLocalRepo()->newFile( $filetitle );

            if ( is_object( $localfile ) && $localfile->exists() ) {
                $zipfilename = $localfile->getName();
                $hashpath = $localfile->getHashPath();
                $startdir = $wgUploadDirectory . '/' . $hashpath . $zipfilename . '.extracted/';
                $startfile = $startdir . $loopzip->start;
                $scaleClass = 'responsive-iframe';

                if ( is_dir( $startdir ) ) {
					if ( file_exists( $startfile ) ) {
						$starturl = $wgUploadPath . '/' . $hashpath . $zipfilename . '.extracted/' . $loopzip->start;
						if ( $loopzip->scale ) {
							$parser->getOutput()->addModules(["skins.loop-resizer.js"]);
							$scaleClass = "scale-frame";
						}
						$iframe = Html::rawElement(
							'iframe',
							array(
								'src' => $starturl,
								'width' => $loopzip->width,
								'height' => $loopzip->height,
								'data-width' => $loopzip->width,
								'data-height' => $loopzip->height,
								'allowfullscreen' => 'allowfullscreen',
								'class' => 'loop-zip ' . $scaleClass
							),
							''
						);
						$return .= '<div class="loop-zip-wrapper">' . $iframe . '</div>';
					} else {
						$return .= new LoopException( wfMessage( 'loopzip-error-nostartfile', $loopzip->start, $loopzip->file )->text() );
						$parser->addTrackingCategory( 'loop-tracking-category-error' );
					}
                } else {
					$return .= new LoopException( wfMessage( 'loopzip-error-noextraction', $loopzip->file )->text() );
					$parser->addTrackingCategory( 'loop-tracking-category-error' );
				}
            } else {
                $return .= new LoopException( wfMessage( "loop-error-missingfile", "loop_zip", $loopzip->file, 1 )->text() );
                $parser->addTrackingCategory( 'loop-tracking-category-error' );
            }
		} else {
            $return .= new LoopException( wfMessage( 'loopzip-error-missingrequired', "loop_zip", "file/start" )->text() );
            $parser->addTrackingCategory( 'loop-tracking-category-error' );
        }

		return $return;
	}

	public static function onUploadComplete( &$upload ) {
        global $wgUploadDirectory;
        $zipfile = $upload->getLocalFile();
        $zipfilename = $zipfile->getName();
        #$filetitle = Title::newFromText( $zipfilename, NS_FILE );

        $hashpath = $zipfile->getHashPath();
        $from = $wgUploadDirectory . '/' . $hashpath . $zipfilename;
		$to = $wgUploadDirectory . '/' . $hashpath . $zipfilename . '.extracted/';

		$zip = new ZipArchive;
		$continue = $zip->open( $from );
        if ( $continue === true ) {
			if ( is_dir($to) ) {
				exec("rm -r $to");
			}
			$extract = true;
			# exclude php files within zips
			for( $i = 0; $i < $zip->numFiles; $i++ ){
				$stat = $zip->statIndex( $i );
				if ( strrpos($stat['name'], ".php", -1 ) ) {
					$extract = false;
					break;
				}
			}
			if ( $extract ) {
				$zip->extractTo( $to );
			}
            $zip->close();
        }
		return true;
	}

	function __construct( $input, $args ) {

		$this->input = $input;
		$this->args = $args;
        $this->width = array_key_exists( 'width', $args ) ? $args['width'] : '800';
        $this->height = array_key_exists( 'height', $args ) ? $args['height'] : '500';
        $this->scale = ( array_key_exists( 'scale', $args ) && strtolower( $args['scale'] ) === "true" ) ? true : false;

		if ( array_key_exists( 'file', $args ) ) {
			$this->file = $args[ "file" ];
		}
		if ( array_key_exists( 'start', $args ) ) {
			$this->start = $args[ "start" ];
		}

		return true;
	}

}
?>
