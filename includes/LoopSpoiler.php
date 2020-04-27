<?php
/**
  * @description Inline and block spoiler. <loop_spoiler> tag.
  * @author Dustin Neß <dustin.ness@th-luebeck.de>
  */

if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file cannot be run standalone.\n" );
}

class LoopSpoiler {
	
	public $mId;
	public $mType;
	public $mBtnText;
	public $mContent;
	public $mParser;
	public $mErrors;
	
	private static $mSpoilerTypes = [
		'default',
		'transparent'
	];

	public function setId( $id ) {
		$this->mId = $id;
	}

	public function setType( $type ) {
		$this->mType = $type;
	}
	
	public function setBtnText( $btn_text ) {
		$this->mBtnText = $btn_text;
	}
	
	public function setContent( $content ) {
		$this->mContent = $content;
	}
	
	public function getId() {
		return $this->mId;
	}
	
	public function getType() {
		return $this->mType;
	}
	
	public function getBtnText() {
		return $this->mBtnText;
	}
	
	public function getContent() {
		return $this->mContent;
	}

	public static function onParserSetup( Parser &$parser ) {
		$parser->setHook( 'spoiler', 'LoopSpoiler::renderLoopSpoiler' );
		$parser->setHook( 'loop_spoiler', 'LoopSpoiler::renderLoopSpoiler' ); // behalten?

		return true;
	}

	public static function renderLoopSpoiler( $input, array $args, Parser $parser, PPFrame $frame ) {
		$parser->getOutput()->addModules( 'loop.spoiler.js' );
		
		$spoiler = LoopSpoiler::newFromTag( $input, $args, $parser, $frame );
		
		$span = "";
		$parser->extractTagsAndParams( ["math"], $input, $math_tags );
		foreach ( $math_tags as $i => $math ) {
			$span .=  $parser->recursiveTagParseFully( "<math>".$math[1]."</math>", $frame );
		}
		$span = str_replace("<p>", "", $span);
		$span = str_replace("</p>", "", $span);
		$spoiler->btnAppend = $span;

		$spoiler->setContent( $parser->recursiveTagParseFully( $input, $frame ) );
		
		$return = "";
		if ( !empty ( $spoiler->mErrors ) ) {
			$return .= "$spoiler->mErrors";
		}
		$return .= $spoiler->render();

		return $return;
	}
	
	public function render() {
		$content = $this->getContent();
		while ( substr( $content, -1, 2 ) == "\n" ) { # remove newlines at the end of content for cleaner html output
			$content = substr( $content, 0, -1 );
		}
		# replace math uniq markers
		$content = preg_replace('/(\'"`UNIQ--postMath-\w{8}-QINU`"\')/', '<span class="loopspoiler_math_replace"></span>', $content);
		
		return Html::rawElement(
			'button',
			[ 'data-spoilercontent' => $content,'type' => 'button', 'id' => $this->getId(), 'class' => 'btn loopspoiler loopspoiler_type_' . $this->getType() . ' ' . $this->getId(),],
			$this->getBtnText() . $this->btnAppend 
		) ;
	}

	public static function newFromTag( $input, array $args, Parser $parser, PPFrame $frame ) {
		global $wgLoopSpoilerDefaultType;
		
		$spoiler = new LoopSpoiler();
		
		$spoiler->setId( uniqid() );
		
		// set spoiler type to standard if not submitted.
		if ( ! isset( $args['type'] ) ) {
			if( $wgLoopSpoilerDefaultType == null ) {
				$wgLoopSpoilerDefaultType = self::$mSpoilerTypes[0]; // default type: "default"
			}
			$spoiler->setType( $wgLoopSpoilerDefaultType ); # muss noch in extension.json/config gesetzt werden
		} else {
			$spoiler->setType( htmlspecialchars ( $args['type'] ) );
		}		
		// throw exception if spoiler type is not valid
		if ( !empty( $spoiler->getType() ) && !in_array ( $spoiler->getType(), self::$mSpoilerTypes ) ) {
			$spoiler->setType( htmlspecialchars ( $args['type'] ) );
			$parser->addTrackingCategory( 'loop-tracking-category-loop-error' );
			$spoiler->mErrors = new LoopException( wfMessage ( "loop-error-unknown-param", "<spoiler>/<loop_spoiler>", "type", $args['type'], implode ( ', ', self::$mSpoilerTypes ), $wgLoopSpoilerDefaultType )->text() );
			
		}		
		
		// button text
		if ( !isset( $args['text'] ) ) {
			$spoiler->setBtnText( wfMessage( 'loopspoiler-default-title' )->inContentLanguage()->text() );
		} else {
			$spoiler->setBtnText( htmlspecialchars( $args['text'] ) ); // parser raus
		}

		
		return $spoiler;
	}
	
}