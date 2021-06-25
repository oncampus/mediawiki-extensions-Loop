<?php
/**
 * Special page used to wipe the OBJECTCACHE table
 * I use it on test wikis when I am fiddling about with things en masse that could be cached
 *
 * @file
 * @ingroup Extensions
 * @author Rob Church <robchur@gmail.com>, Dennis Krohn (oncampus)
 * @licence of this file Public domain
 */

if ( !defined( 'MEDIAWIKI' ) ) die ( "This file cannot be run standalone.\n" );

use MediaWiki\MediaWikiServices;

class SpecialPurgeCache extends SpecialPage {

	function __construct() {
		parent::__construct( 'PurgeCache', 'purgecache' );
	}

	function execute( $par ) {

		$out = $this->getOutput();
		$request = $this->getRequest();
		$user = $this->getUser();
		Loop::handleLoopRequest( $out, $request, $user ); #handle editmode
		$permissionManager = MediaWikiServices::getInstance()->getPermissionManager();
		$this->setHeaders();
		if ( $permissionManager->userHasRight( $out->getUser(), 'purgecache' ) ) {
			if ( $request->getCheck( 'purge' ) && $request->wasPosted() ) {
				self::purge();
				#$dbw = wfGetDB( DB_PRIMARY );
				#$dbw->delete( 'objectcache', '*', __METHOD__ );
				$out->addWikiMsg( 'purgecache-purged' );
				$out->addHTML( $this->makeForm() );

				#$exportPath = $wgUploadDirectory . "/export/";
				#SpecialPurgeCache::deleteAll($exportPath);

			} else {
				$out->addWikiMsg( 'purgecache-warning' );
				$out->addHTML( $this->makeForm() );
			}
		} else {
			$html = '<div class="alert alert-warning" role="alert">' . $this->msg( 'specialpage-no-permission' ) . '</div>';
			$out->addHTML( $html );
		}
	}
	public static function purge() {
		global $wgOut, $wgUploadDirectory;
		$permissionManager = MediaWikiServices::getInstance()->getPermissionManager();
		if ( $permissionManager->userHasRight( $wgOut->getUser(), 'purgecache' ) ) {
			$dbw = wfGetDB( DB_PRIMARY );
			$dbw->delete( 'objectcache', "keyname NOT LIKE '%MWSession%'", __METHOD__ );

			$exportPath = $wgUploadDirectory . "/export/";
			$screenshotPath = $wgUploadDirectory . "/screenshots/";
			SpecialPurgeCache::deleteAll($exportPath);
			SpecialPurgeCache::deleteAll($screenshotPath);
			LoopObject::updateStructurePageTouched();
			LoopGlossary::updateGlossaryPageTouched();
		}
		return true;
	}

	public static function deleteAll( $str ) {
		if (is_file($str)) {
			return unlink($str);
		}
		elseif (is_dir($str)) {
			$scan = glob(rtrim($str,'/').'/*');
			foreach($scan as $index=>$path) {
				SpecialPurgeCache::deleteAll($path);
			}
			return @rmdir($str);
		}
	}

	function makeForm() {
		$self = Title::newFromText( 'Special:PurgeCache' );
		$form  = Xml::openElement( 'form', array( 'method' => 'post', 'action' => $self->getLocalUrl() ) );
		$form .= Xml::element( 'input', array( 'type' => 'submit', 'name' => 'purge', 'value' => $this->msg( 'purgecache-button' ) ) );
		$form .= Xml::closeElement( 'form' );
		return $form;
	}

	/**
	 * Specify the specialpages-group loop
	 *
	 * @return string
	 */
	protected function getGroupName() {
		return 'loop';
	}

}
