<?php
/**
 * A parser extension that adds the tag <loop_listing> to mark content as listing and provide a table of listings
 *
 * @ingroup Extensions
 *
 */
class LoopListing extends LoopObject{
	
	public static $mTag = 'loop_listing';
	public static $mIcon = 'we-list';

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
	 *
	 * @param string $input        	
	 * @param array $args        	
	 * @param Parser $parser        	
	 * @param Frame $frame        	
	 * @return string
	 */
	public static function renderLoopListing($input, array $args, $parser, $frame) {
		try {
			$listing = new LoopListing();
			$listing->init($input, $args, $parser, $frame);
			$listing->parse();
			if ( isset( $args["index"] ) ) {
				if ( $args["index"] == "false" ) {
					$listing->indexing = false;
				}  elseif ( strtolower( $args["index"] ) == "true" ) {
					$listing->indexing = true;
				} else {
					throw new LoopException( wfMessage( 'loopobject-error-unknown-indexoption', $args["index"], implode( ', ', LoopObject::$mIndexingOptions ) ) );
				}
			} else {
				$listing->indexing = true;
			}
			$html = $listing->render();
		} catch ( LoopException $e ) {
			$parser->addTrackingCategory( 'loop-tracking-category-loop-error' );
			$html = "$e";
		}
		return  $html ;		
	}
	
}

/**
 * Display list of listings for current structure
 * 
 * @author vorreitm, krohnden
 *        
 */
class SpecialLoopListings extends SpecialPage {
	
	public function __construct() {
		parent::__construct ( 'LoopListings' );
	}
	
	public function execute($sub) {
		global $wgParserConf, $wgLoopNumberingType;
		
		$config = $this->getConfig ();
		$request = $this->getRequest ();
		
		$out = $this->getOutput ();
		
		$out->setPageTitle ( $this->msg ( 'looplistings-specialpage-title' ) );
		
		$out->addHtml ( '<h1>' );
		$out->addWikiMsg ( 'looplistings-specialpage-title' );
		$out->addHtml ( '</h1>' );
		
		$loopStructure = new LoopStructure();
		$loopStructure->loadStructureItems();
		
		$parser = new Parser ( $wgParserConf );
		$parserOptions = ParserOptions::newFromUser ( $this->getUser () );
		$parser->Options ( $parserOptions );		
		
		$listings = array ();
		$items = $loopStructure->getStructureItems();
		$listing_number = 1;
		foreach ( $items as $item ) {
			
			$article_id = $item->getArticle ();
			$title = Title::newFromID ( $article_id );
			$rev = Revision::newFromTitle ( $title );
			$content = $rev->getContent ();
			$listing_tags = array ();
			
			$parser->clearState();
			$parser->setTitle ( $title );
			
			$listing_tags = LoopObjectIndex::getObjectsOfType ( 'loop_listing' );
			
			if ( isset( $listing_tags[$article_id] ) ) {
				foreach ( $listing_tags[$article_id] as $listing_tag ) {
					$listing = new LoopListing();
					$listing->init($listing_tag["thumb"], $listing_tag["args"]);
					
					$listing->parse(true);
					if ( $wgLoopNumberingType == "chapter" ) {
						$listing->setNumber ( $listing_tag["nthoftype"] );
					} elseif ( $wgLoopNumberingType == "ongoing" ) {
						$listing->setNumber ( $listing_number );
						$listing_number ++;
					}
					$listing->setArticleId ( $article_id );
					
					$out->addHtml ( $listing->renderForSpecialpage () );
				}
			}
		}
	}
	protected function getGroupName() {
		return 'loop';
	}
}

