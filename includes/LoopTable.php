<?php
#TODO MW 1.35 DEPRECATION
/**
 * @description A parser extension that adds the tag <loop_table> to mark content as table and provide a table of tables
 * @ingroup Extensions
 * @author Marc Vorreiter @vorreiter <marc.vorreiter@th-luebeck.de>
 * @author Dennis Krohn @krohnden <dennis.krohn@th-luebeck.de>
 */
if ( !defined( 'MEDIAWIKI' ) ) {
    die( "This file cannot be run standalone.\n" );
}
class LoopTable extends LoopObject{

	public static $mTag = 'loop_table';
	public static $mIcon = 'table';

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
	public static function renderLoopTable($input, array $args, $parser, $frame) {

		$table = new LoopTable();
		$table->init($input, $args, $parser, $frame);
		$table->parse();
		$html = $table->render();

		return  $html ;
	}
}

/**
 * Display list of tables for current structure
 *
 * @author vorreitm, krohnden
 *
 */
class SpecialLoopTables extends SpecialPage {

	public function __construct() {
		parent::__construct ( 'LoopTables' );
	}

	public function execute($sub) {

		$out = $this->getOutput();
		$request = $this->getRequest();
		$user = $this->getUser();
		Loop::handleLoopRequest( $out, $request, $user ); #handle editmode

		$out->setPageTitle ( $this->msg ( 'looptables-specialpage-title' ) );
		$html = self::renderLoopTableSpecialPage();
		$out->addHtml ( $html );

	}

	public static function renderLoopTableSpecialPage() {
	    global $wgParserConf, $wgLoopNumberingType;

	    $html = '<h1>';
	    $html .= wfMessage( 'looptables-specialpage-title' );
	    $html .= '</h1>';

	    $loopStructure = new LoopStructure();
	    $loopStructure->loadStructureItems();

	    $parser = new Parser ( $wgParserConf );
	    $parserOptions = new ParserOptions();
	    $parser->Options ( $parserOptions );

	    $tables = array ();
	    $structureItems = $loopStructure->getStructureItems();
	    $glossaryItems = LoopGlossary::getGlossaryPages();
	    $table_number = 1;
	    $articleIds = array();
	    $html .= '<table class="table table-hover list_of_objects">';
	    $table_tags = LoopObjectIndex::getObjectsOfType ( 'loop_table' );

	    foreach ( $structureItems as $structureItem ) {
	        $articleIds[ $structureItem->article ] = NS_MAIN;
	    }
	    foreach ( $glossaryItems as $glossaryItem ) {
	        $articleIds[ $glossaryItem->mArticleID ] = NS_GLOSSARY;
	    }

	    foreach ( $articleIds as $article => $ns ) {

	        $article_id = $article;

	        if ( isset( $table_tags[$article_id] ) ) {
	            foreach ( $table_tags[$article_id] as $table_tag ) {

	                $table = new LoopTable();
	                $table->init($table_tag["thumb"], $table_tag["args"]);

	                $table->parse();
	                if ( $wgLoopNumberingType == "chapter" ) {
	                    $table->setNumber ( $table_tag["nthoftype"] );
	                } elseif ( $wgLoopNumberingType == "ongoing" ) {
	                    $table->setNumber ( $table_number );
	                    $table_number ++;
	                }
	                $table->setArticleId ( $article_id );

	                $html .= $table->renderForSpecialpage ( $ns );
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

