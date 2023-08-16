<?php
/**
 * @description All hooks for LOOP that don't fit into more specific classes
 * @author Dennis Krohn <dennis.krohn@th-luebeck.de>
 */

if ( !defined( 'MEDIAWIKI' ) ) die ( "This file cannot be run standalone.\n" );

use MediaWiki\MediaWikiServices;

class LoopHooks {

	/**
	 * Catch the Request to perform custom action LoopEditMode and LoopRenderMode
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
	 * Cache different page version depending on status of Mode
	 *
	 * This is attached to the MediaWiki 'PageRenderingHash' hook.
	 *
	 * @param string $confstr
	 * @param User $user
	 * @param array $forOptions
	 * @return boolean
	 */
	public static function onPageRenderingHash( &$confstr, $user, $forOptions ) {

		$userOptionsLookup = MediaWikiServices::getInstance()->getUserOptionsLookup();
		$editMode = $userOptionsLookup->getOption( $user, 'LoopEditMode', false, true );

		if ( $editMode ) {
			$confstr .= "!loopeditmode=true";
		} else {
			$confstr .= "!loopeditmode=false";
		}

		return true;
	}

	/**
	 * Restricts viewing special pages to privileged users
	 *
	 * This is attached to the MediaWiki 'SpecialPage_initList' hook.
	 *
	 * @param array $specialPages
	 * @return boolean
	 */
	public static function onSpecialPageinitList ( &$specialPages ) {

		global $wgOut;
		$user = $wgOut->getUser();
		$userGroupManager = MediaWikiServices::getInstance()->getUserGroupManager();
		$permissionManager = MediaWikiServices::getInstance()->getPermissionManager();
		$hideSpecialPages = false;
		if ( $user->mId == null ) { # no check for specific rights - anon users get blocked from viewing these special pages.
			$hideSpecialPages = true;
		} elseif ( in_array( "shared", $userGroupManager->getUserGroups($user) ) || in_array( "shared_basic", $userGroupManager->getUserGroups($user) ) ) {
			$hideSpecialPages = true;
			$hideExtended = array( 'ChangeCredentials', 'ChangeEmail', "PasswordReset", "Preferences" );
		} elseif ( ! $permissionManager->userHasRight( $user, "loop-view-special-pages" ) ) { # for logged in users, we can check the rights.
			$hideSpecialPages = true;
		}

		if ( $hideSpecialPages ) {
			$hidePages = array(
			);
			if ( isset( $hideExtended ) ) {
				$hidePages = array_merge($hidePages, $hideExtended);
			}
			foreach( $hidePages as $page ){
				unset( $specialPages[$page] );
			}
		}

		return true;
	}

	/**
	 * Remove image link when not in loopeditmode and add responsive-img class to all images
	 *
	 * This is attached to the MediaWiki 'ParserMakeImageParams' hook.
	 *
	 * @param Title $title
	 * @param File $file
	 * @param array $params
	 * @param Parser $parser
	 * @return boolean
	 */
	public static function onParserMakeImageParams( $title, $file, &$params, $parser ) {
		global $wgOut;
		$user = $wgOut->getUser();
		$userOptionsLookup = MediaWikiServices::getInstance()->getUserOptionsLookup();
		$editMode = $userOptionsLookup->getOption( $user, 'LoopEditMode', false, true );

		if ( is_object( $file ) ) {
			$mediaType = $file->getMediaType();
			if ( $mediaType == "BITMAP" || $mediaType == "DRAWING" ) {
				$params['frame']['class'] = 'responsive-image';
				if ( $editMode ) {
					$params['frame']['class'] .= ' image-editmode';
				}
				if( class_exists( 'ImageMap' ) ) {
					$params['frame']['no-link'] = false;
				} else {
					if ( $editMode ) {
						$params['frame']['no-link'] = false;
					} else {
						$params['frame']['no-link'] = true;
					}
				}
				if ( isset( $params['frame']['align'] ) ) {
					$params['horizAlign'][ $params['frame']['align'] ] = true;
				}
			} elseif ( $mediaType == "VIDEO" ) {
				$params['frame']['class'] = 'responsive-video';
				if ( !isset( $params['handler']['width'] ) ) {
					$params['handler']['width']= "800";
				}
			}  elseif ( $mediaType == "AUDIO" ) {
				$params['frame']['class'] = 'responsive-audio';
			}
		}

		return true;
	}

	/**
	 * Custom hook called after after pre save transforming
	 * @param String &$text
	 * @param Title $title
	 * @param User $user
	 */
	public static function onPreSaveTransformComplete( &$text, $title, $user ) {

		$text = preg_replace('/<!--(.|\s)*?-->/', '', $text);
		$text = Loop::setReferenceIds( $text );
		$text = str_replace( 'scale="true"  scale="true"  ', 'scale="true" ', $text ); # fix for an update error

		return true;

	}

}
