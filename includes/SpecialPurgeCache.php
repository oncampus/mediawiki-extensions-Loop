<?php
if ( !defined( 'MEDIAWIKI' ) ) {
	echo( "This file is an extension to the MediaWiki software and cannot be used standalone.\n" );
	exit( 1 );
}
/**
 * Special page used to wipe the OBJECTCACHE table
 * I use it on test wikis when I am fiddling about with things en masse that could be cached
 *
 * @file
 * @ingroup Extensions
 * @author Rob Church <robchur@gmail.com>, Dennis Krohn (oncampus)
 * @licence of this file Public domain
 */

class SpecialPurgeCache extends SpecialPage {

	function __construct() {
		parent::__construct( 'PurgeCache', 'purgecache' );
	}

	function execute( $par ) {
		global $wgRequest, $wgOut;

		$out = $this->getOutput();
		$request = $this->getRequest();
		
		$this->setHeaders();
		if ( $out->getUser()->isAllowed( 'purgecache' ) ) {
			if ( $request->getCheck( 'purge' ) && $request->wasPosted() ) {
				$dbw = wfGetDB( DB_MASTER );
				$dbw->delete( 'objectcache', '*', __METHOD__ );
				$out->addWikiMsg( 'purgecache-purged' );
			} else {
				$out->addWikiMsg( 'purgecache-warning' );
				$out->addHTML( $this->makeForm() );
			}
		} else {
			$out->permissionRequired( 'purgecache' );
		}
	}

	function makeForm() {
		$self = $this->getTitle();
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