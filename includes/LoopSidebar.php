
<?php

/**
 * @description All hooks for LOOP that don't fit into more specific classes
 * @author Dennis Krohn <dennis.krohn@th-luebeck.de>
 */

if ( !defined( 'MEDIAWIKI' ) ) {
    die( "This file cannot be run standalone.\n" );
}

class LoopSidebar {
    # Dummy renderings for empty tags such as loop_sidebar
	public static function onParserSetup( Parser &$parser ) {
		$parser->setHook( 'loop_sidebar', 'LoopSidebar::renderSidebar' );
		return true;
    }
    public static function renderSidebar( $input, array $args, Parser $parser, PPFrame $frame ) {
        $html = "";
        try {
            if ( isset ( $args[ "page" ] ) ) {
                $sidebarTitle = Title::newFromText( $args["page"] );
                $sidebarWP = new WikiPage( $sidebarTitle );
                $sidebarParserOutput = $sidebarWP->getParserOutput( new ParserOptions, null, true );
                if ( ! isset ( $sidebarParserOutput->mText ) ) {
                    $parser->addTrackingCategory( 'loop-tracking-category-error' );
                    # no throwing errors. the error is displayed in the sidebar.
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