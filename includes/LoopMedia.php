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
		try {
			$media = new LoopMedia();
			$media->init($input, $args, $parser, $frame);
			$media->parse();
			if ( isset( $args["index"] ) ) {
				if ( $args["index"] == "false" ) {
					$media->indexing = false;
				} elseif ( strtolower( $args["index"] ) == "true" ) {
					$media->indexing = true;
				} else {
					throw new LoopException( wfMessage( 'loopobject-error-unknown-indexoption', $args["index"], implode( ', ', LoopObject::$mIndexingOptions ) ) );
				}
			} else {
				$media->indexing = true;
			}
			$html = $media->render();
		} catch ( LoopException $e ) {
			$parser->addTrackingCategory( 'loop-tracking-category-loop-error' );
			$html = "$e";
		}
		return  $html ;
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
			throw new LoopException( wfMessage( 'loopmedia-error-unknown-mediatype', $this->getMediaType(), implode( ', ',self::$mMediaTypes ) ) );
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
		global $wgParserConf, $wgLoopNumberingType;
		
		$config = $this->getConfig ();
		$request = $this->getRequest ();
		
		$out = $this->getOutput ();
		
		$out->setPageTitle ( $this->msg ( 'loopmedia-specialpage-title' ) );
		
		$out->addHtml ( '<h1>' );
		$out->addWikiMsg ( 'loopmedia-specialpage-title' );
		$out->addHtml ( '</h1>' );
		
		$loopStructure = new LoopStructure();
		$loopStructure->loadStructureItems();
		
		$parser = new Parser ( $wgParserConf );
		$parserOptions = ParserOptions::newFromUser ( $this->getUser () );
		$parser->Options ( $parserOptions );		
		
		$medias = array ();
		$items = $loopStructure->getStructureItems();
		$media_number = 1;
		foreach ( $items as $item ) {
			
			$article_id = $item->getArticle ();
			$title = Title::newFromID ( $article_id );
			$rev = Revision::newFromTitle ( $title );
			$content = $rev->getContent ();
			$media_tags = array ();
			
			$parser->clearState();
			$parser->setTitle ( $title );
			
			$media_tags = LoopObjectIndex::getObjectsOfType ( 'loop_media' );
			
			if ( isset( $media_tags[$article_id] ) ) {
			foreach ( $media_tags[$article_id] as $media_tag ) {
				$media = new LoopMedia();
				$media->init($media_tag ["thumb"], $media_tag ["args"]);
				
				$media->parse(true);
				if ( $wgLoopNumberingType == "chapter" ) {
					$media->setNumber ( $media_tag["nthoftype"] );
				} elseif ( $wgLoopNumberingType == "ongoing" ) {
					$media->setNumber ( $media_number );
					$media_number ++;
				}
				$media->setArticleId ( $article_id );
				
				$out->addHtml ( $media->renderForSpecialpage () );
				}
			}
		}
	}
	protected function getGroupName() {
		return 'loop';
	}
}

