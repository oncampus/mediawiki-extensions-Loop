<?php

class LoopPrint {
	
	public $button;

	public function setButton( $button ) {
		$this->button = $button;
	}

	public function getButton() {
		return $this->button;
	}

	public static function onParserSetup( Parser $parser ) {
		$parser->setHook( 'loop_print', 'LoopPrint::renderLoopPrint' );
		return true;
	}
	
	static function renderLoopPrint( $input, array $args, Parser $parser, PPFrame $frame ) {
		global $wgOut;

		

		$user = $wgOut->getUser();
		$loopeditmode = $user->getOption( 'LoopEditMode' ,false, true );
		$parser->getOptions()->optionUsed( 'LoopEditMode' );		

		/*if (array_key_exists('button', $args)) {
			if ($args["button"] == 'false') {
				$this->setButton(false);
			} else {
				$this->setButton(true);
			}
		} else {
			$this->setButton(true);
		}
*/
		$html = '';
		if ( $loopeditmode ) {
			$html .= '<div class="loopprint">';
			$html .= $parser->recursiveTagParse( $input, $frame );
			$html .= '</div>';		
		}

		return $html;
	}

}

/*		global $wgOut;
		$user = $wgOut->getUser();
		$loopEditMode = $user->getOption( 'LoopEditMode', false, true );
		$parser->getOptions()->optionUsed( 'LoopEditMode' );
*/ 