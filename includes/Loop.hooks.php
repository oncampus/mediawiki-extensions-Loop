<?php 
class LoopHooks {

	/**
	 * Catch the Request to perform custom action LoopEditMode
	 * 
	 * This is attached to the MediaWiki 'MediaWikiPerformAction' hook.
	 * 
	 * @param OutputPage $output
	 * @param Article $article
	 * @param Title $title
	 * @param User $user
	 * @param Request $request
	 * @param Wiki $wiki
	 */
	public static function onMediaWikiPerformAction( $output, $article, $title, $user, $request, $wiki ) {
		
		Loop::handleLoopRequest( $output, $request, $user );
		
		return true;
	}
	/**
	 * Catch request to perform LoopEditMode on Special Pages
	 * 
	 * This is attached to the MediaWiki 'SpecialPageBeforeExecute' hook.
	 * 
	 * @param SpecialPage $special
	 * @param string $subPage
	 */
	public static function onSpecialPageBeforeExecute( $special, $subPage ) { 
	
		global $wgRequest, $wgOut;
		$output = $wgOut;
		$user = $output->getUser();
		$request = $wgRequest;
	
		Loop::handleLoopRequest( $output, $request, $user );
		
		return true;
	
	}
	
	
	/**
	 * Cache different page version depending on status of Mode
	 *
	 * This is attached to the MediaWiki 'PageRenderingHash' hook.
	 *
	 * @param string $confstr
	 * @param User $user
	 * @param array $forOptions
	 * @return boolean
	 */
	public static function onPageRenderingHash( &$confstr, User $user, &$forOptions ) {
	
		if ( in_array( 'loopeditmode', $forOptions ) ) {
			$confstr .= "!loopeditmode=" . $user->getOption( 'loopeditmode', false, true );
		}
		
		if ( in_array( 'looprendermode', $forOptions ) ) {
			$confstr .= "!looprendermode=" . $user->getOption( 'looprendermode' );
		}
	
		return true;
	}
	
}