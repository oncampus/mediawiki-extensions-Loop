<?php
/**
 * A parser extension that adds the tag <loop_media> to mark content as media and provide a table of media
 *
 * @ingroup Extensions
 *
 */
if ( !defined( 'MEDIAWIKI' ) ) die ( "This file cannot be run standalone.\n" );

class LoopMedia extends LoopObject{

	public static $mTag = 'loop_media';
	public static $mIcon = 'media';

	public string $mMediaType;

	public static array $mMediaTypes = array(
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


	public static array $mMediaTypeClass = array(
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
	public function getIcon(): string
	{
		return self::$mMediaTypeClass[$this->getMediaType()];
	}


	/**
	 * Set the media type
	 * @param string $mediatype
	 */
	public function setMediaType(string $mediatype): void
	{
		$this->mMediaType = $mediatype;
	}

	/**
	 * Get the media type
	 * @return string
	 */
	public function getMediaType(): string
	{
		return $this->mMediaType;
	}


	/**
	 *
	 * @param string $input
	 * @param array $args
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @return string
	 */
	public static function renderLoopMedia(string $input, array $args, Parser $parser, PPFrame $frame): string
	{

		$media = new LoopMedia();
		$media->init($input, $args, $parser, $frame);
		$media->parse();
		return $media->render();
	}

	/**
	 * Parse additional args for loop_media
	 * @param bool $fullparse
	 */
	public function parse(bool $fullparse = false): void
	{
		$this->preParse();

		if ($mediatype = $this->GetArg('type')) {
			$this->setMediaType(htmlspecialchars($mediatype));
		} else {
			$this->setMediaType('media');
		}

		if ( ! in_array ( $this->getMediaType(), self::$mMediaTypes ) ) {
			$this->setMediaType('media');
			$e = new LoopException( wfMessage ( "loop-error-unknown-param", "<loop_media>", "type", $this->GetArg('type'), implode ( ', ', self::$mMediaTypes ), 'media' )->text() );
			$this->getParser()->addTrackingCategory( 'loop-tracking-category-error' );
			$this->error = $e;
		}

		$content = $this->getInput();
		if ($content === null) {
			$content = '';
		}
		$this->setContent($this->getParser()->recursiveTagParse($content) );
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

	public function execute($subPage): void
	{

		$out = $this->getOutput();
		$request = $this->getRequest();
		$user = $this->getUser();
		Loop::handleLoopRequest( $out, $request, $user ); #handle editmode

		$out->setPageTitle ( $this->msg ( 'loopmedia-specialpage-title' ) );

		$html = self::renderLoopMediaSpecialPage();
		$out->addHtml ( $html );
	}

	public static function renderLoopMediaSpecialPage(): string
	{
	    global $wgLoopNumberingType;
	    $loopStructure = new LoopStructure();
	    $loopStructure->loadStructureItems();

	    $html = '<h1>';
	    $html .= wfMessage( 'loopmedia-specialpage-title' )->text();
	    $html .= '</h1>';

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

					$media_parser = $media->getParser();
					$title = Title::newFromText( wfMessage( 'loopmedia-specialpage-title')->text());
					$options = ParserOptions::newFromAnon();
					$media_parser->startExternalParse( $title, $options, Parser::OT_HTML );

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

	protected function getGroupName(): string
	{
		return 'loop';
	}
}

