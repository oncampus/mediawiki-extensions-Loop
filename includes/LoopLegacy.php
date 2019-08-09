<?php

/**
 * A class for all deprecated loop functions that still require some kind of rendering.
 */

class LoopLegacy {
    #hooks needed for: 

    
    public static function onParserSetup( Parser $parser ) {
        $parser->setHook ( 'biblio', 'LoopLegacy::renderLegacyBiblio' ); 
        return true;
    }	

    # replaced by loop_literature
    public static function renderLegacyBiblio ( $input, array $args, Parser $parser, PPFrame $frame ) { 
        $e = new LoopException( wfMessage( 'looplegacy-error-unsupported', 'biblio', 'LOOP2' )->text() );
        $parser->addTrackingCategory( 'looplegacy-tracking-category' );
        return $e;
    }
        
}