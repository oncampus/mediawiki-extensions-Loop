<?php
/**
 * @description A parser extension that adds the tag <loop_formula> to mark content as formula and provide a table of formulas
 * @ingroup Extensions
 * @author Marc Vorreiter @vorreiter <marc.vorreiter@th-luebeck.de>
 * @author Dennis Krohn @krohnden <dennis.krohn@th-luebeck.de>
 */
if ( !defined( 'MEDIAWIKI' ) ) die ( "This file cannot be run standalone.\n" );

use MediaWiki\MediaWikiServices;

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
	public static function renderLoopFormula($input, array $args, $parser, $frame) {

		$formula = new LoopFormula();
		$formula->init($input, $args, $parser, $frame);
		$formula->parse();
		$html = $formula->render();

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

		$out = $this->getOutput();
		$request = $this->getRequest();
		$user = $this->getUser();
		Loop::handleLoopRequest( $out, $request, $user ); #handle editmode

		$out->setPageTitle ( $this->msg ( 'loopformulas-specialpage-title' ) );
		$html = self::renderLoopFormulaSpecialPage();
		$out->addHtml ( $html );

	}

	public static function renderLoopFormulaSpecialPage() {
	    global $wgParserConf, $wgLoopNumberingType;

	    $html = '<h1>';
	    $html .= wfMessage( 'loopformulas-specialpage-title' )->text();
	    $html .= '</h1>';

	    $loopStructure = new LoopStructure();
	    $loopStructure->loadStructureItems();

	    $parserFactory = MediaWikiServices::getInstance()->getParserFactory();
        $parser = $parserFactory->create();
	    $parserOptions = new ParserOptions();
	    $parser->getOptions ( $parserOptions );

	    $formulas = array ();
	    $structureItems = $loopStructure->getStructureItems();
	    $glossaryItems = LoopGlossary::getGlossaryPages();
	    $formula_number = 1;
	    $articleIds = array();
	    $html .= '<table class="table table-hover list_of_objects">';
	    $formula_tags = LoopObjectIndex::getObjectsOfType ( 'loop_formula' );

	    foreach ( $structureItems as $structureItem ) {
	        $articleIds[ $structureItem->article ] = NS_MAIN;
	    }
	    foreach ( $glossaryItems as $glossaryItem ) {
	        $articleIds[ $glossaryItem->mArticleID ] = NS_GLOSSARY;
	    }

	    foreach ( $articleIds as $article => $ns ) {

	        $article_id = $article;

	        if ( isset( $formula_tags[$article_id] ) ) {
	            foreach ( $formula_tags[$article_id] as $formula_tag ) {
	                $formula = new LoopFormula();
	                $formula->init($formula_tag ["thumb"], $formula_tag ["args"]);

	                $formula->parse();
	                if ( $wgLoopNumberingType == "chapter" ) {
	                    $formula->setNumber ( $formula_tag["nthoftype"] );
	                } elseif ( $wgLoopNumberingType == "ongoing" ) {
	                    $formula->setNumber ( $formula_number );
	                    $formula_number ++;
	                }
	                $formula->setArticleId ( $article_id );

	                $html .= $formula->renderForSpecialpage ( $ns );
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

