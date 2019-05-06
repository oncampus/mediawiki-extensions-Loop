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
		global $wgLoopObjectNumbering;
		return $wgLoopObjectNumbering;
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
			if ( isset( $args["index"] ) ) {
				if ( $args["index"] == "false" ) {
					$formula->indexing = false;
				} else {
					$formula->indexing = true;
				}
			} else {
				$formula->indexing = true;
			}
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
 * @author vorreitm, krohnden
 *        
 */
class SpecialLoopFormulas extends SpecialPage {
	
	public function __construct() {
		parent::__construct ( 'LoopFormulas' );
	}
	
	public function execute($sub) {
		global $wgParserConf, $wgLoopNumberingType;
		
		$config = $this->getConfig ();
		$request = $this->getRequest ();
		
		$out = $this->getOutput ();
		
		$out->setPageTitle ( $this->msg ( 'loopformulas-specialpage-title' ) );
		
		$out->addHtml ( '<h1>' );
		$out->addWikiMsg ( 'loopformulas-specialpage-title' );
		$out->addHtml ( '</h1>' );
		
		$loopStructure = new LoopStructure();
		$loopStructure->loadStructureItems();
		
		$parser = new Parser ( $wgParserConf );
		$parserOptions = ParserOptions::newFromUser ( $this->getUser () );
		$parser->Options ( $parserOptions );		
		
		$formulas = array ();
		$items = $loopStructure->getStructureItems();
		$formula_number = 1;
		foreach ( $items as $item ) {
			
			$article_id = $item->getArticle ();
			$title = Title::newFromID ( $article_id );
			$rev = Revision::newFromTitle ( $title );
			$content = $rev->getContent ();
			$formula_tags = array ();
			
			$parser->clearState();
			$parser->setTitle ( $title );
			
			$formula_tags = LoopObjectIndex::getObjectsOfType ( 'loop_formula' );
			
			if ( isset( $formula_tags[$article_id] ) ) {
				foreach ( $formula_tags[$article_id] as $formula_tag ) {
					$formula = new LoopFormula();
					$formula->init($formula_tag ["thumb"], $formula_tag ["args"]);
					
					$formula->parse(true);
					if ( $wgLoopNumberingType == "chapter" ) {
						$formula->setNumber ( $formula_tag["nthoftype"] );
					} elseif ( $wgLoopNumberingType == "ongoing" ) {
						$formula->setNumber ( $formula_number );
						$formula_number ++;
					}
					$formula->setArticleId ( $article_id );
					
					$out->addHtml ( $formula->renderForSpecialpage () );
				}
			}
		}
	}
	protected function getGroupName() {
		return 'loop';
	}
}

