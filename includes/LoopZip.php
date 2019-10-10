<?php
if ( ! defined( 'MEDIAWIKI' ) )
	die();

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
        
        global $wgParser, $wgUploadDirectory, $wgUploadPath, $wgServer, $wgOut;
        
        $loopzip = new LoopZip( $input, $args );
        $return = '';
        
		if ( ! empty( $loopzip->file ) && ! empty( $loopzip->start ) ) {
			$filetitle = Title::newFromText( $loopzip->file, NS_FILE );
            $localfile = wfLocalFile( $filetitle ); # wfLocalFile() will be deprecated in 1.34

            if ( $localfile->exists() ) {
                $zipfilename = $localfile->getName();
                $hashpath = $localfile->getHashPath();
                $startfile = $wgUploadDirectory . '/' . $hashpath . $zipfilename . '.extracted/' . $loopzip->start;
                $fileID = uniqid();
                $scaleClass = 'responsive-iframe';
            
                if ( $loopzip->scale ) {
                    $wgOut->addModules("skins.loop-resizer.js");
                    $scaleClass = "scale-frame";
                }

                if ( file_exists( $startfile ) ) {
                    $starturl = $wgUploadPath . '/' . $hashpath . $zipfilename . '.extracted/' . $loopzip->start;
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
                $return .= new LoopException( wfMessage( 'loopzip-error-nozipfile', $loopzip->file )->text() );
                $parser->addTrackingCategory( 'loop-tracking-category-error' );
            }
		} else {
            $return .= new LoopException( wfMessage( 'loopzip-error-missingrequired' )->text() );
            $parser->addTrackingCategory( 'loop-tracking-category-error' );
        }
		
		return $return;
	}
	
	public static function onUploadComplete( &$upload ) {
        global $wgUploadDirectory;
        $zipfile = $upload->getLocalFile();
        $zipfilename = $zipfile->getName();
        $filetitle = Title::newFromText( $zipfilename, NS_FILE );
        
        $hashpath = $zipfile->getHashPath();
        $from = $wgUploadDirectory . '/' . $hashpath . $zipfilename;
        $to = $wgUploadDirectory . '/' . $hashpath . $zipfilename . '.extracted/';
        $zip = new ZipArchive;
        if ( $zip->open( $from ) === true ) {
            $zip->extractTo( $to );
            $zip->close();
        }
		return true;
	}
	
	function __construct( $input, $args ) { 

		$this->input = $input;
		$this->args = $args;
        $this->width = array_key_exists( 'width', $args ) ? $args['width'] : '800';
        $this->height = array_key_exists( 'height', $args ) ? $args['height'] : '500';
        $this->scale = ( array_key_exists( 'scale', $args ) && $args['scale'] === strtolower( "true" ) ) ? true : false;
		
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