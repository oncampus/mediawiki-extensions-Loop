<?php
/**
  * @description RSS Feed special page
  * @ingroup Extensions
  * @author Dennis Krohn @krohnden <dennis.krohn@th-luebeck.de>
  */
  
  if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file cannot be run standalone.\n" );
}

use MediaWiki\MediaWikiServices;

class SpecialLoopRSS extends SpecialPage {

	function __construct() {
		parent::__construct( 'LoopRSS' );
	}

	function execute( $par ) {
        
        global $wgLoopUnprotectedRSS;
		$out = $this->getOutput();
		$request = $this->getRequest();
		$user = $this->getUser();
		Loop::handleLoopRequest( $out, $request, $user ); #handle editmode
		$out->setPageTitle( $this->msg( "privacy" ) );

        if ( $user->isLoggedIn() || $wgLoopUnprotectedRSS ) {

            global $wgCanonicalServer, $wgScriptPath;
            $url = $wgCanonicalServer . $wgScriptPath . "/api.php";

            $params = "";
            if ( ! $user->isLoggedIn() && class_exists( "LoopSessionProvider" ) ) { 
                $params .= LoopSessionProvider::getApiPermission();
            } else {
                $this->setHeaders();
                $out->addHTML($this->msg("specialpage-no-permission"));
                return;
            }
            $params .= "hidebots=1&namespace=2&invert=1&urlversion=1&days=30&limit=20&action=feedrecentchanges&feedformat=atom";
            
            $url .= "?" . $params;
            $httpRequest = MWHttpRequest::factory( $url );
            $status = $httpRequest->execute();
            $result = $httpRequest->getContent();
            
            $this->getOutput()->disable();
            wfResetOutputBuffers();

            header("Last-Modified: " . date("D, d M Y H:i:s T", strtotime(time())));
            header("Content-Type: application/xml; charset=utf-8");
            echo $result;

        } else {
            $this->setHeaders();
			$out->addHTML($this->msg("specialpage-no-permission"));
        }

    }
    
	protected function getGroupName() {
		return 'loop';
	}
}