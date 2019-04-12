<?php 
class LoopObject {

	#const LOOPOBJECTNUMBER_MARKER_SUFFIX = "-QINU\x7f";
	#const LOOPOBJECTNUMBER_MARKER_PREFIX = "\x7fUNIQ--loopobjectnumber-";	
	# const now in extension callback
	
	public static $mTag;
	public static $mIcon;
	
	
	public $mInput;
	public $mArgs;
	public $mParser;	
	public $mFrame;
	
	public $mId;
	public $mArticleId;	
	public $mTitle;
	
	public $mTitleFullyParsed;
	public $mDescriptionFullyParsed;
	public $mCopyrightFullyParsed;
	
	public $mDescription;
	public $mCopyright;
	public $mNumber;
	
	public $mRenderOption;
	public $mAlignment;
	
	public $mContent;

	public static $mObjectTypes = array (
			'loop_figure',
	#		'loop_table',
	#		'loop_media',
	#		'loop_listing',
	#		'loop_formula',
	#		'loop_task'
		);
	
	public static $mRenderOptions=array(
			'none',
			'icon',
			'marked',
			'default',
			'title'
		);
	
	public static $mAlignmentOptions = array (
			'left',
			'right',
			'none'
	);	

	/**
	 * Register the loop object tags hook
	 * @param Parser $parser
	 * @return boolean
	 */
	public static function onParserSetup(Parser $parser) {
		$parser->setHook ( 'loop_figure', 'LoopFigure::renderLoopFigure' );
	#	$parser->setHook ( 'loop_table', 'LoopTable::renderLoopTable' );
	#	$parser->setHook ( 'loop_media', 'LoopMedia::renderLoopMedia' );
	#	$parser->setHook ( 'loop_formula', 'LoopFormula::renderLoopFormula' ); # todo
	#	$parser->setHook ( 'loop_task', 'LoopTask::renderLoopTask' );
	#	$parser->setHook ( 'loop_listing', 'LoopListing::renderLoopListing' );
		
		$parser->setHook ( 'loop_title', 'LoopObject::renderLoopTitle' );
		$parser->setHook ( 'loop_description', 'LoopObject::renderLoopDescription' );
		$parser->setHook ( 'loop_copyrightt', 'LoopObject::renderLoopCopyright' );
		return true;
	}	
	
	/**
	 * Dummy render for loop_title
	 * Real rendering is done in the respective object
	 * 
	 * @param string $input        	
	 * @param array $args        	
	 * @param Parser $parser        	
	 * @param Frame $frame        	
	 * @return string
	 */
	public static function renderLoopTitle($input, array $args, $parser, $frame) {
		return '';
	}
	
	/**
	 * Dummy render for loop_description
	 * Real rendering is done in the respective object
	 *
	 * @param string $input
	 * @param array $args
	 * @param Parser $parser
	 * @param Frame $frame
	 * @return string
	 */	
	public static function renderLoopDescription($input, array $args, $parser, $frame) {
		return '';
	}
	
	/**
	 * Dummy render for loop_copyright
	 * Real rendering is done in the respective object
	 *
	 * @param string $input
	 * @param array $args
	 * @param Parser $parser
	 * @param Frame $frame
	 * @return string
	 */	
	public static function renderLoopCopyright($input, array $args, $parser, $frame) {
		return '';
	}	

	/**
	 * Get the tag name
	 */
	public function getTag() {
		return static::$mTag;
	}
	
	/**
	 * Get the icon name
	 */
	public function getIcon() {
		return static::$mIcon;
	}	
	
	/**
	 * Returns whether the numbering is to be displayed
	 * @return bool
	 */
	public function getShowNumber() {
		return true;
	}
	
	/**
	 * Get the default render option
	 * @return string
	 */
	public function getDefaultRenderOption() {
		return 'marked';
	}	
	
	/**
	 * Render loop object for content
	 *
	 * @return string
	 */
	public function render() {
		global $wgLoopFigureNumbering;
	
		$floatclass = '';
		if ($this->getAlignment()=='left') {
			$floatclass = 'float-left';
		} elseif ($this->getAlignment()=='right') {
			$floatclass = 'float-right';
		}

		$html = '<div ';
		if ($this->getId()) {
			$html .= 'id="' . $this->getId() . '" ';
		}
		$html .= 'class="loop_object '.$this->getTag().' '.$floatclass.' loop_object_render_'.$this->getRenderOption().'"';
		$html .= '>';
	
		$html .= '<div class="loop_object_content">';
		$html .= $this->getContent();
		$html .= '</div>';
			
		if ($this->getRenderOption() != 'none') {
			$html .= '<div class="loop_object_footer">';
			$html .= '<div class="loop_object_title">';
			if ($this->getRenderOption() == 'icon') {
				$html .= '<span class="loop_object_icon"><span class="ic ic-'.$this->getIcon().'"></span>&nbsp;</span>';
			}
			if (($this->getRenderOption() == 'icon') || ($this->getRenderOption() == 'marked')) {
				$html .= '<span class="loop_object_name">'.wfMessage ( $this->getTag().'-name-short' )->inContentLanguage ()->text () . '</span>';
			}
			if (($this->getShowNumber()) && (($this->getRenderOption() == 'icon') || ($this->getRenderOption() == 'marked'))) {
				$html .= '<span class="loop_object_number">&nbsp; .LOOPOBJECTNUMBER_MARKER_PREFIX '. $this->getTag() . uniqid ()  . 'LOOPOBJECTNUMBER_MARKER_SUFFIX . </span>';
			}
			if (($this->getRenderOption() == 'icon') || ($this->getRenderOption() == 'marked')) {
				$html .= '<span class="loop_object_title_seperator">:&nbsp;</span><wbr>';
			}
			if ($this->getRenderOption() != 'none') {
				$html .= '<span class="loop_object_title_content">'.$this->getTitle().'</span>';
			}
			$html .= '</div>';
				
			if ($this->getDescription()  && (($this->getRenderOption() == 'icon') || ($this->getRenderOption() == 'marked'))) {
				$html .= '<div class="loop_object_description">' . $this->getDescription() . '</div>';
			}
			if ($this->getCopyright()  && (($this->getRenderOption() == 'icon') || ($this->getRenderOption() == 'marked'))) {
				$html .= '<div class="loop_object_copyright">' . $this->getCopyright() . '</div>';
			}
	
			$html .= '</div>';
		}
			
		$html .= '</div>';
	
		return $html;
	}	
	
	/**
	 * Render loop object for list of objects
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
		$html .= '<br/><span class="ic ic-'.$this->getIcon().'" aria-hidden="true"></span><br/>';
		$html .= '</td>';
		$html .= '<td>';
	
	
		$html .= wfMessage ( $this->getTag().'-name' )->inContentLanguage ()->text () . ' ' . $this->getNumber() . ': ' . preg_replace ( '!(<br)( )?(\/)?(>)!', ' ', $this->getTitleFullyParsed() ) . '<br/>';
		if ($this->getDescription()) {
			$html .= preg_replace ( '!(<br)( )?(\/)?(>)!', ' ', $this->getDescriptionFullyParsed() ) . '<br/>';
		}
		$linkTitle = Title::newFromID ( $this->getArticleId () );
		$linkTitle->setFragment ( '#' . $this->getId () );
	
		$lsi = LoopStructureItem::newFromIds ( $this->getArticleId (), $structure->getId () );
		if ($lsi) {
			$linktext = $lsi->getTocNumber () . ' ' . $lsi->getTocText ();
				
			$html .= Linker::link ( $linkTitle, $linktext ) . '<br/>';
		}
		$html .= '</td>';
		$html .= '</tr>';
		return $html;
	}	
	
	/**
	 * Init loop object
	 * @param string $input
	 * @param array $args
	 * @param Parser $parser
	 * @param PPFrame $frame
	 */
	public function init($input, array $args, $parser = false, $frame = false) {
		global $wgUser, $wgParserConf;
		
		$this->setInput($input);
		$this->setArgs($args);
				
		if ($parser == false) {
			$parser = new Parser ( $wgParserConf );
			$parserOptions = ParserOptions::newFromUser ( $wgUser );
			$parser->Options ( $parserOptions );
			$t = Title::newFromText ( 'NO TITLE' );
			$parser->setTitle ( $t );
			$parser->clearState ();
			$frame = $parser->getPreprocessor ()->newFrame ();
		}
		
		$this->setParser($parser);
		$this->setFrame($frame);
	}
	
	/**
	 * Set the input from a tag
	 * @param string $input
	 */
	public function setInput($input) {
		$this->mInput = $input;
	}	
	
	/**
	 * Set the args from a tag
	 * @param array $args
	 */
	public function setArgs($args) {
		$this->mArgs = $args;
	}	
	
	/**
	 * Set parser
	 * @param Parser $parser
	 */
	public function setParser($parser) {
		$this->mParser = $parser;
	}	
	
	/**
	 * Set frame
	 * @param PPFrame $frame
	 */
	public function setFrame($frame) {
		$this->mFrame = $frame;
	}	
	
	/**
	 * Set the id
	 * @param integer $id
	 */
	public function setId($id) {
		$this->mId = $id;
	}
	
	/**
	 * Set the article id the object belongs to
	 * @param integer $articleId
	 */
	public function setArticleId($articleId) {
		$this->mArticleId = $articleId;
	}	
	
	/**
	 * Set the title
	 * @param string $title
	 */
	public function setTitle($title) {
		$this->mTitle = $title;
	}
	
	/**
	 * Set the description
	 * @param string $description
	 */
	public function setDescription($description) {
		$this->mDescription = $description;
	}
	
	/**
	 * Set the copyright
	 * @param string $copyright
	 */
	public function setCopyright($copyright) {
		$this->mCopyright = $copyright;
	}

	/**
	 * Set show copyright
	 * @param bool $showcopyright
	 */
	public function setShowCopyright($showcopyright) {
		$this->mShowCopyright = $showcopyright;
	}	
	
	/**
	 * Set the fully parsed title
	 * @param string $title
	 */
	public function setTitleFullyParsed($title) {
		$this->mTitleFullyParsed = $title;
	}
	
	/**
	 * Set the fully parsed description
	 * @param string $description
	 */
	public function setDescriptionFullyParsed($description) {
		$this->mDescriptionFullyParsed = $description;
	}
	
	/**
	 * Set the fully parsed copyright
	 * @param string $copyright
	 */
	public function setCopyrightFullyParsed($copyright) {
		$this->mCopyrightFullyParsed = $copyright;
	}	
	
	/**
	 * Set the number
	 * @param integer $number
	 */
	public function setNumber($number) {
		$this->mNumber = $number;
	}

	/**
	 * Set the render option
	 * @param string $renderoption
	 */
	public function setRenderOption($renderoption) {
		$this->mRenderOption = $renderoption;
	}	
	
	/**
	 * Set the alignment option
	 * @param string $alignment
	 */
	public function setAlignment($alignment) {
		$this->mAlignment = $alignment;
	}	
	
	/**
	 * Set the parsed content
	 * @param string $content
	 */
	public function setContent($content) {
		$this->mContent = $content;
	}	
	
	/**
	 * Get the input from a tag
	 * @return string
	 */
	public function GetInput() {
		return $this->mInput;
	}
	
	/**
	 * Get the args from a tag
	 * @return array 
	 */
	public function GetArgs() {
		return $this->mArgs;
	}

	/**
	 * Get arg
	 * @return string|false
	 */
	public function GetArg($arg) {
		if (isset($this->mArgs[$arg])) {
			return $this->mArgs[$arg];
		} else {
			return false;
		}
	}	
	
	/**
	 * Get parser
	 * @return Parser
	 */
	public function GetParser() {
		return $this->mParser;
	}
	
	/**
	 * Get frame
	 * @return PPFrame
	 */
	public function GetFrame() {
		return $this->mFrame;
	}
	
	/**
	 * Get ID
	 * @return string
	 */
	public function getId() {
		return $this->mId;
	}
	
	/**
	 * Get the article id the object belongs to
	 * @return integer
	 */
	public function getArticleId() {
		return $this->mArticleId;
	}	
	
	/**
	 * Get the title
	 * @return string
	 */
	public function getTitle() {
		return $this->mTitle;
	}
	
	/**
	 * Get the description
	 * @return string
	 */
	public function getDescription() {
		return $this->mDescription;
	}
	
	/**
	 * Get the copyright
	 * @return string
	 */
	public function getCopyright() {
		return $this->mCopyright;
	}
	
	/**
	 * Get show copyright
	 * @return bool
	 */
	public function getShowCopyright() {
		return $this->mShowCopyright;
	}	
	
	/**
	 * Get the fully parsed title
	 * @return string
	 */
	public function getTitleFullyParsed() {
		return $this->mTitleFullyParsed;
	}
	
	/**
	 * Get the fully parsed description
	 * @return string
	 */
	public function getDescriptionFullyParsed() {
		return $this->mDescriptionFullyParsed;
	}
	
	/**
	 * Get the fully parsed copyright
	 * @return string
	 */
	public function getCopyrightFullyParsed() {
		return $this->mCopyrightFullyParsed;
	}	
	
	/**
	 * Get the number
	 * @return integer
	 */
	public function getNumber() {
		return $this->mNumber;
	}	

	/**
	 * Get the render option
	 * @return string
	 */
	public function getRenderOption() {
		return $this->mRenderOption;
	}	
	
	/**
	 * Get the alignment option
	 * @return string
	 */
	public function getAlignment() {
		return $this->mAlignment;
	}	
	
	/**
	 * Get the parsed content
	 * @return string
	 */
	public function getContent() {
		return $this->mContent;
	}	
	
	/**
	 * Fully parse wikitext with extra parser instance
	 * @param string $wikiText
	 * @return string
	 */
	public function extraParse($wikiText) {
		global $wgTitle, $wgUser;
		$myParser = new Parser ();
		$myParserOptions = ParserOptions::newFromUser ( $wgUser );
		$result = $myParser->parse ( $wikiText, $wgTitle, $myParserOptions );
		return $myParser->stripOuterParagraph($result->getText ());	
	}
	
	/**
	 * Parse the given Parameters and subtags
	 * @param bool $fullparse
	 */
	public function parse($fullparse = false) {
		$this->preParse($fullparse);
		$this->setContent($this->getParser()->recursiveTagParse($this->getInput(),$this->GetFrame()) );
	}
	
	/**
	 * Parse common args 
	 * Parse common subtags and strip them from input
	 * @param bool $fullparse complete parse for special pages
	 */
	public function preParse($fullparse = false) {
		
		if ($id = $this->GetArg('id')) {
			$this->setId(htmlspecialchars($id));
		}
		
		// handle rendering option
		if ($renderoption = $this->GetArg('render')) {
			$this->setRenderOption(htmlspecialchars($renderoption));
		} else {
			$this->setRenderOption('default');	
		}
		
		if ($this->getRenderOption() == 'default') {
			$this->setRenderOption($this->getDefaultRenderOption());
		}

		if ( ! in_array ( $this->getRenderOption(), self::$mRenderOptions ) ) {
			throw new LoopException( wfMessage( 'loopobject-error-unknown-renderoption', $this->getRenderOption(), implode( ', ', self::$mRenderOptions ) ) );
		}
		
		
		if ($alignment = $this->GetArg('align')) {
			$this->setAlignment(htmlspecialchars($alignment));
		} else {
			$this->setAlignment('none');
		}		
		
		if ( ! in_array ( $this->getAlignment(), self::$mAlignmentOptions ) ) {
			throw new LoopException( wfMessage( 'loopobject-error-unknown-alignmentoption', $this->getAlignment(), implode( ', ', self::$mAlignmentOptions ) ) );
		}		
		
		
		if ($title = $this->GetArg('title')) {
			$this->setTitle(htmlspecialchars($title));
			$this->setTitleFullyParsed(htmlspecialchars($title));
		}
		
		if ($description = $this->GetArg('description')) {
			$this->setDescription(htmlspecialchars($description));
			$this->setDescriptionFullyParsed(htmlspecialchars($description));
		}
		
		
		if ($this->GetArg('show_copyright')) {
			$showcopyright = htmlspecialchars($this->GetArg('show_copyright'));
		} else {
			$showcopyright = 'false';
		}
		
		switch ($showcopyright) {
			case 'true':
				$this->setShowCopyright(true);
				break;
			case 'false':
				$this->setShowCopyright(false);
				break;
			default:
				throw new LoopException( wfMessage( 'loopobject-error-unknown-showcopyrightoption', $showcopyright, array('true','false') ) );
		}
		
		if ($copyright = $this->GetArg('copyright')) {
			$this->setCopyright(htmlspecialchars($copyright));
			$this->setCopyrightFullyParsed(htmlspecialchars($copyright));
		}
		
		// strip other objects in the text to prevent mismatch for title, descrition and copyright
		$otherObjectTypes = array();
		foreach (self::$mObjectTypes as $objectType) {
			if ($objectType != self::$mTag) {
				$otherObjectTypes[]=$objectType;
			}
		}
		$otherObjectMatches = array();
		$text = $this->getParser()->extractTagsAndParams ( $otherObjectTypes, $this->getInput(), $otherObjectMatches );
		$striped_text = $this->getParser()->killMarkers ( $text );
		
		$matches = array ();
		$subtags = array (
				'loop_title',
				'loop_description',
				'loop_copyright'
		);
		$text = $this->getParser()->extractTagsAndParams ( $subtags, $striped_text, $matches );
		
		foreach ( $matches as $marker => $subtag ) {
			switch ($subtag [0]) {
				case 'loop_title' :
					if ($fullparse == true) {
						$this->setTitleFullyParsed($this->extraParse( $subtag [1] ));
					} else {
						$this->setTitle($this->mParser->stripOuterParagraph ( $this->mParser->recursiveTagParse ( $subtag [1] ) ));
					}
					break;
				case 'loop_description' :
					if ($fullparse == true) {
						$this->setDescriptionFullyParsed($this->extraParse( $subtag [1] ));
					} else {
						$this->setDescription($this->mParser->stripOuterParagraph ( $this->mParser->recursiveTagParse ( $subtag [1] ) ));
					}
					break;
				case 'loop_copyright' :
					if ($fullparse == true) {
						$this->setCopyrightFullyParsed($this->extraParse( $subtag [1] ));
					} else {
						$this->setCopyright($this->mParser->stripOuterParagraph ( $this->mParser->recursiveTagParse ( $subtag [1] ) ));
					}
					break;
			}
		}
		#$striped_text = $this->getParser()->killMarkers ( $text );
	}
	
	public static function onPageContentSaveComplete ( $wikiPage, $user, $content, $summary, $isMinor, $isWatch, $section, &$flags, $revision, $status, $baseRevId, $undidRevId ) { 
# bei jedem speichern datenbankeinträge über die medien



#global $wgLoopCurrentStructure;

		$loopStructure = new LoopStructure();
		$loopStructure->loadStructureItems();

		$title = $wikiPage->getTitle();
	
		#Loop::handleLoopRequest ();
	
		#$parser->getOptions ()->optionUsed ( 'loopstructure' );
	
		if ( $title->getNamespace() == NS_MAIN ) {
			#if ((! $parser->getOptions ()->getIsSectionPreview ()) && (! $parser->getOptions ()->getIsPreview ())) {
				
				// check if loop_object in page content
				$contentText = ContentHandler::getContentText( $content );
				#dd(ContentHandler::getContentText( $content ));

				$has_object = false;
				foreach (self::$mObjectTypes as $objectType) {
					if ((substr_count ( $contentText, $objectType ) >= 1)) {
						$has_object = true;
						break;
					}
				}
				#dd($has_object);
				if ( $has_object ) {
					$objects = array();
					foreach (self::$mObjectTypes as $objectType) {
						$objects[$objectType] = 0;
					}
					# 

					$lsi = LoopStructureItem::newFromIds ( $title->getArticleID() );
					
					if ( $lsi ) {
						/*
						while ( $previous_item = $lsi->getPreviousItem () ) {
							$article_id = $previous_item->getArticle ();
							$title = Title::newFromID ( $article_id );
							$rev = Revision::newFromTitle ( $title );
							$content = $rev->getContent ();
							

							$object_tags = array ();
							$wikitext = $content->getWikitextForTransclusion ();
							$extractTags = array_merge(self::$mObjectTypes, array('nowiki'));
							$marked_objects_text = $parser->extractTagsAndParams ( $extractTags, $wikitext, $object_tags );
							
							foreach ( $object_tags as $object_tag ) {
								foreach (self::$mObjectTypes as $objectType) {
									if ($object_tag[0] == $objectType) {
										$count[$objectType]++;
									}
								}

							}
							$lsi = $previous_item;
						}
						*/
						// Reset Cache for following pages in every LoopStructure
						$dbr = wfGetDB ( DB_SLAVE );
						$article_ids = array ();
						$structuresResult = $dbr->select ( array (
								'loop_structure_items'
						), array (
								'lsi_structure',
								'lsi_sequence'
						), array (
								"lsi_article=" . $title->getArticleID ()
						), __METHOD__ );
						foreach ( $structuresResult as $structureRow ) {
							$lsi_structure = $structureRow->lsi_structure;
							$lsi_sequence = $structureRow->lsi_sequence;
								
							$pagesResult = $dbr->select ( array (
									'loop_structure_items'
							), array (
									'lsi_article'
							), array (
									0 => "lsi_structure=" . $lsi_structure,
									1 => "lsi_sequence >= " . $lsi_sequence
							), __METHOD__ );
							foreach ( $pagesResult as $pageRow ) {
								$article_ids [] = $pageRow->lsi_article;
							}
						}
	

						// Update page_touched 
						if ( $article_ids ) {
							$article_ids = array_unique ( $article_ids );
							$dbw = wfGetDB ( DB_MASTER );
								
							$dbPageTouchedResult = $dbw->update ( 'page', array (
									'page_touched' => $dbw->timestamp ()
							), array (
									0 => 'page_id in (' . implode ( ',', $article_ids ) . ')'
							), __METHOD__ );
						}
						
						/* 
						foreach ( self::$mObjectTypes as $objectType ) {
							$matches = array ();
							preg_match_all ( "/(" . $LOOPOBJECTNUMBER_MARKER_PREFIX . $objectType . ")([a-z0-9]{13})(" . $LOOPOBJECTNUMBER_MARKER_SUFFIX . ")/", $text, $matches );
							
							$i = $count[$objectType] + 1;
							foreach ( $matches [0] as $objectmarker ) {
								$text = preg_replace ( "/" . $objectmarker . "/", $i++, $text );
							}							
							
						}
						*/

					}
				}
			#}
		}


		return true;
		
	}

	/**
	 * Replace object number marker with the correct numbering according to loop structure
	 * @param Parser $parser
	 * @param string $text
	 */
	
public static function onParserAfterTidy(&$parser, &$text) {
#bei jedem parsen numerierungen abrufen

	
	
		
		return true;
	}
}
?>