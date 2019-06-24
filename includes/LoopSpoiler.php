<?php

if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file cannot be run standalone.\n" );
}

class LoopSpoiler {
	
	public $mId;
	public $mType;
	public $mBtnText;
	public $mContent;
	public $mParser;
	
	private static $mSpoilerTypes = array(
		'default',
		'transparent',
		'in_text',
		'in_text_transparent'
	);
	
	public function setId($id) {
		$this->mId = $id;
	}

	public function setType($type) {
		$this->mType = $type;
	}
	
	public function setBtnText($btn_text) {
		$this->mBtnText = $btn_text;
	}
	
	public function setContent($content) {
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
		
		$parser->setHook( 'spoiler', 'LoopSpoiler::renderLoopSpoiler2' );
		$parser->setHook( 'loop_spoiler', 'LoopSpoiler::renderLoopSpoiler2' ); // entfernen?
		return true;
	}

	public static function renderLoopSpoiler2( $input, array $args, Parser $parser, PPFrame $frame ) {
		
		try {		
			$spoiler = LoopSpoiler::newFromTag($input, $args, $parser, $frame);
			$spoiler->setContent($parser->recursiveTagParse($input));
			$return  = $spoiler->render2();
		} catch ( LoopException $e ) {
			$parser->addTrackingCategory( 'loop-tracking-category-loop-error' );
			$return = "$e";
		}
		
		return $return;
	}
	
	
	/**
	 * Render
	 */
	public function render2() {
		$content = $this->getContent();
		while ( substr( $content, -1, 2 ) == "\n" ) { # remove newlines at the end of content for cleaner html output
			$content = substr( $content, 0, -1 );
		}
		$return = '<span class="btn loopspoiler loopspoiler_type_'.$this->getType().'" onclick="$(\'#'.$this->getId().'\').toggleClass(\'d-inline\'); $(\'#'.$this->getId().'\').toggleClass(\'d-none\'); return false;" >'.$this->getBtnText().'</span>';
		$return .= '<div id="'.$this->getId().'" class="loopspoiler_content_wrapper d-none loopspoiler_type_'.$this->getType().'">';
		$return .= '<div class="loopspoiler_content">'.$content.'</div>';
		$return .= "\n</div>";
		
		return $return;
		
	}

	public static function renderLoopSpoiler( $input, array $args, Parser $parser, PPFrame $frame ) {
		try {		
			$spoiler = LoopSpoiler::newFromTag($input, $args, $parser, $frame);
			//$return = $spoiler->render($input, $parser, $spoiler);




			$toset = $input;
		
			$toset = $parser->recursiveTagParse($toset); 
			//$toset = $parser->killMarkers( $toset );
			//$toset = $parser->insertStripItem($toset, $parser->mStripState);
			#$toset = $parser->killMarkers( $toset ); #killmarkers muss für loopobjekte etc raus
			$spoiler->setContent($toset);

	
/*			$html = '<span class="loopspoilercontainer loopspoilercontainer_'.$spoiler->getType().'">';
			$html .= '<span class="btn loopspoilerbutton loopspoiler_type_'.$spoiler->getType().'" onclick="$(\'#'.$spoiler->getId().'\').toggle(); $(this).toggleClass(\'spoileractive\'); $(this).parent().toggleClass(\'parentspoileractive\');return false;" >'.$spoiler->getBtnText().'</span>';
			$html .= '<span id="'.$spoiler->getId().'" class="loopspoiler_type_'.$spoiler->getType().'" style="display:none;">';
			$html .= '<span class="loopspoiler_content">'.$spoiler->getContent().'</span>'; 
			$html .= '</span>';
			$html .= '</span>';
			$html .= '</span>';
			*/

			$html = Html::openElement('span', ['class'=>'loopspoilercontainer d-inline loopspoilercontainer_'.$spoiler->getType()]);
			$html .= Html::element('span', ['class'=>'btn loopspoilerbutton loopspoiler_type_'. $spoiler->getType(), 'onclick'=>'$(\'#'.$spoiler->getId().'\').toggle(); $(this).toggleClass(\'spoileractive\'); $(this).parent().toggleClass(\'parentspoileractive\');return false;'], $spoiler->getBtnText());
			$html .= Html::openElement('span', ['id'=>$spoiler->getId(), 'class'=>'loopspoiler_type_'.$spoiler->getType(), 'style'=>'display:none;']);
			$html .= $spoiler->getContent(); # inhalte wurden als text angezeigt, wenn sie in einem html::element als content eingefügt wurden. (delete comment)
			$html .= Html::closeElement('span');
			$html .= Html::closeElement('span');
			$return = $html;
			#dd($toset,$input,$html);

			//$return = $parser->killMarkers( $return );

			//$return = $parser->insertStripItem($return, $parser->mStripState);
			//$return = $parser->recursiveTagParse($return);
			
			//return [$parser->insertStripItem( $return, $parser->mStripState ), 'noparse'=>false];
			return $return;
		} catch ( LoopException $e ) {
			$parser->addTrackingCategory( 'loop-tracking-category-loop-error' );
			return $e;
		}
	}

	public static function newFromTag( $input, array $args, Parser $parser, PPFrame $frame ) {
	
		global $wgLoopSpoilerDefaultType;
		
		$spoiler = new LoopSpoiler();
		
		$spoiler->setId(uniqid());
		
		// set spoiler type to standard if not submitted.
		if ( ! isset( $args['type'] ) ) {
			$spoiler->setType($wgLoopSpoilerDefaultType); # muss noch in extension.json/config gesetzt werden oder einfach hier den standard definieren
		} else {
			$spoiler->setType( htmlspecialchars ( $args['type'] ) );
		}		
		
		// throw exception if spoiler type is not valid
		if ( !empty($spoiler->getType()) && !in_array ( $spoiler->getType(), self::$mSpoilerTypes ) ) {
			throw new LoopException( wfMessage ( 'loopspoiler-error-unknown-spoilertype', $spoiler->getType(), implode ( ', ',self::$mSpoilerTypes ) ) );
		}		
		
		// button text
		if ( !isset( $args['text'] ) ) {
			$spoiler->setBtnText(wfMessage('loopspoiler-default-title')->inContentLanguage()->text());
		} else {
			$spoiler->setBtnText( $parser->recursiveTagParse(htmlspecialchars ( $args['text'] )) );
		}		


		return $spoiler;
	}
		

	

/*

NOT ACTIVE ATM

*/

/*
	public function render($input, $parser, $spoiler) {

		$toset = $input;
		
		$toset = $parser->recursiveTagParse($toset); 
		$toset = $parser->killMarkers( $toset );
		$toset = $parser->insertStripItem($toset, $parser->mStripState);

		$spoiler->setContent($toset);
*/


		//dd($this->getContent());
		/*
			Attention: Using only spans because "return [$parser->insertStripItem( $html, $parser->mStripState ), 'noparse' =>true, 'isHTML'=>true];"
			and variants of this snippet is somehow ignored or interpreted differently than it should. Already one single div inside this snippet breaks
			the spoiler tag. Found no way to prevent p tags from generating before and/or after spoiler tag (inline spoilers) other than using inline tags
		*/
/*
		$html = '<span class="loopspoilercontainer loopspoilercontainer_'.$this->getType().'">';
		$html .= '<span class="btn loopspoilerbutton loopspoiler_type_'.$this->getType().'" onclick="$(\'#'.$this->getId().'\').toggle(); $(this).toggleClass(\'spoileractive\'); $(this).parent().toggleClass(\'parentspoileractive\');return false;" >'.$this->getBtnText().'</span>';
		$html .= '<span id="'.$this->getId().'" class="loopspoiler_type_'.$this->getType().'" style="display:none;">';
		$html .= '<span class="loopspoiler_content">'.$this->getContent().'</span>'; 
		$html .= '</span>';
		$html .= '</span>';
		
		$render = $html;*/
		//$render = $html;
		//$render = $parser->killMarkers($render);
		//$render = $parser->insertStripItem($render, $parser->mStripState);
		//dd($render);
		//$text = $html;
		//$striped_text = $parser->killMarkers( $render );
		//$render = $parser->recursiveTagParse($striped_text);
		//$render = $parser->insertStripItem($render, $parser->mStripState);		
		//dd($render);

	//	return $render;
	//}

}