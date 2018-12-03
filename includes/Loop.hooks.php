<?php 
class LoopHooks {

	/**
	 * Catch the Request to perform custom action LoopEditMode and LoopRenderMode
	 * 
	 * This is attached to the MediaWiki 'onBeforeInitialize' hook.
	 * 
	 * @param Title $title
	 * @param Article $article
	 * @param OutputPage $output
	 * @param User $user
	 * @param Request $request
	 * @param Wiki $wiki
	 */
	public static function onBeforeInitialize( $title, $article = null, $output, $user, $request, $wiki ) {
		
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
	
		global $wgHiddenPrefs;
		
		if ( in_array( 'loopeditmode', $forOptions ) ) {
			$confstr .= "!loopeditmode=" . $user->getOption( 'LoopEditMode', false, true );
		}
		
		if ( in_array( 'looprendermode', $forOptions ) ) {
			$confstr .= "!looprendermode=" . $wgHiddenPrefs[ 'LoopRenderMode' ];
		}
	
		return true;
	}
	
}