<?php
#TODO MW 1.35 DEPRECATION
/**
  * @description Cloned WikitextContent objects
  * @author Dennis Krohn <dennis.krohn@th-luebeck.de>
  */

class LoopWikitextContentHandler extends WikitextContentHandler {
    protected function getContentClass() {
        return 'LoopWikitextContent';
    }
}
class LoopWikitextContent extends WikitextContent {
	/**
	* Copied from WikitextContent.php, overriding it with our own content and a custom Hook
	*
	* Returns a Content object with pre-save transformations applied using
	* Parser::preSaveTransform().
	*
	* @param Title $title
	* @param User $user
	* @param ParserOptions $popts
	*
	* @return Content
	*/
   public function preSaveTransform( Title $title, User $user, ParserOptions $popts ) {
	   global $wgParser;
	   $text = $this->getText();
	   $pst = $wgParser->preSaveTransform( $text, $title, $user, $popts );

	   # Custom Hook for changing content before it's saved
	   Hooks::run( 'PreSaveTransformComplete', [ &$pst, $title, $user ] );

	   if ( $text === $pst ) {
		   return $this;
	   }
	   $ret = new static( $pst );
	   if ( $wgParser->getOutput()->getFlag( 'user-signature' ) ) {
		   $ret->hadSignature = true;
	   }
	   return $ret;
   }
}
