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
		global $wgLoopListingNumbering;
		return $wgLoopListingNumbering;
	}
	
	/**
	 * {@inheritDoc}
	 * @see LoopObject::getDefaultRenderOption()
	 */
	public function getDefaultRenderOption() {
		global $wgLoopListingDefaultRenderOption;
		return $wgLoopListingDefaultRenderOption;
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
		global $wgParserConf;
		
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
		
		#$out->addHtml ( '<table>' );
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
			
			
			$marked_listings_text = $parser->extractTagsAndParams ( array (
					'loop_listing',
					'nowiki' 
			), $content->getWikitextForTransclusion (), $listing_tags );
			
			foreach ( $listing_tags as $listing_tag ) {
				if ($listing_tag [0] == 'loop_listing') {
					$listing = new LoopListing();
					$listing->init($listing_tag [1], $listing_tag [2]);
					
					$listing->parse(true);
					$listing->setNumber ( $listing_number );
					$listing->setArticleId ( $article_id );
					
					$out->addHtml ( $listing->renderForSpecialpage () );
					$listing_number ++;
				}
			}
		}
		#$out->addHtml ( '</table>' );
	}
	protected function getGroupName() {
		return 'loop';
	}
}

