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
		global $wgLoopMediaNumbering;
		return $wgLoopMediaNumbering;
	}
	
	/**
	 * {@inheritDoc}
	 * @see LoopObject::getDefaultRenderOption()
	 */
	public function getDefaultRenderOption() {
		global $wgLoopMediaDefaultRenderOption;
		return $wgLoopMediaDefaultRenderOption;
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
 * @author vorreitm
 *        
 */
class SpecialLoopMedia extends SpecialPage {
	
	public function __construct() {
		parent::__construct ( 'LoopMedia' );
	}
	
	public function execute($sub) {
		global $wgParserConf;
		
		$config = $this->getConfig ();
		$request = $this->getRequest ();
		
		$out = $this->getOutput ();
		
		$out->setPageTitle ( $this->msg ( 'loopmedia-specialpage-title' ) );
		
		$out->addHtml ( '<h1>' );
		$out->addWikiMsg ( 'loopmedia-specialpage-title' );
		$out->addHtml ( '</h1>' );
		
		Loop::handleLoopRequest ();
		
		//$structure = LoopStructures::getCurrentLoopStructure ( $this->getUser () );
		$structure = new LoopStructure;
		$structure->getId ();
		
		$parser = new Parser ( $wgParserConf );
		$parserOptions = ParserOptions::newFromUser ( $this->getUser () );
		$parser->Options ( $parserOptions );		
		
		$out->addHtml ( '<table>' );
		$medias = array ();
		$items = $structure->getItems ();
		$media_number = 1;
		foreach ( $items as $item ) {
			
			$article_id = $item->getArticle ();
			$title = Title::newFromID ( $article_id );
			$rev = Revision::newFromTitle ( $title );
			$content = $rev->getContent ();
			$media_tags = array ();
			
			$parser->clearState();
			$parser->setTitle ( $title );
			
			$marked_medias_text = $parser->extractTagsAndParams ( array (
					'loop_media',
					'nowiki' 
			), $content->getWikitextForTransclusion (), $media_tags );
			
			foreach ( $media_tags as $media_tag ) {
				if ($media_tag [0] == 'loop_media') {
					$media = new LoopMedia();
					$media->init($media_tag [1], $media_tag [2]);
					
					$media->parse(true);
					$media->setNumber ( $media_number );
					$media->setArticleId ( $article_id );
					
					$out->addHtml ( $media->renderForSpecialpage () );
					$media_number ++;
				}
			}
		}
		$out->addHtml ( '</table>' );
	}
	protected function getGroupName() {
		return 'loop';
	}
}

