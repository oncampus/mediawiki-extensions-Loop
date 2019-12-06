<?php
/**
 * @description Adds zoom functionality to choosen images
 * @ingroup Extensions
 * @author Dustin Neß <dustin.ness@th-luebeck.de> 
 */

if ( !defined( 'MEDIAWIKI' ) ) die ( "This file cannot be run standalone.\n" );

class LoopZoom {
    static function onParserSetup( Parser $parser ) {
        $parser->setHook( 'loop_zoom', 'LoopZoom::renderLoopZoom' );
        return true;
    }

    static function renderLoopZoom( $input, array $args, Parser $parser, PPFrame $frame ) {
        
        $unique_id =  str_replace(".", "", microtime(true));
        
        $html = '<div id="loopzoom-'. $unique_id .'" class="loopzoom" data-toggle="modal" data-target=".loopzoom-'.$unique_id.'-modal">';
        $html .= $parser->recursiveTagParseFully( $input );
        $html .= '</div>';

        $html .= '<div class="modal fade loopzoom-'.$unique_id.'-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content"></div>
        </div>
        </div>';

        return $html;
    }

}
/*
<button type="button" class="btn btn-primary" data-toggle="modal" data-target=".bd-example-modal-lg">Large modal</button>

<div class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      ...
    </div>
  </div>
</div>

*/