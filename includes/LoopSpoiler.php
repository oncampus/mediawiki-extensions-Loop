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

	protected static $_instances = array();
	
	private static $mSpoilerTypes = array(
		'default',
		'transparent'
	);

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

	public static function getInstances()
    {
        $return = array();
        foreach(self::$_instances as $instance) {
            if($instance instanceof LoopSpoiler) {
                $return[] = $instance;
            }
        }
        return $return;
	}
	
	public function __construct()
    {
        self::$_instances[] = $this;
    }

    public function __destruct()
    {
        unset(self::$_instances[array_search($this, self::$_instances, true)]);
    }

	public static function onParserSetup( Parser &$parser ) {
		$parser->setHook( 'spoiler', 'LoopSpoiler::renderLoopSpoiler' );
		$parser->setHook( 'loop_spoiler', 'LoopSpoiler::renderLoopSpoiler' ); // behalten?
		return true;
	}

	public static function renderLoopSpoiler( $input, array $args, Parser $parser, PPFrame $frame ) {
		$parser->getOutput()->addModules( 'loop.spoiler.js' );
		
		$spoiler = LoopSpoiler::newFromTag( $input, $args, $parser, $frame );
		$spoiler->setContent( $parser->recursiveTagParseFully( $input ), $frame );
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
		
		if($this->getType() === 'default') {

			$return = Html::rawElement(
				'button',
				[
					'type' => 'button',
					'class' => 'btn loopspoiler loopspoiler_type_default ' . $this->getId(),
				],
				$this->getBtnText()
			);

		} else {
			$return = '<div class="loopspoiler-container container_' . $this->getType() . '">';
			$return .= '<button class="btn loopspoiler loopspoiler_type_' . $this->getType() . ' ' . $this->getId() . '" type="button">'.$this->getBtnText() . '</button>';
			$return .= '<div id="'.$this->getId() . '" class="loopspoiler_content_wrapper loopspoiler_type_'.$this->getType() . '">';
			$return .= '<div class="loopspoiler_content">' . $content . '</div>';
			$return .= "</div></div>";
		}
		

		return $return;
	}

	//mw.config.get('wgLoopSpoilerContent')
	public static function onMakeGlobalVariablesScript( array &$vars, OutputPage $out ) {
		if(!empty(LoopSpoiler::getInstances())) {
			foreach(LoopSpoiler::getInstances() as $instance) {
				$vars['wgLoopSpoilerContent'][$instance->mId] = [$instance->mContent];
			}
		}
	}

	public static function newFromTag( $input, array $args, Parser $parser, PPFrame $frame ) {
		global $wgLoopSpoilerDefaultType;
		
		$spoiler = new LoopSpoiler();
		
		$spoiler->setId(uniqid());
		
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