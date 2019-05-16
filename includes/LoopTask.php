<?php
/**
 * A parser extension that adds the tag <loop_task> to mark content as task and provide a table of tasks
 *
 * @ingroup Extensions
 *
 */
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
		global $wgParserConf, $wgLoopNumberingType;
		
		$config = $this->getConfig ();
		$request = $this->getRequest ();
		
		$out = $this->getOutput ();
		
		$out->setPageTitle ( $this->msg ( 'looptasks-specialpage-title' ) );
		
		$out->addHtml ( '<h1>' );
		$out->addWikiMsg ( 'looptasks-specialpage-title' );
		$out->addHtml ( '</h1>' );
		
		$loopStructure = new LoopStructure();
		$loopStructure->loadStructureItems();
		
		$parser = new Parser ( $wgParserConf );
		$parserOptions = ParserOptions::newFromUser ( $this->getUser () );
		$parser->Options ( $parserOptions );		
		
		$tasks = array ();
		$items = $loopStructure->getStructureItems();
		$task_number = 1;
		
		$out->addHtml ( '<table class="table table-hover list_of_objects">' );
		foreach ( $items as $item ) {
			
			$article_id = $item->getArticle ();
			$title = Title::newFromID ( $article_id );
			$rev = Revision::newFromTitle ( $title );
			$content = $rev->getContent ();
			$task_tags = array ();
			
			$parser->clearState();
			$parser->setTitle ( $title );
			
			$task_tags = LoopObjectIndex::getObjectsOfType ( 'loop_task' );
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
					
					$out->addHtml ( $task->renderForSpecialpage () );
				}
			}
		}
		$out->addHtml ( '</table>' );
	}

	protected function getGroupName() {
		return 'loop';
	}
}

