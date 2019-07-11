<?php

use MediaWiki\MediaWikiServices;

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
		
		$figure = new LoopFigure();
		$figure->init($input, $args, $parser, $frame);
		$figure->parse();
		$html = $figure->render();
		
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
						$this->setTitleFullyParsed($this->extraParse( $subtag [1] ), false);
					} else {
						$this->setTitle($this->mParser->stripOuterParagraph ( $this->getParser()->recursiveTagParse ( $subtag [1] ) ));
					}
					break;
				case 'loop_figure_description' :
					if ($fullparse == true) {
						$this->setDescriptionFullyParsed($this->extraParse( $subtag [1] ), false);
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
	public function renderForSpecialpage( $ns = null ) {
		global $wgLoopObjectNumbering;

		$html = '<tr scope="row" class="ml-1 pb-3">';
		$html .= '<td scope="col" class="pl-0 pr-0">';
		
		if ( $this->mFile && wfLocalFile( $this->mFile ) ) {

			$file = wfLocalFile( $this->mFile );
			$thumb = $file->transform( array (
					'width' => 120,
					'height' => 100 
			) );
			$html .= $thumb->toHtml( array (
				'desc-link' => false
			) );
		} 
		$html .= '</td>';
		$numberText = '';
		if ( $wgLoopObjectNumbering == 1 ) {
			if ( $ns == NS_MAIN || !isset( $ns ) ) {
				$loopStructure = new LoopStructure();
				$loopStructure->loadStructureItems();
				$lsi = LoopStructureItem::newFromIds ( $this->mArticleId );
				
				$previousObjects = LoopObjectIndex::getObjectNumberingsForPage ( $lsi, $loopStructure );
				if ( $lsi ) {
					$pageData = array( "structure", $lsi, $loopStructure );
					$numberText = " " . LoopObject::getObjectNumberingOutput($this->mId, $pageData, $previousObjects);
				}
			} elseif ( $ns == NS_GLOSSARY ) {
				$pageData = array( "glossary", $this->mArticleId );
				$previousObjects = LoopObjectIndex::getObjectNumberingsForGlossaryPage ( $pageData );
				$numberText = " " . LoopObject::getObjectNumberingOutput( $this->mId, $pageData, $previousObjects);
			}
		}
		$outputTitle = '';

		if ( $this->getTitleFullyParsed() ) {
			$outputTitle = $this->getTitleFullyParsed();
		} elseif ( $this->getTitle() ) {
			$outputTitle = $this->getTitle();
		}
		$html .= '<td scope="col" class="pl-1 pr-1"><span class="font-weight-bold">'. wfMessage ( $this->getTag().'-name-short' )->inContentLanguage ()->text () . $numberText . ': ' . '</span></td>';
		$html .= '<td scope="col" class=" "><span class="font-weight-bold">'. preg_replace ( '!(<br)( )?(\/)?(>)!', ' ', $outputTitle ) . '</span><br/><span>';
	
		if ($this->mDescriptionFullyParsed) {
			$html .= preg_replace ( '!(<br)( )?(\/)?(>)!', ' ', $this->getDescriptionFullyParsed() ) . '<br/>';
		} elseif ($this->mDescription) {
			$html .= preg_replace ( '!(<br)( )?(\/)?(>)!', ' ', $this->getDescription() ) . '<br/>';
		} 
		$linkTitle = Title::newFromID ( $this->getArticleId () );
		$linkTitle->setFragment ( '#' . $this->getId () );
		
		$lsi = LoopStructureItem::newFromIds ( $this->getArticleId () ); 
		if ($lsi) {
			$linktext = $lsi->tocNumber . ' ' . $lsi->tocText;
			
			$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
			$html .= $linkRenderer->makeLink( 
				$linkTitle, 
				new HtmlArmor( $linktext ),
				array()
				) . '<br/>';
		} elseif ( $ns == NS_GLOSSARY ) {
			$linktext = wfMessage( 'loop-glossary-namespace' )->text() . ': ' . $linkTitle->mTextform;
			
			$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
			$html .= $linkRenderer->makeLink( 
				$linkTitle, 
				new HtmlArmor( $linktext ),
				array()
				) . '<br/>';
		}
		$html .= '</span></td>';
		$html .= '</tr>';
		return $html;
	}
	

}

/**
 * Display list of figures for current structure
 * 
 * @author vorreitm, krohnden
 *        
 */
class SpecialLoopFigures extends SpecialPage {
	
	public function __construct() {
		parent::__construct ( 'LoopFigures' );
	}
	
	public function execute($sub) {
		global $wgParserConf, $wgLoopNumberingType;
		
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
		
		$loopStructure = new LoopStructure();
		$loopStructure->loadStructureItems();
	
		$parser = new Parser ( $wgParserConf );
		$parserOptions = ParserOptions::newFromUser ( $this->getUser () );
		$parser->Options ( $parserOptions );		
		
		$figures = array ();
		$structureItems = $loopStructure->getStructureItems();
		$glossaryItems = LoopGlossary::getGlossaryPages();
		$figure_number = 1;
		$articleIds = array();
		$out->addHtml ( '<table class="table table-hover list_of_figures list_of_objects">' );
		$figure_tags = LoopObjectIndex::getObjectsOfType ( 'loop_figure' );
		
		foreach ( $structureItems as $structureItem ) {
			$articleIds[ $structureItem->article ] = NS_MAIN;
		}
		foreach ( $glossaryItems as $glossaryItem ) {
			$articleIds[ $glossaryItem->mArticleID ] = NS_GLOSSARY;
		}

		foreach ( $articleIds as $article => $ns ) {
			$article_id = $article;
			if ( isset( $figure_tags[$article_id] ) ) {
				foreach ( $figure_tags[$article_id] as $figure_tag ) {
					$figure = new LoopFigure();
					$figure->init($figure_tag["thumb"], $figure_tag["args"]);
					$figure->parse();
					$figure->setNumber ( $figure_tag["nthoftype"] );
					$figure->setArticleId ( $article_id );

					preg_match('/:{1}(.{1,}\.[a-z0-9]{2,4})[]{2}|\|{1}]/i', $figure_tag["thumb"], $thumbFile); # File names after [[file:FILENAME.PNG]] up until ] or | (i case of |alignment or size)
					if (isset($thumbFile[1])) {
						$figure->setFile($thumbFile[1]);
					}
					$out->addHtml ( $figure->renderForSpecialpage ( $ns ) );
				}
			}
		}

		$out->addHtml ( '</table>' );
	}
	protected function getGroupName() {
		return 'loop';
	}
}

