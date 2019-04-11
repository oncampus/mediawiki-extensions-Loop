<?php
/**
 * A parser extension that adds the tag <loop_formula> to mark content as formula and provide a table of formulas
 *
 * @ingroup Extensions
 *
 */
class LoopFormula extends LoopObject{
	
	public static $mTag = 'loop_formula';
	public static $mIcon = 'formula';

	/**
	 * {@inheritDoc}
	 * @see LoopObject::getShowNumber()
	 */
	public function getShowNumber() {
		global $wgLoopFormulaNumbering;
		return $wgLoopFormulaNumbering;
	}
	
	/**
	 * {@inheritDoc}
	 * @see LoopObject::getDefaultRenderOption()
	 */
	public function getDefaultRenderOption() {
		global $wgLoopFormulaDefaultRenderOption;
		return $wgLoopFormulaDefaultRenderOption;
	}
		
	
	/**
	 *
	 * @param string $input        	
	 * @param array $args        	
	 * @param Parser $parser        	
	 * @param Frame $frame        	
	 * @return string
	 */
	public static function renderLoopFormula($input, array $args, $parser, $frame) {
		try {
			$formula = new LoopFormula();
			$formula->init($input, $args, $parser, $frame);
			$formula->parse();
			$html = $formula->render();
		} catch ( LoopException $e ) {
			$parser->addTrackingCategory( 'loop-tracking-category-loop-error' );
			$html = "$e";
		}
		return  $html ;		
	}
	
	
	

}

/**
 * Display list of formulas for current structure
 * 
 * @author vorreitm
 *        
 */
class SpecialLoopFormulas extends SpecialPage {
	
	public function __construct() {
		parent::__construct ( 'LoopFormulas' );
	}
	
	public function execute($sub) {
		global $wgParserConf, $wgParser, $wgUser;
		
		$config = $this->getConfig ();
		$request = $this->getRequest ();
		
		$out = $this->getOutput ();
		
		$out->setPageTitle ( $this->msg ( 'loopformulas-specialpage-title' ) );
		
		$out->addHtml ( '<h1>' );
		$out->addWikiMsg ( 'loopformulas-specialpage-title' );
		$out->addHtml ( '</h1>' );
		
		Loop::handleLoopRequest ();
		
		//$structure = LoopStructures::getCurrentLoopStructure ( $this->getUser () );
		$structure = new LoopStructure;
		$structure->getId ();
		
		$parser = new Parser ( $wgParserConf );
		$parserOptions = ParserOptions::newFromUser ( $this->getUser () );
		$parser->Options ( $parserOptions );		
		
		$out->addHtml ( '<table>' );
		$formulas = array ();
		$items = $structure->getItems ();
		$formula_number = 1;
		foreach ( $items as $item ) {
			
			$article_id = $item->getArticle ();
			$title = Title::newFromID ( $article_id );
			$rev = Revision::newFromTitle ( $title );
			$content = $rev->getContent ();
			$formula_tags = array ();
			
			$parser->clearState();
			$parser->setTitle ( $title );
			
			
			$marked_formulas_text = $parser->extractTagsAndParams ( array (
					'loop_formula',
					'nowiki' 
			), $content->getWikitextForTransclusion (), $formula_tags );
			
			foreach ( $formula_tags as $formula_tag ) {
				if ($formula_tag [0] == 'loop_formula') {
					$formula = new LoopFormula();
					$formula->init($formula_tag [1], $formula_tag [2]);
					
					$formula->parse(true);
					$formula->setNumber ( $formula_number );
					$formula->setArticleId ( $article_id );
					
					$out->addHtml ( $formula->renderForSpecialpage () );
					$formula_number ++;
				}
			}
		}
		$out->addHtml ( '</table>' );
	}
	protected function getGroupName() {
		return 'loop';
	}
}

