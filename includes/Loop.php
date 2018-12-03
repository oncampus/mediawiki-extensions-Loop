<?php class Loop {

	/**
	 * Save changes in LoopEditMode and LoopRenderMode
	 * 
	 * This is called by 'SpecialPageBeforeExecute' and 'MediaWikiPerformAction' hooks.
	 * 
	 * @param OutputPage $output
	 * @param Request $request
	 * @param User $user
	 */
	public static function handleLoopRequest( $output, $request, $user ) { 
		
		global $wgHiddenPrefs;
		
		if ( $user->isAllowed( 'edit' ) ) {
			$loopeditmodeRequestValue  = $request->getText( 'loopeditmode' );
			if( isset( $loopeditmodeRequestValue ) && ( in_array( $loopeditmodeRequestValue, array( "0", "1" ) ) ) ) {
				$user->setOption( 'LoopEditMode', $loopeditmodeRequestValue );
				$user->saveSettings();
			}
		}
		
		if ( $user->isAllowed( 'loop-rendermode' ) ) {
			$looprendermodeRequestValue  = $request->getText( 'looprendermode' );
			if( isset( $looprendermodeRequestValue ) && ( in_array( $looprendermodeRequestValue, array( 'offline', 'epub' ) ) ) ) {
				$wgHiddenPrefs['LoopRenderMode'] = $looprendermodeRequestValue;
			} else {
				$wgHiddenPrefs['LoopRenderMode'] = 'default';
			}
		}	
			
	}
	
}