<?php
/**
 * A parser extension that adds the tag <loop_figure> to mark content as figure and provide a table of figures
 *
 * @ingroup Extensions
 *
 */
class LoopFigure extends LoopObject{

	public static $mTag = 'loop_figure';
	public static $mIcon = 'figure';
	
	public $mFile;
	
	/**
	 * {@inheritDoc}
	 * @see LoopObject::getShowNumber()
	 */
	public function getShowNumber() {
		global $wgLoopFigureNumbering;
		return $wgLoopFigureNumbering;
	}
	
	/**
	 * {@inheritDoc}
	 * @see LoopObject::getDefaultRenderOption()
	 */
	public function getDefaultRenderOption() {
		global $wgLoopFigureDefaultRenderOption;
		return $wgLoopFigureDefaultRenderOption;
	}
	
	/**
	 * Set the filename
	 * @param string $file 
	 */
	public function setFile($file) {
		$this->mFile = $file;
	}	
	
	/**
	 * Get the filename
	 * @return string Filename
	 */
	public function getFile() {
		return $this->mFile;
	}

	/**
	 *
	 * @param string $input        	
	 * @param array $args        	
	 * @param Parser $parser        	
	 * @param Frame $frame        	
	 * @return string
	 */
	public static function renderLoopFigure($input, array $args, $parser, $frame) {
		try {
			$figure = new LoopFigure();
			$figure->init($input, $args, $parser, $frame);
			$figure->parse();
			$html = $figure->render();
		} catch ( LoopException $e ) {
				$parser->addTrackingCategory( 'loop-tracking-category-loop-error' );
				$html = "$e";
		}
		return  $html ;			
	}
	
	/**
	 * Parse the given Parameters and subtags
	 * @param bool $fullparse
	 */
	public function parse($fullparse = false) {

		$this->preParse($fullparse);
		
		$matches = array ();
		$subtags = array (
				'loop_figure_title',
				'loop_figure_description'
		);
		
		$text = $this->getParser()->extractTagsAndParams ( $subtags, $this->getInput(), $matches );
		
		foreach ( $matches as $marker => $subtag ) {
			switch ($subtag [0]) {
				case 'loop_figure_title' :
					if ($fullparse == true) {
						$this->setTitleFullyParsed($this->extraParse( $subtag [1] ));
					} else {
						$this->setTitle($this->mParser->stripOuterParagraph ( $this->getParser()->recursiveTagParse ( $subtag [1] ) ));
					}
					break;
				case 'loop_figure_description' :
					if ($fullparse == true) {
						$this->setDescriptionFullyParsed($this->extraParse( $subtag [1] ));
					} else {
						$this->setDescription($this->mParser->stripOuterParagraph ( $this->getParser()->recursiveTagParse ( $subtag [1] ) ));
					}
					break;
			}
		}

		$striped_text = $this->getParser()->killMarkers ( $text );
		$this->setInput($striped_text);
		
		
		$pattern = '@src="(.*?)"@';
		$this->setContent($this->getParser()->recursiveTagParse ( $this->getInput()) );
		$file_found = preg_match ( $pattern, $this->getContent(), $matches );
		if ($matches) {
			$tmp_src = $matches [1];
			$tmp_src_array = explode ( '/', $tmp_src );
			if (isset ( $tmp_src_array [7] )) {
				$filename = $tmp_src_array [7];
			} elseif (isset ( $tmp_src_array [6] )) { 
				$filename = $tmp_src_array [6];
			} else {
				$filename = "";
				# TODO loop exception
			}
			$filename = urldecode ( $filename );
			$this->setFile($filename);
		}
		
		if (preg_match ( '<div class="float-left">', $this->getContent(), $float_matches ) === 1) {
			$this->setAlignment('left');
		} elseif (preg_match ( '<div class="float-right">', $this->getContent(), $float_matches ) === 1) {
			$this->setAlignment('right');
		}		
		
	}
	
	
	/**
	 * Render loop_figure for list of figures
	 * 
	 * @return string
	 */
	public function renderForSpecialpage() {
		global $wgUser;

		//$structure = LoopStructures::getCurrentLoopStructure ( $wgUser );
		$structure = new LoopStructure;
		$structure->getId ();
		
		$html = '';
		$html .= '<tr>';
		$html .= '<td>';
		if ($this->mFile) {
			$file = wfLocalFile ( $this->mFile );
			$thumb = $file->transform ( array (
					'width' => 100,
					'height' => 100 
			) );
			$html .= $thumb->toHtml ( array (
					'desc-link' => true 
			) );
		} else {
			$html .= '<br/><span class="ic ic-'.$this->getIcon().'" aria-hidden="true"></span><br/>';
		}
		$html .= '</td>';
		$html .= '<td>';
		
		
		$html .= wfMessage ( $this->getTag().'-name' )->inContentLanguage ()->text () . ' ' . $this->getNumber() . ': ' . preg_replace ( '!(<br)( )?(\/)?(>)!', ' ', $this->getTitleFullyParsed() ) . '<br/>';
		if ($this->mDescription) {
			$html .= preg_replace ( '!(<br)( )?(\/)?(>)!', ' ', $this->getDescriptionFullyParsed() ) . '<br/>';
		}
		$linkTitle = Title::newFromID ( $this->getArticleId () );
		$linkTitle->setFragment ( '#' . $this->getId () );
		
		$lsi = LoopStructureItem::newFromIds ( $this->getArticleId (), $structure->getId () ); // $wgLoopCurrentStructure
		if ($lsi) {
			$linktext = $lsi->getTocNumber () . ' ' . $lsi->getTocText ();
			
			$html .= Linker::link ( $linkTitle, $linktext ) . '<br/>';
		}
		$html .= '</td>';
		$html .= '</tr>';
		return $html;
	}
	

}

/**
 * Display list of figures for current structure
 * 
 * @author vorreitm
 *        
 */
class SpecialLoopFigures extends SpecialPage {
	
	public function __construct() {
		parent::__construct ( 'LoopFigures' );
	}
	
	public function execute($sub) {
		global $wgParserConf, $wgParser, $wgUser;
		
		$config = $this->getConfig ();
		$request = $this->getRequest ();
		
		$out = $this->getOutput ();
		
		$out->addModuleStyles( [ 'ext.math.styles' ] );
		$mathmode = MathHooks::mathModeToString( $this->getUser()->getOption( 'math' ) );
		if ( $mathmode == 'mathml' ) {
			$out->addModuleStyles( [ 'ext.math.desktop.styles' ] );
			$out->addModules( [ 'ext.math.scripts' ] );
		}
		
		$out->setPageTitle ( $this->msg ( 'loopfigures-specialpage-title' ) );
		
		$out->addHtml ( '<h1>' );
		$out->addWikiMsg ( 'loopfigures-specialpage-title' );
		$out->addHtml ( '</h1>' );
		
		Loop::handleLoopRequest ();
		
		//$structure = LoopStructures::getCurrentLoopStructure ( $this->getUser () );
		$structure = new LoopStructure;
		$structure->getId ();
	
		$parser = new Parser ( $wgParserConf );
		$parserOptions = ParserOptions::newFromUser ( $this->getUser () );
		$parser->Options ( $parserOptions );		
		
		$out->addHtml ( '<table>' );
		$figures = array ();
		$items = $structure->getItems ();
		$figure_number = 1;
		foreach ( $items as $item ) {
			
			$article_id = $item->getArticle ();
			$title = Title::newFromID ( $article_id );
			$rev = Revision::newFromTitle ( $title );
			$content = $rev->getContent ();
			$figure_tags = array ();
			
			$parser->clearState();
			$parser->setTitle ( $title );
			
			
			$marked_figures_text = $parser->extractTagsAndParams ( array (
					'loop_figure',
					'nowiki' 
			), $content->getWikitextForTransclusion (), $figure_tags );
			
			foreach ( $figure_tags as $figure_tag ) {
				if ($figure_tag [0] == 'loop_figure') {
					$figure = new LoopFigure();
					$figure->init($figure_tag [1], $figure_tag [2]);
					
					$figure->parse(true);
					$figure->setNumber ( $figure_number );
					$figure->setArticleId ( $article_id );
					
					$out->addHtml ( $figure->renderForSpecialpage () );
					$figure_number ++;
				}
			}
		}
		$out->addHtml ( '</table>' );
	}
	protected function getGroupName() {
		return 'loop';
	}
}

