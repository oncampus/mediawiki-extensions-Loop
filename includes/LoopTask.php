<?php
/**
 * A parser extension that adds the tag <loop_task> to mark content as task and provide a table of tasks
 *
 * @ingroup Extensions
 *
 */
class LoopTask extends LoopObject{
	
	public static $mTag = 'loop_task';
	public static $mIcon = 'list';

	/**
	 * {@inheritDoc}
	 * @see LoopObject::getShowNumber()
	 */
	public function getShowNumber() {
		global $wgLoopTaskNumbering;
		return $wgLoopTaskNumbering;
	}
	
	/**
	 * {@inheritDoc}
	 * @see LoopObject::getDefaultRenderOption()
	 */
	public function getDefaultRenderOption() {
		global $wgLoopTaskDefaultRenderOption;
		return $wgLoopTaskDefaultRenderOption;
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
		try {
			$task = new LoopTask();
			$task->init($input, $args, $parser, $frame);
			$task->parse();
			$html = $task->render();
		} catch ( LoopException $e ) {
			$parser->addTrackingCategory( 'loop-tracking-category-loop-error' );
			$html = "$e";
		}
		return  $html ;		
	}
	
	


}

/**
 * Display list of tasks for current structure
 * 
 * @author vorreitm
 *        
 */
class SpecialLoopTasks extends SpecialPage {
	
	public function __construct() {
		parent::__construct ( 'LoopTasks' );
	}
	
	public function execute($sub) {
		global $wgParserConf, $wgParser, $wgUser;
		
		$config = $this->getConfig ();
		$request = $this->getRequest ();
		
		$out = $this->getOutput ();
		
		$out->setPageTitle ( $this->msg ( 'looptasks-specialpage-title' ) );
		
		$out->addHtml ( '<h1>' );
		$out->addWikiMsg ( 'looptasks-specialpage-title' );
		$out->addHtml ( '</h1>' );
		
		Loop::handleLoopRequest ();
		
		//$structure = LoopStructures::getCurrentLoopStructure ( $this->getUser () );
		$structure = new LoopStructure;
		$structure->getId ();
		
		$parser = new Parser ( $wgParserConf );
		$parserOptions = ParserOptions::newFromUser ( $this->getUser () );
		$parser->Options ( $parserOptions );		
		
		$out->addHtml ( '<table>' );
		$tasks = array ();
		$items = $structure->getItems ();
		$task_number = 1;
		foreach ( $items as $item ) {
			
			$article_id = $item->getArticle ();
			$title = Title::newFromID ( $article_id );
			$rev = Revision::newFromTitle ( $title );
			$content = $rev->getContent ();
			$task_tags = array ();
			
			$parser->clearState();
			$parser->setTitle ( $title );
			
			
			$marked_tasks_text = $parser->extractTagsAndParams ( array (
					'loop_task',
					'nowiki' 
			), $content->getWikitextForTransclusion (), $task_tags );
			
			foreach ( $task_tags as $task_tag ) {
				if ($task_tag [0] == 'loop_task') {
					$task = new LoopTask();
					$task->init($task_tag [1], $task_tag [2]);
					
					$task->parse(true);
					$task->setNumber ( $task_number );
					$task->setArticleId ( $article_id );
					
					$out->addHtml ( $task->renderForSpecialpage () );
					$task_number ++;
				}
			}
		}
		$out->addHtml ( '</table>' );
	}
	protected function getGroupName() {
		return 'loop';
	}
}

