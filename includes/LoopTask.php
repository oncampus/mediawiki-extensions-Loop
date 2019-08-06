<?php
/**
 * @description A parser extension that adds the tag <loop_task> to mark content as task and provide a table of tasks
 * @ingroup Extensions
 * @author Marc Vorreiter @vorreiter <marc.vorreiter@th-luebeck.de>
 * @author Dennis Krohn @krohnden <dennis.krohn@th-luebeck.de>
 */
if ( !defined( 'MEDIAWIKI' ) ) {
    die( "This file cannot be run standalone.\n" );
}
class LoopTask extends LoopObject{
	
	public static $mTag = 'loop_task';
	public static $mIcon = 'task';

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
	public static function renderLoopTask($input, array $args, $parser, $frame) {
		
		$task = new LoopTask();
		$task->init($input, $args, $parser, $frame);
		$task->parse();
		$html = $task->render();

		return  $html ;		
	}
	
	


}

/**
 * Display list of tasks for current structure
 * 
 * @author vorreitm, krohnden
 *        
 */
class SpecialLoopTasks extends SpecialPage {
	
	public function __construct() {
		parent::__construct ( 'LoopTasks' );
	}
	
	public function execute($sub) {
	    
		$out = $this->getOutput ();
		$out->setPageTitle ( $this->msg ( 'looptasks-specialpage-title' ) );
		$html = self::renderLoopTaskSpecialPage();
		$out->addHtml ( $html );
		
	}
	
	public static function renderLoopTaskSpecialPage() {
	    global $wgParserConf, $wgLoopNumberingType;
	    
	    $html = '<h1>';
	    $html .= wfMessage( 'looptasks-specialpage-title' )->text();
	    $html .= '</h1>';
	    
	    $loopStructure = new LoopStructure();
	    $loopStructure->loadStructureItems();
	    
	    $parser = new Parser ( $wgParserConf );
	    $parserOptions = new ParserOptions();
	    $parser->Options ( $parserOptions );
	    
	    $tasks = array ();
	    $structureItems = $loopStructure->getStructureItems();
	    $glossaryItems = LoopGlossary::getGlossaryPages();
	    $task_number = 1;
	    $articleIds = array();
	    $html .= '<table class="table table-hover list_of_objects">';
	    $task_tags = LoopObjectIndex::getObjectsOfType ( 'loop_task' );
	    
	    foreach ( $structureItems as $structureItem ) {
	        $articleIds[ $structureItem->article ] = NS_MAIN;
	    }
	    foreach ( $glossaryItems as $glossaryItem ) {
	        $articleIds[ $glossaryItem->mArticleID ] = NS_GLOSSARY;
	    }
	    
	    foreach ( $articleIds as $article => $ns ) {
	        
	        $article_id = $article;
	        
	        if ( isset( $task_tags[$article_id] ) ) {
	            foreach ( $task_tags[$article_id] as $task_tag ) {
	                
	                $task = new LoopTask();
	                $task->init($task_tag["thumb"], $task_tag["args"]);
	                
	                $task->parse();
	                if ( $wgLoopNumberingType == "chapter" ) {
	                    $task->setNumber ( $task_tag["nthoftype"] );
	                } elseif ( $wgLoopNumberingType == "ongoing" ) {
	                    $task->setNumber ( $task_number );
	                    $task_number ++;
	                }
	                $task->setArticleId ( $article_id );
	                
	                $html .= $task->renderForSpecialpage ( $ns );
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

