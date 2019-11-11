<?php

/**
 * A class for all deprecated loop functions that still require some kind of rendering.
 */

class LoopLegacy {
    #hooks needed for: 
    
    public static function onParserSetup( Parser $parser ) {
        $parser->setHook ( 'biblio', 'LoopLegacy::renderLegacyBiblio' ); 
        $parser->setHook ( 'capira', 'LoopLegacy::renderLegacyCapira' ); 
        $parser->setHook ( 'references', 'LoopLegacy::renderLegacyReferences' ); 
        $parser->setHook ( 'inline-code', 'LoopLegacy::renderLegacyInlinecode' ); 
        $parser->setHook ( 'harvardreferences', 'LoopLegacy::renderLegacyHarverdreferences' ); 
        $parser->setHook ( 'mscgen', 'LoopLegacy::renderLegacyMscgen' ); 
        $parser->setHook ( 'nocite', 'LoopLegacy::renderLegacNocite' ); 
        $parser->setHook ( 'talkpage', 'LoopLegacy::renderLegacyTalkpage' ); 
        $parser->setHook ( 'thread', 'LoopLegacy::renderLegacyThread' ); 
        $parser->setHook ( 'graphwiz', 'LoopLegacy::renderLegacyThread' ); 
        return true;
    }	

    # Replaced by loop_literature. Will be removed later.
    public static function renderLegacyBiblio ( $input, array $args, Parser $parser, PPFrame $frame ) { 
        $e = new LoopException( wfMessage( 'looplegacy-biblio' )->text() );
        $parser->addTrackingCategory( 'looplegacy-tracking-category' );
        return $e;
    }
    
    # No replacement. Will be removed later.
    public static function renderLegacyCapira ( $input, array $args, Parser $parser, PPFrame $frame ) { 
        $e = new LoopException( wfMessage( 'looplegacy-error-unsupported', 'capira', 'LOOP2' )->text() );
        $parser->addTrackingCategory( 'looplegacy-tracking-category' );
        return $e;
    }

    # No replacement. Will be removed later.
    public static function renderLegacyReferences ( $input, array $args, Parser $parser, PPFrame $frame ) { 
        $e = new LoopException( wfMessage( 'looplegacy-references' )->text() );
        $parser->addTrackingCategory( 'looplegacy-tracking-category' );
        return $e;
    }

    # No replacement. Will be removed later.
    public static function renderLegacyInlinecode ( $input, array $args, Parser $parser, PPFrame $frame ) { 
        $e = new LoopException( wfMessage( 'looplegacy-error-unsupported', 'inline-code', 'LOOP2' )->text() );
        $parser->addTrackingCategory( 'looplegacy-tracking-category' );
        return $e;
    }

    # No replacement. Will be removed later.
    public static function renderLegacyHarverdreferences ( $input, array $args, Parser $parser, PPFrame $frame ) { 
        $e = new LoopException( wfMessage( 'looplegacy-error-unsupported', 'harvardreferences', 'LOOP2' )->text() );
        $parser->addTrackingCategory( 'looplegacy-tracking-category' );
        return $e;
    }

    # No replacement. Will be removed later.
    public static function renderLegacyMscgen ( $input, array $args, Parser $parser, PPFrame $frame ) { 
        $e = new LoopException( wfMessage( 'looplegacy-error-unsupported', 'mscgen', 'LOOP2' )->text() );
        $parser->addTrackingCategory( 'looplegacy-tracking-category' );
        return $e;
    }

    # No replacement. Will be removed later.
    public static function renderLegacNocite ( $input, array $args, Parser $parser, PPFrame $frame ) { 
        $e = new LoopException( wfMessage( 'looplegacy-error-unsupported', 'nocite', 'LOOP2' )->text() );
        $parser->addTrackingCategory( 'looplegacy-tracking-category' );
        return $e;
    }

    # No replacement. Will be removed later.
    public static function renderLegacyTalkpage ( $input, array $args, Parser $parser, PPFrame $frame ) { 
        $e = new LoopException( wfMessage( 'looplegacy-error-unsupported', 'talkpage', 'LOOP2' )->text() );
        $parser->addTrackingCategory( 'looplegacy-tracking-category' );
        return $e;
    }

    # No replacement. Will be removed later.
    public static function renderLegacyThread ( $input, array $args, Parser $parser, PPFrame $frame ) { 
        $e = new LoopException( wfMessage( 'looplegacy-error-unsupported', 'thread', 'LOOP2' )->text() );
        $parser->addTrackingCategory( 'looplegacy-tracking-category' );
        return $e;
    }

    # No replacement. Will be removed later.
    public static function renderLegacyGraphwiz ( $input, array $args, Parser $parser, PPFrame $frame ) { 
        $e = new LoopException( wfMessage( 'looplegacy-error-unsupported', 'GraphWiz', 'MediaWiki 1.33 (LOOP 2)' )->text() );
        $parser->addTrackingCategory( 'looplegacy-tracking-category' );
        return $e;
    }
        
}