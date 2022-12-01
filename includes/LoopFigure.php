<?php
/**
 * @description A parser extension that adds the tag <loop_figure> to mark content as figure and provide a table of figures
 * @ingroup Extensions
 * @author Marc Vorreiter @vorreiter <marc.vorreiter@th-luebeck.de>
 * @author Dennis Krohn @krohnden <dennis.krohn@th-luebeck.de>
 */
if ( !defined( 'MEDIAWIKI' ) ) die ( "This file cannot be run standalone.\n" );

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
	 */
	public function parse() {

		$this->preParse();

		$matches = array ();
		$subtags = array (
				'loop_figure_title',
				'loop_figure_description'
		);

		$text = $this->getParser()->extractTagsAndParams ( $subtags, $this->getInput(), $matches );

		foreach ( $matches as $marker => $subtag ) {
			switch ($subtag [0]) {
				case 'loop_figure_title' :
					$this->setTitle($this->mParser->stripOuterParagraph ( $this->getParser()->recursiveTagParse ( $subtag [1] ) ));
					break;
				case 'loop_figure_description' :
					$this->setDescription($this->mParser->stripOuterParagraph ( $this->getParser()->recursiveTagParse ( $subtag [1] ) ));
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
		global $wgLoopObjectNumbering, $wgLoopObjectDefaultRenderOption;

		$html = '<tr scope="row" class="ml-1 pb-3">';
		$html .= '<td scope="col" class="pl-0 pr-0 loop-listofobjects-image">';
		$localRepo = MediaWikiServices::getInstance()->getRepoGroup()->getLocalRepo();
		if ( $this->mFile && $localRepo->newFile( $this->mFile ) ) {

			$file = $localRepo->newFile( $this->mFile );
			$thumb = $file->transform( array (
					'width' => 120,
					'height' => 100
			) );
			$html .= $thumb->toHtml( array (
				'desc-link' => false,
				'img-class' => 'responsive-image'
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

		if ( $wgLoopObjectDefaultRenderOption == "marked" ) {
			$html .= '<td scope="col" class="pl-1 pr-1 loop-listofobjects-type text-right">';
			$html .= '<span class="font-weight-bold">';
			$html .= wfMessage ( $this->getTag().'-name-short' )->inContentLanguage ()->text () . $numberText . ': ';
			$html .= '</span></td>';
		}

		$html .= '<td scope="col" class="loop-listofobjects-data"><span class="font-weight-bold">';#. preg_replace ( '!(<br)( )?(\/)?(>)!', ' ', htmlspecialchars_decode( $this->getTitle() ) ) . '</span><br/>';

		#$html .= '<td scope="col" class="loop-listofobjects-data"><span class="font-weight-bold">'. preg_replace ( '!(<br)( )?(\/)?(>)!', ' ', htmlspecialchars_decode( $this->getTitle() ) ) . '</span><br/>';

		$linkTitle = Title::newFromID ( $this->getArticleId () );
		if ($this->getTitle()) {
			$parserOutput = $this->getParser()->parse(  preg_replace ( '!(<br)( )?(\/)?(>)!', ' ', htmlspecialchars_decode( htmlspecialchars_decode( $this->getTitle() ) ) ), $linkTitle, $this->getParser()->getOptions(), $this->GetFrame() );
			$parserOutput->clearWrapperDivClass();
			$html .= $this->getParser()->stripOuterParagraph( $parserOutput->getText() ) . '</span><br/>';
		}
		$linkTitle->setFragment ( '#' . $this->getId () );

		$lsi = LoopStructureItem::newFromIds ( $this->getArticleId () );
		$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
		$linkRenderer->setForceArticlePath(true);
		if ($lsi) {
			$linktext = $lsi->tocNumber . ' ' . $lsi->tocText;

			$html .= $linkRenderer->makeLink(
				$linkTitle,
				new HtmlArmor( $linktext ),
				array()
				) . '<br/>';
		} elseif ( $ns == NS_GLOSSARY ) {
			$linktext = wfMessage( 'loop-glossary-namespace' )->text() . ': ' . $linkTitle->getText();
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
		$out = $this->getOutput();
		$request = $this->getRequest();
		$user = $this->getUser();
		Loop::handleLoopRequest( $out, $request, $user ); #handle editmode

		$out->setPageTitle ( $this->msg ( 'loopfigures-specialpage-title' ) );
		$html = self::renderLoopFigureSpecialPage();
		$out->addHtml ( $html );
	}

	public static function renderLoopFigureSpecialPage() {
	    global $wgParserConf;

	    $html = '<h1>';
	    $html .= wfMessage( 'loopfigures-specialpage-title')->text();
	    $html .= '</h1>';

	    $loopStructure = new LoopStructure();
	    $loopStructure->loadStructureItems();

	    $parserFactory = MediaWikiServices::getInstance()->getParserFactory();
        $parser = $parserFactory->create();

	    $structureItems = $loopStructure->getStructureItems();
	    $glossaryItems = LoopGlossary::getGlossaryPages();
	    $articleIds = array();
	    $html .= '<table class="table table-hover list_of_figures list_of_objects">';
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

	                if ( !empty( $thumbFile ) ) {
						$pattern = '@src="(.*?)"@';
						$parsedInput = $parser->recursiveTagParse ( $thumbFile );
						$file_found = preg_match ( $pattern, $parsedInput, $matches );
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
							$figure->setFile($filename);
						}
	                }
	                $html .= $figure->renderForSpecialpage ( $ns );
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

