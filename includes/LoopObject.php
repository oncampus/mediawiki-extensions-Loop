<?php
/**
 * @description Renders LOOP objects
 * @ingroup Extensions
 * @author Marc Vorreiter @vorreiter <marc.vorreiter@th-luebeck.de>
 * @author Dennis Krohn @krohnden <dennis.krohn@th-luebeck.de>
 */
if ( !defined( 'MEDIAWIKI' ) ) die ( "This file cannot be run standalone.\n" );

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
	public $mTitleInput;

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
		$parser->setHook ( 'loop_formula', 'LoopFormula::renderLoopFormula' );
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
		global $wgLoopObjectDefaultRenderOption;
		return $wgLoopObjectDefaultRenderOption;
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
		$showNumbering = true;
		$floatclass = '';
		if ($this->getAlignment()=='left') {
			$floatclass = 'float-left';
		} elseif ($this->getAlignment()=='right') {
			$floatclass = 'float-right';
		}
		$html = '<div ';
		if ( $this->getId() ) {

			$html .= 'id="' . $this->getId() . '" ';
			$object = LoopObjectIndex::getObjectData( $this->getId() );
			$articleId = $this->getParser()->getTitle()->getArticleID();

			# objects with render=none are not numbered as it would lead to confusion
			if ( !$object && $this->getRenderOption() == "none" ) {
				$showNumbering = false;
			} elseif ( is_array( $object ) ) {
				if ( htmlspecialchars_decode( $this->mTitleInput ) != htmlspecialchars_decode( $object["title"] )
				|| $articleId != $object["articleId"]
				|| $this->getTag() != $object["index"] ) {
					#if there are hints for this element has a dublicate id, don't render the number and add an error
					$otherTitle = Title::newFromId( $object["articleId"] );
					if (! isset( $this->error ) ){
						$this->error = "";
					}
					$textform = "-";
					if ( !is_null( $otherTitle ) && $otherTitle->getArticleId() != $articleId ) {
						$textform = $otherTitle->getText();
						$e = new LoopException( wfMessage( 'loopobject-error-dublicate-id', $this->getId(), $textform )->text() );
						$this->getParser()->addTrackingCategory( 'loop-tracking-category-error' );
						$this->error .= $e . "\n";
					} else {
						$lsi = LoopStructureItem::newFromIds($articleId);
						if ( $lsi ) {
							$title = $this->getParser()->getTitle();
							$latestRevId = $title->getLatestRevID();
							$fwp = new FlaggableWikiPage ( $title );

							if ( isset($fwp) ) {
								$stableRevId = $fwp->getStable();

								if ( $latestRevId == $stableRevId && $stableRevId != null ) {
									# the id is not in db
									$e = new LoopException( wfMessage( 'loopobject-error-unknown-id', $this->getId() )->text() );
									$this->getParser()->addTrackingCategory( 'loop-tracking-category-error' );
									$this->error .= $e . "\n";
								}
							}
						}
					}


					$showNumbering = false;
				}
			}
		}
		$html .= 'class="loop_object '.$this->getTag().' '.$floatclass.' loop_object_render_'.$this->getRenderOption().'"';
		$html .= '>';

		if ( isset( $this->error ) ) {
			$html .= $this->error;
		}

		$content = '<div class="loop_object_content">';
		$content .= $this->getContent();

		$content .= '</div>';

		$footer = '';
		if ( $this->getRenderOption() != 'none' ) {
			$footer .= '<div class="loop_object_footer">';
			$footer .= '<div class="loop_object_title">';
				# icon
				if ( $this->getRenderOption() == 'icon' || $this->getRenderOption() == 'marked' ) {
					$footer .= '<span class="loop_object_icon"><span class="ic ic-'.$this->getIcon().'"></span>&nbsp;</span>';
				}
				# type and object number
				if ( $this->getRenderOption() == 'marked' ) {
					$footer .= '<span class="loop_object_name">'.wfMessage ( $this->getTag().'-name-short' )->inContentLanguage ()->text () . '</span>';
					if ( $showNumbering && $this->mIndexing ) {
						$footer .= '<span class="loop_object_number"> '.LOOPOBJECTNUMBER_MARKER_PREFIX . $this->getTag() . $this->getId() . LOOPOBJECTNUMBER_MARKER_SUFFIX;
						$footer .= '</span>';
					}
				}
				# Seperator
				if ( $this->getRenderOption() == 'marked' && !empty ( $this->getTitle() ) ) {
					$footer .= '<span class="loop_object_title_seperator">:&nbsp;</span><wbr>';
				}
				# user-entered title
				if ( $this->getTitle() ) {
					$footer .= '<span class="loop_object_title_content">'.$this->getTitle().'</span>';
				}
			$footer .= '</div>';

			if ( $this->getRenderOption() != 'title' ) {
				if ( $this->getDescription() ) {
					$footer .= '<div class="loop_object_description">' . htmlspecialchars_decode( $this->getDescription() ) . '</div>';
				}
				if ( $this->getCopyright() ) {
					$footer .= '<div class="loop_object_copyright">' . $this->getCopyright() . '</div>';
				}
			}

			$footer .= '</div>';
		}

		if ( $this->getTag() == "loop_task" ) {
			$html .= $footer . $content;
		} else {
			$html .= $content . $footer;
		}
		$html .= '</div>';

		return $html;
	}

	/**
	 * Render loop object for list of objects
	 *
	 * @return string
	 */
	public function renderForSpecialpage( $ns ) {
		global $wgLoopObjectNumbering, $wgLoopObjectDefaultRenderOption;
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
				$previousObjects = LoopObjectIndex::getObjectNumberingsForGlossaryPage ( $this->mArticleId );
				$numberText = " " . LoopObject::getObjectNumberingOutput( $this->mId, $pageData, $previousObjects);
			}
		}
		$html = '<tr scope="row" class="ml-1 pb-3">';

		if ( $wgLoopObjectDefaultRenderOption == "marked" ) {
			$html .= '<td scope="col" class="pl-1 pr-1 loop-listofobjects-type">';
			$html .= '<span class="font-weight-bold">';
			$html .= wfMessage ( $this->getTag().'-name-short' )->inContentLanguage ()->text () . $numberText . ': ';
			$html .= '</span></td>';
		}
		$html .= '<td scope="col" class="loop-listofobjects-data"><span class="font-weight-bold">';
		if ( $type == 'loop_media' ) {
			$html .= '<span class="ic ic-'.$this->getIcon().'"></span> ';
		}

		$linkTitle = Title::newFromID ( $this->getArticleId () );
		if ($this->getTitle()) {
			$parserOutput = $this->getParser()->parse(  preg_replace ( '!(<br)( )?(\/)?(>)!', ' ', htmlspecialchars_decode( htmlspecialchars_decode( $this->getTitle() ) ) ), $linkTitle, $this->getParser()->getOptions(), $this->GetFrame() );
			$parserOutput->clearWrapperDivClass();
			$html .= $this->getParser()->stripOuterParagraph( $parserOutput->getText() ) . '</span><br/><span>';
		}
		$linkTitle->setFragment ( '#' . $this->getId () );

		$lsi = LoopStructureItem::newFromIds ( $this->getArticleId () );
		$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
		$linkRenderer->setForceArticlePath(true);
		if ( $lsi ) {
			global $wgLoopLegacyPageNumbering;

			$linktext = $wgLoopLegacyPageNumbering ? $lsi->tocNumber . ' ' . $lsi->tocText : $lsi->tocText;

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

	/**
	 * Init loop object
	 * @param string $input
	 * @param array $args
	 * @param Parser $parser
	 * @param PPFrame $frame
	 */
	public function init($input, array $args, $parser = false, $frame = false) {
		global $wgOut;
		$user = $wgOut->getUser();
		$this->setInput($input);
		$this->setArgs($args);

		if ($parser == false) {
			$parserFactory = MediaWikiServices::getInstance()->getParserFactory();
			$parser = $parserFactory->create();
			$parserOptions = ParserOptions::newFromUser ( $user );
			$parser->setOptions ( $parserOptions );
			$t = Title::newFromText ( 'NO TITLE' );
			$parser->setTitle ( $t );
			$parser->clearState ();
			$parser->mStripState = new StripState( $parser );
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
		$this->mTitleInput = $title;
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
	public static function localParse( $wikiText ) {
		global $wgOut;
		$parserFactory = MediaWikiServices::getInstance()->getParserFactory();
		$parser = $parserFactory->create();
		$user = $wgOut->getUser();
		$tmpTitle = Title::newFromText('NO_TITLE');
		$parserOptions = ParserOptions::newFromUser ( $user );
		$result = $parser->parse ( html_entity_decode( $wikiText ), $tmpTitle, $parserOptions );
		$result->clearWrapperDivClass();
		return $parser->stripOuterParagraph( $result->getText() );
	}

	/**
	 * Parse the given Parameters and subtags
	 */
	public function parse() {
		$this->preParse();

		# remove subtags ad they might interfere with rendering
		$text = $this->getParser()->killMarkers ( $this->getInput() );
		$subtags = array (
			'loop_title',
			'loop_description',
			'loop_copyright'
		);
		$text = $this->getParser()->extractTagsAndParams ( $subtags, $text, $matches );
		$text = $this->getParser()->killMarkers ( $text );
		#$this->setInput();

		$this->setContent($this->getParser()->recursiveTagParse($text,$this->GetFrame()) );
	}

	/**
	 * Parse common args
	 * Parse common subtags and strip them from input
	 */
	public function preParse() {

		$this->error = "";
		if ($id = $this->GetArg('id')) {
			$this->setId(htmlspecialchars($id));
		}

		// handle rendering option
		if ($renderoption = $this->GetArg('render')) {
			$this->setRenderOption(strtolower(htmlspecialchars($renderoption)));
		} else {
			$this->setRenderOption($this->getDefaultRenderOption());
		}
		if ($this->getRenderOption() == 'default') {
			$this->setRenderOption($this->getDefaultRenderOption());
		}
		if ( ! in_array ( $this->getRenderOption(), self::$mRenderOptions ) ) {
			$e = new LoopException( wfMessage( "loop-error-unknown-param", "<".$this->getTag().">", "render", $this->GetArg('render'), implode( ', ', self::$mRenderOptions ), $this->getDefaultRenderOption() )->text() );
			$this->setRenderOption($this->getDefaultRenderOption());

			$this->getParser()->addTrackingCategory( 'loop-tracking-category-error' );
			$this->error .= $e;
		}

		try {
			if ($alignment = $this->GetArg('align')) {
				$this->setAlignment(strtolower(htmlspecialchars($alignment)));
			} else {
				$this->setAlignment('none');
			}

			if ( ! in_array ( $this->getAlignment(), self::$mAlignmentOptions ) ) {
				$this->setAlignment('none');
				throw new LoopException( wfMessage( "loop-error-unknown-param", "<".$this->getTag().">", "align", $this->GetArg('align'), implode( ', ', self::$mAlignmentOptions ), 'none' )->text() );

			}
		} catch ( LoopException $e ) {
			$this->getParser()->addTrackingCategory( 'loop-tracking-category-error' );
			$this->error .= $e;
		}

		if ($title = $this->GetArg('title')) {
			$this->setTitle(htmlspecialchars($title));
		}

		if ($description = $this->GetArg('description')) {
			$this->setDescription($this->getParser()->recursiveTagParse(htmlspecialchars($description),$this->GetFrame()));
		}
		if ($this->GetArg('show_copyright')) {
			$showcopyright = strtolower(htmlspecialchars($this->GetArg('show_copyright')));
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
				$e = new LoopException( wfMessage( "loop-error-unknown-param", "<".$this->getTag().">", "show_copyright", $this->GetArg('show_copyright'), implode( ', ', self::$mShowCopyrightOptions  ), "false" )->text() );
				$this->getParser()->addTrackingCategory( 'loop-tracking-category-error' );
				$this->error = $e;
		}

		if ($this->GetArg('index')) {
			$indexing = strtolower(htmlspecialchars($this->GetArg('index')));
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
				$e = new LoopException( wfMessage( "loop-error-unknown-param", "<".$this->getTag().">", "index", $this->GetArg('index'), implode( ', ', self::$mIndexingOptions ), "true" )->text() );
				$this->getParser()->addTrackingCategory( 'loop-tracking-category-error' );
				$this->error = $e;
				break;
		}

		if ( $copyright = $this->GetArg('copyright') ) {
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
		$this->getParser()->extractTagsAndParams ( $subtags, $striped_text, $matches );
		foreach ( $matches as $marker => $subtag ) {
			switch ($subtag [0]) {
				case 'loop_title' :
						$this->setTitle($this->mParser->stripOuterParagraph ( $this->mParser->recursiveTagParse ( $subtag [1] ) ));
						$this->mTitleInput = $subtag [1];
					break;
				case 'loop_description' :
						$this->setDescription($this->mParser->stripOuterParagraph ( $this->mParser->recursiveTagParse ( $subtag [1] ) ));
					break;
				case 'loop_copyright' :
						$this->setCopyright($this->mParser->stripOuterParagraph ( $this->mParser->recursiveTagParse ( $subtag [1] ) ));
					break;
			}
		}
	}
	/**
	 * Custom hook called after stabilization changes of pages in FlaggableWikiPage->updateStableVersion()
	 * @param Title $title
	 * @param Content $content
	 */
	public static function onAfterStabilizeChange ( $title, $content, $userId ) {

		$latestRevId = $title->getLatestRevID();
		$wikiPage = MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle($title);
		$fwp = new FlaggableWikiPage ( $title );

		if ( isset($fwp) ) {
			$stableRevId = $fwp->getStable();

			if ( $latestRevId == $stableRevId || $stableRevId == null ) {
				$content = $wikiPage->getContent( MediaWiki\Revision\RevisionRecord::RAW );
				$contentText = ContentHandler::getContentText( $content );
				self::handleObjectItems( $wikiPage, $title, $contentText );
			}
		}
		return true;
	}
	/**
	 * Custom hook called after stabilization changes of pages in FlaggableWikiPage->clearStableVersion()
	 * @param Title $title
	 */
	public static function onAfterClearStable( $title ) {

		$wikiPage = MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle( $title );
		self::handleObjectItems( $wikiPage, $title );
		return true;
	}

	/**
	 * When deleting a page, remove all Object entries from DB.
	 * Attached to ArticleDeleteComplete hook.
	 */
	public static function onArticleDeleteComplete( &$article, User &$user, $reason, $id, $content, LogEntry $logEntry, $archivedRevisionCount ) {

		LoopObjectIndex::removeAllPageItemsFromDb ( $id );

		return true;
	}

	/**
	 * Adds objects to db. Called by onLinksUpdateConstructed and onAfterStabilizeChange (custom Hook)
	 * @param WikiPage $wikiPage
	 * @param Title $title
	 * @param String $contentText
	 */
	public static function handleObjectItems( &$wikiPage, $title, $contentText = null ) {
		if ( $wikiPage != null) {
			$content = $wikiPage->getContent();
			if ( $contentText == null) {
				if ( $wikiPage->getContent() != null ) {
					$content = $wikiPage->getContent( MediaWiki\Revision\RevisionRecord::RAW );
					$contentText = ContentHandler::getContentText( $content );
				} else {
					return '';
				}
			}
		}


		if ( $title->getNamespace() == NS_MAIN || $title->getNamespace() == NS_GLOSSARY ) {

			$parserFactory = MediaWikiServices::getInstance()->getParserFactory();
			$parser = $parserFactory->create();
			#$loopObjectIndex = new LoopObjectIndex();
			$fwp = new FlaggableWikiPage ( $title );
			$stableRevId = $fwp->getStable();
			$latestRevId = $title->getLatestRevID();
			$stable = false;
			if ( $stableRevId == $latestRevId ) {
				$stable = true;
				# on edit, delete all objects of that page from db.
				LoopObjectIndex::removeAllPageItemsFromDb ( $title->getArticleID() );
			}

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
				$forbiddenTags = array( 'nowiki', 'code', '!--', 'syntaxhighlight', 'source' ); # don't save ids when in here
				$extractTags = array_merge( self::$mObjectTypes, $forbiddenTags );
				$parser->extractTagsAndParams( $extractTags, $contentText, $object_tags );

				foreach ( $object_tags as $outter_object ) {
					if ( ! in_array( strtolower($outter_object[0]), $forbiddenTags ) ) { #exclude loop-tags that are in code or nowiki tags
						$parser->extractTagsAndParams( $extractTags, $outter_object[1], $items );
						$items[] = $outter_object;
						foreach ( $items as $object) {
							if ( ( ! isset ( $object[2]["index"] ) || $object[2]["index"] != strtolower("false") ) && isset( $objects[$object[0]] ) ) {
								$valid = true;
								$tmpLoopObjectIndex = new LoopObjectIndex();
								$objects[$object[0]]++;
								$tmpLoopObjectIndex->nthItem = $objects[$object[0]];
								$tmpLoopObjectIndex->index = $object[0];
								$tmpLoopObjectIndex->pageId = $title->getArticleID();

								if ( $object[0] == "loop_figure" ) {
									preg_match('/(.*)(\[\[.*\]\])(.*)/U', $object[1], $thumb);
									$tmpLoopObjectIndex->itemThumb = array_key_exists( 2, $thumb ) ? $thumb[2] : null;
								}
								if ( $object[0] == "loop_media" && isset( $object[2]["type"] ) ) {
									$tmpLoopObjectIndex->itemType = $object[2]["type"];
								}
								$title_tags = array ();
								$parser->extractTagsAndParams( ["loop_title", "loop_figure_title"], $object[1], $title_tags );
								if ( !empty( $title_tags ) ) {
									foreach( $title_tags as $tag ) {
										$tmpLoopObjectIndex->itemTitle = htmlspecialchars( $tag[1] );
										break;
									}
								} elseif ( isset( $object[2]["title"] ) ) {
									$tmpLoopObjectIndex->itemTitle = htmlspecialchars( $object[2]["title"] );
								}
								$desc_tags = array ();
								$parser->extractTagsAndParams( ["loop_description", "loop_figure_description"], $object[1], $desc_tags );
								if ( !empty( $desc_tags ) ) {
									foreach( $desc_tags as $tag ) {
										$tmpLoopObjectIndex->itemDescription =  htmlspecialchars( $tag[1] );
										break;
									}
								} elseif ( isset( $object[2]["description"] ) ) {
									$tmpLoopObjectIndex->itemDescription = htmlspecialchars(  $object[2]["description"] );
								}
								if ( isset( $object[2]["id"] ) ) {
									if ( $tmpLoopObjectIndex->checkDublicates( $object[2]["id"] ) ) {
										$tmpLoopObjectIndex->refId = $object[2]["id"];
									} else {
										# dublicate id!
										$valid = false;
										$objects[$object[0]]--;
									}
								} else {
									$valid = false;
								}

								#dd($tmpLoopObjectIndex, $valid, $tmpLoopObjectIndex->checkDublicates( $object[2]["id"] ) ); #debug, wird öfter mal gebraucht

								if ( $valid && $stable ) {
									# page is valid and stable
									if ( ! isset ( $object[2]["index"] ) ) {
										# no index set
										if ( ! isset ( $object[2]["render"] ) ) {
											# no index, no render -> save
											$tmpLoopObjectIndex->addToDatabase();
										} elseif ( strtolower( $object[2]["render"] ) != "none" ) {
											# no index but render is not none -> save
											$tmpLoopObjectIndex->addToDatabase();
										}
										# index defaults to true but if render=none, there is no indexing. unless it is explicitly said so in index=true

									} elseif ( strtolower($object[2]["index"]) != "false" ) {
										# index is set to true, basically. so index it no matter the render option
										$tmpLoopObjectIndex->addToDatabase();
									}
								}
							}
						}
					}
				}
				$lsi = LoopStructureItem::newFromIds ( $title->getArticleID() );

				if ( $lsi ) {
					self::updateStructurePageTouched( $title );
				} elseif ( $title->getNamespace() == NS_GLOSSARY ) {
					LoopGlossary::updateGlossaryPageTouched();
				}
			}
		}
		return true;
	}


	/**
	 * Updates pagetouched data for all pages after given title, or all if there is no title
	 * @param Title $title
	 */
	public static function updateStructurePageTouched($title = null) {
		// Reset Cache for following pages in every LoopStructure
		$cond = "";
		// if a title is given, each page after given one is updated
		if ( isset($title) ) {
			# $cond = "lsi_article=" . $title->getArticleID();
		}

		$dbr = wfGetDB ( DB_REPLICA );
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
			$dbw = wfGetDB ( DB_PRIMARY );

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

	public static function onParserAfterTidy( &$parser, &$text ) {

		global $wgLoopObjectNumbering;
		$title = $parser->getTitle();
		$article = $title->getArticleID();
		$showNumbers = true;
		if ( isset( $title->flaggedRevsArticle ) ) {
			$fwp = $title->flaggedRevsArticle;
			if ( $fwp->getRevisionRecord() ) {
				$revId = $fwp->getRevisionRecord()->getId();
				$stableId = $fwp->getStable();
				if ( $stableId != $revId && $stableId != null ) {
					$showNumbers = false;
				}
			}
		}
		$count = array();
		foreach (self::$mObjectTypes as $objectType) {
			$count[$objectType] = 0;
		}
		$lsi = LoopStructureItem::newFromIds ( $article );

		if ( $lsi ) {
			$loopStructure = new LoopStructure();
			$loopStructure->loadStructureItems();
			$previousObjects = LoopObjectIndex::getObjectNumberingsForPage ( $lsi, $loopStructure );

		} elseif ( $title->getNamespace() == NS_GLOSSARY ) {
			$previousObjects = LoopObjectIndex::getObjectNumberingsForGlossaryPage ( $article );
		}
		foreach ( self::$mObjectTypes as $objectType ) {

			$matches = array();
			preg_match_all( "/(" . LOOPOBJECTNUMBER_MARKER_PREFIX . $objectType . ")(.*)(" . LOOPOBJECTNUMBER_MARKER_SUFFIX . ")/U", $text, $matches );

			if ( $lsi && $wgLoopObjectNumbering == 1 && $showNumbers ) {

				$i = 0;
				foreach ( $matches[0] as $objectmarker ) {
					$objectid = $matches[2][$i];
					$pageData = array( "structure", $lsi, $loopStructure );
					$numbering = self::getObjectNumberingOutput($objectid, $pageData, $previousObjects);

					$text = preg_replace ( "/" . $objectmarker . "/", $numbering, $text );
					$i++;
				}

			} elseif ( $title->getNamespace() == NS_GLOSSARY && $wgLoopObjectNumbering == 1 && $showNumbers ) {
				$i = 0;
				foreach ( $matches[0] as $objectmarker ) {
					$objectid = $matches[2][$i];
					$pageData = array( "glossary", $article );
					$numbering = self::getObjectNumberingOutput( $objectid, $pageData, $previousObjects );
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

	/**
	 * Outputs the given object's numbering
	 * @param String $objectId
	 * @param Array $pageData = [
	 * 					0 => "structure" or "glossary"
	 * 					1 => LoopStructureItem or $articleId
	 * 					2 => LoopStructure or empty
	 * 					]
	 * @param Array $previousObjects
	 * @param Array $objectData
	 */
	public static function getObjectNumberingOutput($objectid, Array $pageData, $previousObjects = null, $objectData = null ) {

		global $wgLoopNumberingType;
		$typeOfPage = $pageData[0];

		if ( $previousObjects == null ) {
			if ( $typeOfPage == "structure" ) {
				$previousObjects = LoopObjectIndex::getObjectNumberingsForPage ( $pageData[1], $pageData[2] ); // $lsi, $loopStructure
			} else {
				$previousObjects = LoopObjectIndex::getObjectNumberingsForGlossaryPage ( $pageData[1] );
			}
		}
		if ( $objectData == null ) {
			$objectData = LoopObjectIndex::getObjectData( $objectid );
		}

		if ( is_array($objectData) ) {
			if ( $objectData["refId"] == $objectid ) {
				$tmpPreviousObjects = 0;

				if ( $wgLoopNumberingType == "chapter" && $typeOfPage == "structure" ) {

					$lsi = $pageData[1];

					preg_match('/(\d+)\.{0,1}/', $lsi->tocNumber, $tocChapter);

					if (isset($tocChapter[1])) {
						$tocChapter = $tocChapter[1];
					}
					if ( empty( $tocChapter ) ) {
						$tocChapter = 0;
					}
					if ( isset($previousObjects[$objectData["index"]]) ) {
						$tmpPreviousObjects = $previousObjects[$objectData["index"]];
					}
					return $tocChapter . "." . ( $tmpPreviousObjects + $objectData["nthoftype"] );

				} elseif ( $wgLoopNumberingType == "ongoing" || $typeOfPage == "glossary" ) {
					if ( isset($previousObjects[$objectData["index"]]) ) {
						$tmpPreviousObjects = $previousObjects[$objectData["index"]];
					}
					$prefix = '';
					if ( $typeOfPage == "glossary" ) {
						$prefix = wfMessage("loop-glossary-objectnumber-prefix")->text();
					}
					return $prefix . ( $tmpPreviousObjects + $objectData["nthoftype"] );

				}
			}
		}
	}
}
?>
