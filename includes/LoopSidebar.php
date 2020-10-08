
<?php

/**
 * @description Error renderings for loop_sidebar, as the tag itself does not need rendering.
 * @author Dennis Krohn <dennis.krohn@th-luebeck.de>
 */

if ( !defined( 'MEDIAWIKI' ) ) {
    die( "This file cannot be run standalone.\n" );
}

class LoopSidebar {
	public static function onParserSetup( Parser &$parser ) {
		$parser->setHook( 'loop_sidebar', 'LoopSidebar::renderSidebar' );
		return true;
    }
    public static function renderSidebar( $input, array $args, Parser $parser, PPFrame $frame ) {
        $html = "";
        try {
            if ( isset ( $args[ "page" ] ) && !empty( $args[ "page" ] ) ) {
                $sidebarTitle = Title::newFromText( $args["page"] );
                if ( is_object($sidebarTitle) ) {
                    $sidebarWP = new WikiPage( $sidebarTitle );
                    if ( $sidebarWP->getID() == 0 ) {
                        $parser->addTrackingCategory( 'loop-tracking-category-error' );
                    } 
                }
               
            } else {
                throw new LoopException( wfMessage("loopsidebar-error-nopage")->text() );
                $parser->addTrackingCategory( 'loop-tracking-category-error' );
            }
        } catch ( LoopException $e) {
            $parser->addTrackingCategory( 'loop-tracking-category-error' );
            $html = $e;
        }
        return $html;
    }
}