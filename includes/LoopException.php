<?php 
/**
 * Loop exception
 */
class LoopException extends Exception {
	/**
	 * Constructor function
	 *
	 * @param $message Message to create error message from. Should be fully parsed.
	 * @param $code int optionally, an error code.
	 * @param $previous Exception that caused this exception.
	 */
	public function __construct( $message, $code = 0, Exception $previous = null ) {
		parent::__construct( $message, $code, $previous );
	}
	/**
	 * Auto-renders exception as HTML error message in the wiki's content language.
	 *
	 * @return string Error message HTML.
	 */
	public function __toString() {
		
		global $wgOut;
		$user = $wgOut->getUser();
		
		$editMode = $user->getOption( 'LoopEditMode', false, true );
		if ( $editMode == true ) {
			global $wgParser, $wgFrame;
			
			$parsedMsg = $wgParser->recursiveTagParse( $this->getMessage(), $wgFrame );
			return Html::rawElement( 'div',	array( 'class' => 'errorbox' ), $parsedMsg );
		} else {
			return '';
		}
	}
}