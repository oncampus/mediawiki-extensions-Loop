<?php 
use MediaWiki\MediaWikiServices;
use MediaWiki\Logger\LoggerFactory;

class LoopObject {
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
	public $mIndexing;
	
	public $mRenderOption;
	public $mAlignment;
	
	public $mContent;
	public static $mObjectTypes = array (
			'loop_figure',
			'loop_table',
			'loop_media',
			'loop_listing',
			'loop_formula',
			'loop_task'
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
	public static $mIndexingOptions = array (
			'true',
			'false'
	);	
	public static $mShowCopyrightOptions = array (
			'true',
			'false'
	);	
	/**
	 * Register the loop object tags hook
	 * @param Parser $parser
	 * @return boolean
	 */
	public static function onParserSetup(Parser $parser) {
		$parser->setHook ( 'loop_figure', 'LoopFigure::renderLoopFigure' );
		$parser->setHook ( 'loop_table', 'LoopTable::renderLoopTable' );
		$parser->setHook ( 'loop_media', 'LoopMedia::renderLoopMedia' );
		$parser->setHook ( 'loop_formula', 'LoopFormula::renderLoopFormula' ); # todo
		$parser->setHook ( 'loop_task', 'LoopTask::renderLoopTask' );
		$parser->setHook ( 'loop_listing', 'LoopListing::renderLoopListing' );
		
		$parser->setHook ( 'loop_title', 'LoopObject::renderLoopTitle' );
		$parser->setHook ( 'loop_description', 'LoopObject::renderLoopDescription' );
		$parser->setHook ( 'loop_copyright', 'LoopObject::renderLoopCopyright' );
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
		global $wgLoopObjectNumbering;

		LoggerFactory::getInstance( 'LoopObject' )->debug( 'Start rendering' );


		$html = '';
		
	
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
		
		if (isset($this->error) ){
			$html .= $this->error;
		} 
	
		$html .= '<div class="loop_object_content">';

		$html .= $this->getContent();
		
		$html .= '</div>';
			
		if ( $this->getRenderOption() != 'none' ) {
			$html .= '<div class="loop_object_footer">';
			$html .= '<div class="loop_object_title">';
			if ($this->getRenderOption() == 'icon') {
				$html .= '<span class="loop_object_icon"><span class="ic ic-'.$this->getIcon().'"></span>&nbsp;</span>';
			}
			if (($this->getRenderOption() == 'icon') || ($this->getRenderOption() == 'marked')) {
				$html .= '<span class="loop_object_name">'.wfMessage ( $this->getTag().'-name-short' )->inContentLanguage ()->text () . '</span>';
			}
			#dd($this->mIndexing,  $this->getId());
			if (($this->getShowNumber()) && (($this->getRenderOption() == 'icon') || ($this->getRenderOption() == 'marked')) && $this->mIndexing ) {
				$html .= '<span class="loop_object_number"> '.LOOPOBJECTNUMBER_MARKER_PREFIX . $this->getTag() . $this->getId() . LOOPOBJECTNUMBER_MARKER_SUFFIX;
				$html .= '</span>';
			}
			if (($this->getRenderOption() == 'icon') || ($this->getRenderOption() == 'marked')) {
				$html .= '<span class="loop_object_title_seperator">:&nbsp;</span><wbr>';
			}
			if ($this->getRenderOption() != 'none' && $this->getTitleFullyParsed()) {
				$html .= '<span class="loop_object_title_content">'.$this->getTitleFullyParsed().'</span>';
			} elseif ($this->getRenderOption() != 'none' && $this->getTitle()) {
				$html .= '<span class="loop_object_title_content">'.$this->getTitle().'</span>';
			}
			$html .= '</div>';
				
			if ($this->getDescriptionFullyParsed()  && (($this->getRenderOption() == 'icon') || ($this->getRenderOption() == 'marked'))) {
				$html .= '<div class="loop_object_description">' . $this->getDescriptionFullyParsed() . '</div>';
			} elseif ($this->getDescription()  && (($this->getRenderOption() == 'icon') || ($this->getRenderOption() == 'marked'))) {
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
		global $wgLoopObjectNumbering, $wgLoopNumberingType;
		
		$objectClass = get_class( $this );
		switch ( $objectClass ) {
			case "LoopFormula":
				$type = 'loop_formula';
				break;
			case "LoopListing":
				$type = 'loop_listing';
				break;
			case "LoopMedia":
				$type = 'loop_media';
				break;
			case "LoopTable":
				$type = 'loop_table';
				break;
			case "LoopTask":
				$type = 'loop_task';
				break;
		}
		#dd($numbering, $type);
		$numberText = '';
		if ( $wgLoopObjectNumbering == true ) {
		
			$lsi = LoopStructureItem::newFromIds ( $this->mArticleId );
				
			if ( $lsi ) {
					
				$loopStructure = new LoopStructure();
				$loopStructure->loadStructureItems();
				$previousObjects = LoopObjectIndex::getObjectNumberingsForPage ( $lsi, $loopStructure );
				
				if ( $lsi ) {
					$numberText = " " . LoopObject::getObjectNumberingOutput($this->mId, $lsi, $loopStructure, $previousObjects);
				}
					
			}
		}
		$outputTitle = '';
		if ($this->mTitleFullyParsed) {
			$outputTitle = $this->getTitleFullyParsed();
		} elseif ($this->mTitle) {
			$outputTitle = $this->getTitle();
		}

		$html = '<tr scope="row" class="ml-1 pb-3">';
		#$html .= '<span class="ic ic-'.$this->getIcon().'"></span> ';
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
		}
		$html .= '</span></td>';
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
		global $wgOut, $wgParserConf;
		$user = $wgOut->getUser();
		$this->setInput($input);
		$this->setArgs($args);
				
		if ($parser == false) {
			$parser = new Parser ( $wgParserConf );
			$parserOptions = ParserOptions::newFromUser ( $user );
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
	 * Set indexing
	 * @param bool $indexing
	 */
	public function setIndexing($indexing) {
		$this->mIndexing = $indexing;
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
	public function extraParse($wikiText, $recursive = false) {
		$localParser = new Parser ();
		if ( ! $recursive ) {
			global $wgTitle, $wgOut;
			$user = $wgOut->getUser();
			$localParserOptions = ParserOptions::newFromUser ( $user );
			$result = $localParser->parse ( $wikiText, $wgTitle, $localParserOptions );
		} else {
			$result = $localParser->recursiveTagParse ( $wikiText, $this->GetFrame() );
		}
		$result->clearWrapperDivClass();
		return $localParser->stripOuterParagraph($result->getText ());	
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
			$e = new LoopException( wfMessage( 'loopobject-error-unknown-renderoption', $renderoption, implode( ', ', self::$mRenderOptions ) )->text() );
			$this->getParser()->addTrackingCategory( 'loop-tracking-category-error' );
			$this->error = $e;
		}
		
		try {
			if ($alignment = $this->GetArg('align')) {
				$this->setAlignment(htmlspecialchars($alignment));
			} else {
				$this->setAlignment('none');
			}		
			
			if ( ! in_array ( $this->getAlignment(), self::$mAlignmentOptions ) ) {
				global $wgParser, $wgFrame;
				$this->setAlignment('none');
				throw new LoopException( wfMessage( 'loopobject-error-unknown-alignmentoption',$alignment, implode( ', ', self::$mAlignmentOptions ) )->text() );
				
			}	
		} catch ( LoopException $e ) {
			$this->getParser()->addTrackingCategory( 'loop-tracking-category-error' );
			$this->error = $e;
		}
		
		if ($title = $this->GetArg('title')) {
			$this->setTitle(htmlspecialchars($title));
		}
		
		if ($description = $this->GetArg('description')) {
			$this->setDescription(htmlspecialchars($description));
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
				$e = new LoopException( wfMessage( 'loopobject-error-unknown-showcopyrightoption', $showcopyright, implode( ', ', self::$mShowCopyrightOptions ) )->text() );
				$this->getParser()->addTrackingCategory( 'loop-tracking-category-error' );
				$this->error = $e;
		}

		if ($this->GetArg('index')) {
			$indexing = htmlspecialchars($this->GetArg('index'));
		} else {
			$indexing = 'true';
		}
		
		switch ($indexing) {
			case 'true':
				$this->setIndexing(true);
				break;
			case 'false':
				$this->setIndexing(false);
				break;
			default:
				$this->setIndexing(true);
				$e = new LoopException( wfMessage( 'loopobject-error-unknown-indexoption', $indexing, implode( ', ', self::$mIndexingOptions ) )->text() );
				$this->getParser()->addTrackingCategory( 'loop-tracking-category-error' );
				$this->error = $e;
				break;
		}
		
		if ($copyright = $this->GetArg('copyright')) {
			$this->setCopyright(htmlspecialchars($copyright));
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
					#if ($fullparse == true) {
						$this->setTitleFullyParsed($this->extraParse( $subtag [1], false ));
					#} else {
						$this->setTitle($this->mParser->stripOuterParagraph ( $this->mParser->recursiveTagParse ( $subtag [1] ) ));
					#}
					break;
				case 'loop_description' :
					#if ($fullparse == true) {
						$this->setDescriptionFullyParsed($this->extraParse( $subtag [1], false ));
					#} else {
						$this->setDescription($this->mParser->stripOuterParagraph ( $this->mParser->recursiveTagParse ( $subtag [1] ) ));
					#}
					break;
				case 'loop_copyright' :
					#if ($fullparse == true) {
						$this->setCopyrightFullyParsed($this->extraParse( $subtag [1], false ));
					#} else {
						$this->setCopyright($this->mParser->stripOuterParagraph ( $this->mParser->recursiveTagParse ( $subtag [1] ) ));
					#}
					break;
			}
		}
		#$striped_text = $this->getParser()->killMarkers ( $text );
	}
	/**
	 * Adds objects to db after edit
	 */
	public static function onPageContentSaveComplete( $wikiPage, $user, $content, $summary, $isMinor, $isWatch, $section, &$flags, $revision, $status, $baseRevId, $undidRevId ) {
		
		$title = $wikiPage->getTitle();
		if ( $title->getNamespace() == NS_MAIN ) {
				
			# on edit, delete all objects of that page from db. 
			$loopObjectIndex = new LoopObjectIndex();
			$loopObjectIndex->removeAllPageItemsFromDb($title->getArticleID());
			$contentText = ContentHandler::getContentText( $content );
			$parser = new Parser();
			
			# check if loop_object in page content
			$has_object = false;
			foreach (self::$mObjectTypes as $objectType) {
				if ((substr_count ( $contentText, $objectType ) >= 1)) {
					$has_object = true;
					break;
				}
			}
			if ( $has_object ) {
				$objects = array();
				foreach (self::$mObjectTypes as $objectType) {
					$objects[$objectType] = 0;
				}
				$object_tags = array ();
				$extractTags = array_merge(self::$mObjectTypes, array('nowiki'));
				$parser->extractTagsAndParams( $extractTags, $contentText, $object_tags );
				$newContentText = $contentText;
				foreach ( $object_tags as $object ) {
					
					$tmpLoopObjectIndex = new LoopObjectIndex();
					if ( ! isset ( $object[2]["index"] ) || $object[2]["index"] != "false" ) {
						$objects[$object[0]]++;
					}
					$tmpLoopObjectIndex->index = $object[0];
					$tmpLoopObjectIndex->nthItem = $objects[$object[0]];
					
					$tmpLoopObjectIndex->pageId = $title->getArticleID();
					
					
					if ( $object[0] == "loop_figure" ) {
						$tmpLoopObjectIndex->itemThumb = $object[1];
					}
					if ( $object[0] == "loop_media" && isset( $object[2]["type"] ) ) {
						$tmpLoopObjectIndex->itemType = $object[2]["type"];
					}
					if ( isset( $object[2]["title"] ) ) {
						$tmpLoopObjectIndex->itemTitle = $object[2]["title"];
					}
					if ( isset( $object[2]["description"] ) ) {
						$tmpLoopObjectIndex->itemDescription = $object[2]["description"];
					}
					if ( isset( $object[2]["id"] ) ) {
						
						if ( $tmpLoopObjectIndex->checkDublicates( $object[2]["id"] ) ) {
							$tmpLoopObjectIndex->refId = $object[2]["id"];
						} else {
							# dublicate id must be replaced
							$newRef = uniqid();
							$newContentText = preg_replace('/(id="'.$object[2]["id"].'")/', 'id="'.$newRef.'"'  , $newContentText, 1 );
							$tmpLoopObjectIndex->refId = $newRef; 
						}
					} else {
						# create new id
						$newRef = uniqid();
						$newContentText = self::setReferenceId( $newContentText, $newRef ); 
						$tmpLoopObjectIndex->refId = $newRef; 
					}
					if ( ! isset ( $object[2]["index"] ) || $object[2]["index"] != strtolower("false") ) {
						$tmpLoopObjectIndex->addToDatabase();
					}
				}
				$lsi = LoopStructureItem::newFromIds ( $title->getArticleID() );
				
				if ( $lsi ) {
					self::removeStructureCache( $title );
				}
				if ( $contentText !== $newContentText ) {
					#dd($contentText , $newContentText);
					$content = $content->getContentHandler()->unserializeContent( $newContentText );
					$content = $content->updateRedirect	( $title );
					$wikiPage->doEditContent ( $content, $summary, $flags, false, $user );
				}
			}
		}
	}

	/**
	 * Adds id to object tags if there is none
	 * @param string $text
	 * @param string $id
	 */
	public static function setReferenceId( $text, $id ) {
		$changedText = false;
		$text = mb_convert_encoding("<?xml version='1.0' encoding='utf-8'?>\n<div>" .$text.'</div>', 'HTML-ENTITIES', 'UTF-8');
		
		$dom = new DOMDocument("1.0", 'utf-8');
		@$dom->loadHTML( $text, LIBXML_HTML_NODEFDTD );
		
		$xpath = new DOMXPath( $dom );
		
		$objectTags = array();
		foreach (self::$mObjectTypes as $objectTag) {
			$objectTags[] = '//'.$objectTag;
		}
		$query = implode(' | ', $objectTags);
		
		$nodes = $xpath->query( $query );
		$changed = false;
		foreach ( $nodes as $node ) {
			$existingId = $node->getAttribute( 'id' );
			if( ! $existingId ) {
				$node->setAttribute('id', $id );
				$changed = true;
				$changedText = mb_substr($dom->saveHTML(), 55, -21);
				$decodedText = html_entity_decode($changedText);
				#dd($changedText, $decodedText);
				return $decodedText;
				break;
			}
		}
	}

	/**
	 * Updates pagetouched data for all pages after given title, or all if there is no title
	 * @param Title $title
	 */
	public static function removeStructureCache($title = null) {
		// Reset Cache for following pages in every LoopStructure
		$cond = "";
		// if a title is given, each page after given one is updated
		if ( isset($title) ) {
			# $cond = "lsi_article=" . $title->getArticleID(); # TODO remove? Für referenzen müssen alle Seiten immer neu geladen werden.
		} 

		$dbr = wfGetDB ( DB_SLAVE );
		$article_ids = array ();
		$structuresResult = $dbr->select ( array (
				'loop_structure_items'
		), array (
				'lsi_structure',
				'lsi_sequence'
		),  $cond,
		 __METHOD__ 
		);

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
					'page_touched' => $dbw->timestamp()
			), array (
					0 => 'page_id in (' . implode ( ',', $article_ids ) . ')'
			), __METHOD__ );
		}
		return true;
	}

	/**
	 * Replace object number marker with the correct numbering according to loop structure
	 * @param Parser $parser
	 * @param string $text
	 */
	
	public static function onParserAfterTidy(&$parser, &$text) {
		
		global $wgLoopObjectNumbering;
		$title = $parser->getTitle();
		$article = $title->getArticleID();
		
		$count = array();
		foreach (self::$mObjectTypes as $objectType) {
			$count[$objectType] = 0; 
		}
		$lsi = LoopStructureItem::newFromIds ( $article );
		
		if ( $lsi ) {
			
			$loopStructure = new LoopStructure();
			$loopStructure->loadStructureItems();
			$previousObjects = LoopObjectIndex::getObjectNumberingsForPage ( $lsi, $loopStructure );
			#$allObjects = LoopObjectIndex::getAllObjects( $loopStructure );
			
		}
		foreach ( self::$mObjectTypes as $objectType ) {
			
			$matches = array();
			preg_match_all( "/(" . LOOPOBJECTNUMBER_MARKER_PREFIX . $objectType . ")(.*)(" . LOOPOBJECTNUMBER_MARKER_SUFFIX . ")/", $text, $matches );
			
			if ( $lsi && $wgLoopObjectNumbering == 1 ) {
				
				$i = 0;
				foreach ( $matches[0] as $objectmarker ) {
					$objectid = $matches[2][$i];
					$numbering = self::getObjectNumberingOutput($objectid, $lsi, $loopStructure, $previousObjects);
					
					$text = preg_replace ( "/" . $objectmarker . "/", $numbering, $text );
					$i++;
				} 

			} else {
				foreach ( $matches[0] as $objectmarker ) {
					$text = preg_replace ( "/" . $objectmarker . "/", "", $text );
				}
			}	
		}
		
		return true;
	}

	public static function getObjectNumberingOutput($objectid, $lsi, $loopStructure, $previousObjects = null, $objectData = null ) {

		global $wgLoopNumberingType;
		if ( $previousObjects == null ) {
			$previousObjects = LoopObjectIndex::getObjectNumberingsForPage ( $lsi, $loopStructure );
		}
		if ( $objectData == null ) {
			$objectData = LoopObjectIndex::getObjectData($objectid, $loopStructure);
		}

		if ( $objectData["refId"] == $objectid ) {
			if ( $wgLoopNumberingType == "chapter" ) {

				preg_match('/(\d+)\.{0,1}/', $lsi->tocNumber, $tocChapter);

				if (isset($tocChapter[1])) {
					$tocChapter = $tocChapter[1];
				}
				if ( empty( $tocChapter ) ) {
					$tocChapter = 0;
				}
				return $tocChapter . "." . ( $previousObjects[$objectData["index"]] + $objectData["nthoftype"] );
				
			} elseif ( $wgLoopNumberingType == "ongoing" ) {
				
				return ( $previousObjects[$objectData["index"]] + $objectData["nthoftype"] );
					
			} 
		}
	}
}
?>