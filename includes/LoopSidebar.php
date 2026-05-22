
<?php
/**
 * @description Error renderings for loop_sidebar, as the tag itself does not need rendering.
 * @author Dennis Krohn <dennis.krohn@th-luebeck.de>
 */

if ( !defined( 'MEDIAWIKI' ) ) die ( "This file cannot be run standalone.\n" );

class LoopSidebar {
	public static function onParserSetup( Parser $parser ): bool
	{
		$parser->setHook( 'loop_sidebar', 'LoopSidebar::renderSidebar' );
		return true;
    }
    public static function renderSidebar( $input, array $args, Parser $parser, PPFrame $frame ): Exception|LoopException|string
	{
        $html = "";
        try {
            if (!empty( $args[ "page" ] )) {
                $sidebarTitle = Title::newFromText( $args["page"] );
                if ( is_object($sidebarTitle) ) {
                    $sidebarWP = new WikiPage( $sidebarTitle );
                    if ( $sidebarWP->getID() == 0 ) {
                        $parser->addTrackingCategory( 'loop-tracking-category-error' );
                    }
                }

            } else {
				$parser->addTrackingCategory( 'loop-tracking-category-error' );
                throw new LoopException( wfMessage("loopsidebar-error-nopage") );
            }
        } catch ( LoopException $e) {
            $parser->addTrackingCategory( 'loop-tracking-category-error' );
            $html = $e;
        }
        return $html;
    }
}
