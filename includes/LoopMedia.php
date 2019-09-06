<?php
/**
 * A parser extension that adds the tag <loop_media> to mark content as media and provide a table of media
 *
 * @ingroup Extensions
 *
 */
class LoopMedia extends LoopObject{

	public static $mTag = 'loop_media';
	public static $mIcon = 'media';
	
	public $mMediaType;
	
	public static $mMediaTypes = array(
			'rollover',
			'video',
			'interaction',
			'audio',
			'animation',
			'simulation',
			'click',
			'dragdrop',
			'media'
	);
	
	
	public static $mMediaTypeClass = array(
			'rollover' => 'rollover',
			'video' => 'video',
			'interaction' => 'click',
			'audio' => 'audio',
			'animation' => 'animation',
			'simulation' => 'simulation',
			'click' => 'click',
			'dragdrop' => 'dragdrop',
			'media' => 'media'			
	);
	
	/**
	 * {@inheritDoc}
	 * @see LoopObject::getShowNumber()
	 */
	public function getShowNumber() {
		global $wgLoopObjectNumbering;
		return $wgLoopObjectNumbering;
	}
	
	/**
	 * {@inheritDoc}
	 * @see LoopObject::getDefaultRenderOption()
	 */
	public function getDefaultRenderOption() {
		global $wgLoopObjectDefaultRenderOption;
		return $wgLoopObjectDefaultRenderOption;
	}
		
	/**
	 * {@inheritDoc}
	 * @see LoopObject::getIcon()
	 */
	public function getIcon() {
		return self::$mMediaTypeClass[$this->getMediaType()];
	}
	
	
	/**
	 * Set the media type
	 * @param string $mediatype
	 */
	public function setMediaType($mediatype) {
		$this->mMediaType = $mediatype;
	}
	
	/**
	 * Get the media type
	 * @return string 
	 */
	public function getMediaType() {
		return $this->mMediaType;
	}	
	
	
	/**
	 *
	 * @param string $input        	
	 * @param array $args        	
	 * @param Parser $parser        	
	 * @param Frame $frame        	
	 * @return string
	 */
	public static function renderLoopMedia($input, array $args, $parser, $frame) {
		
		$media = new LoopMedia();
		$media->init($input, $args, $parser, $frame);
		$media->parse();
		$html = $media->render();
			
		return  $html;
	}
	
	/**
	 * Parse additional args for loop_media
	 * @param bool $fullparse
	 */
	public function parse($fullparse = false) {
		$this->preParse($fullparse);
		
		if ($mediatype = $this->GetArg('type')) {
			$this->setMediaType(htmlspecialchars($mediatype));
		} else {
			$this->setMediaType('media');
		}
		
		if ( ! in_array ( $this->getMediaType(), self::$mMediaTypes ) ) {
			$this->setMediaType('media');
			$e = new LoopException( wfMessage( 'loopmedia-error-unknown-mediatype', $mediatype, implode( ', ',self::$mMediaTypes ) ) );
			$this->getParser()->addTrackingCategory( 'loop-tracking-category-error' );
			$this->error = $e;
		}		
		
		$this->setContent($this->getParser()->recursiveTagParse($this->getInput()) );
	}	
}

/**
 * Display list of media for current structure
 * 
 * @author vorreitm, krohnden
 *        
 */
class SpecialLoopMedia extends SpecialPage {
	
	public function __construct() {
		parent::__construct ( 'LoopMedia' );
	}
	
	public function execute($sub) {
		
		$config = $this->getConfig ();

		$out = $this->getOutput();
		$request = $this->getRequest();
		$user = $this->getUser();
		Loop::handleLoopRequest( $out, $request, $user ); #handle editmode
		
		$out->setPageTitle ( $this->msg ( 'loopmedia-specialpage-title' ) );
		
		$html = self::renderLoopMediaSpecialPage();
		$out->addHtml ( $html );
	}
	
	public static function renderLoopMediaSpecialPage() {
	    global $wgParserConf, $wgLoopNumberingType;
	    $loopStructure = new LoopStructure();
	    $loopStructure->loadStructureItems();
	    
	    $html = '<h1>';
	    $html .= wfMessage( 'loopmedia-specialpage-title' )->text();
	    $html .= '</h1>';
	    
	    $parser = new Parser ( $wgParserConf );
	    $parserOptions = new ParserOptions();
	    $parser->Options ( $parserOptions );
	    
	    $medias = array ();
	    $structureItems = $loopStructure->getStructureItems();
	    $glossaryItems = LoopGlossary::getGlossaryPages();
	    $media_number = 1;
	    $articleIds = array();
	    $html .= '<table class="table table-hover list_of_objects">';
	    $media_tags = LoopObjectIndex::getObjectsOfType ( 'loop_media' );
	    
	    foreach ( $structureItems as $structureItem ) {
	        $articleIds[ $structureItem->article ] = NS_MAIN;
	    }
	    foreach ( $glossaryItems as $glossaryItem ) {
	        $articleIds[ $glossaryItem->mArticleID ] = NS_GLOSSARY;
	    }
	    
	    foreach ( $articleIds as $article => $ns ) {
	        
	        $article_id = $article;
	        
	        if ( isset( $media_tags[$article_id] ) ) {
	            foreach ( $media_tags[$article_id] as $media_tag ) {
	                $media = new LoopMedia();
	                $media->init($media_tag ["thumb"], $media_tag ["args"]);
	                
	                $media->parse();
	                if ( $wgLoopNumberingType == "chapter" ) {
	                    $media->setNumber ( $media_tag["nthoftype"] );
	                } elseif ( $wgLoopNumberingType == "ongoing" ) {
	                    $media->setNumber ( $media_number );
	                    $media_number ++;
	                }
	                $media->setArticleId ( $article_id );
	                
	                $html .= $media->renderForSpecialpage ( $ns );
	            }
	        }
	    }
	    $html .= '</table>';
	    return $html;
	}
	
	protected function getGroupName() {
		return 'loop';
	}
}

