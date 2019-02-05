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

		$dbr = wfGetDB( DB_REPLICA );
		if ( $dbr->tableExists( 'loop_settings' ) ) {

			$res = $dbr->select(
				'loop_settings',
				array( 'lset_id', 'lset_rightstext', 'lset_rightsurl', 'lset_rightsicon', 'lset_languagecode', 'lset_skinstyle' ),
				array(),
				__METHOD__,
				array( 'ORDER BY' => 'lset_id DESC LIMIT 1' )
			);
			$row = $res->fetchObject();

			if ( isset( $row->lset_id ) ) {
				$wgRightsText = ( empty( $row->lset_rightstext ) ? $wgRightsText : $row->lset_rightstext );
				$wgRightsUrl = ( empty( $row->lset_rightsurl ) ? $wgRightsUrl : $row->lset_rightsurl );
				$wgRightsIcon = ( empty( $row->lset_rightsicon ) ? $wgRightsIcon : $row->lset_rightsicon  );
				$wgLanguageCode = ( empty( $row->lset_languagecode ) ? $wgLanguageCode : $row->lset_languagecode );
				$wgDefaultUserOptions['LoopSkinStyle'] = ( empty( $row->lset_skinstyle ) ? 'loop-common' : $row->lset_skinstyle );
			}
		}

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

		global $wgDefaultUserOptions;

		if ( in_array( 'loopeditmode', $forOptions ) ) {
			$confstr .= "!loopeditmode=" . $user->getOption( 'LoopEditMode', false, true );
		}

		if ( in_array( 'looprendermode', $forOptions ) ) {
			$confstr .= "!looprendermode=" . $user->getOption( 'LoopRenderMode', $wgDefaultUserOptions["LoopRenderMode"], true );
		}

		return true;
	}

}
