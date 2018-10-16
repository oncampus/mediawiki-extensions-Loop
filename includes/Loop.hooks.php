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
		
		// catch action loopeditmode and save setting in user options
		if ( $user->isAllowed( 'edit' ) ) {
			$loopeditmodeRequestValue  = $request->getText( 'loopeditmode' );
			if( isset( $loopeditmodeRequestValue ) && ( in_array( $loopeditmodeRequestValue, array( '0', '1' ) ) ) ) {
				$user->setOption( 'loopeditmode', $loopeditmodeRequestValue );
				$user->saveSettings();
			}
		}	
		
		// catch action loopeditmode and save setting in user options
		
		if ( $user->isAllowed( 'loop-rendermode' ) ) {
			$looprendermodeRequestValue  = $request->getText( 'looprendermode' );
			if( isset( $looprendermodeRequestValue ) && ( in_array( $looprendermodeRequestValue, array( 'offline', 'epub' ) ) ) ) {
				$user->setOption( 'looprendermode', $looprendermodeRequestValue );
				$user->saveSettings();
			} else {
				$user->setOption( 'looprendermode', 'default' );
				$user->saveSettings();
			}
		}		
		
		return true;
	}
	/**
	 * Cache different page version depending on status of LoopEditMode
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