<?php
/**
  * @description Consent prompt for YouTube
  * @author Dustin Neß <dustin.ness@th-luebeck.de>
  */


 //include $_SERVER['DOCUMENT_ROOT'].'/extensions/EmbedVideo/EmbedVideo.hooks.php';

if( !defined( 'MEDIAWIKI' ) ) { die( "This file cannot be run standalone.\n" ); }

class LoopConsent {
	public static function onParserBeforeStrip( &$parser ) {
  //dd($_COOKIE);
    if(!isset($_COOKIE['loopYtConsent'])) {
       $parser->setHook( 'ev', 'LoopConsent::handleConsentPrompt' );
       $parser->setHook( 'youtube', 'LoopConsent::handleConsentPrompt' );
      }
        // $parser->setFunctionHook( "ev", "EmbedVideoHooks::parseEV" );
    // return true;
   // dd('asd');
    }
    
    public static function handleConsentPrompt( $input, array $args, Parser $parser, PPFrame $frame ) {
       // dd($parser);
        $out = '<div class="loop_consent">';
        $out .= '<p>Wenn Sie das Video starten, werden Inhalte von YouTube geladen und dadurch Ihre IP-Adresse an YouTube übertragen.</p>';
        $out .= '<button class="btn btn-dark btn-block border-0 loop_consent_agree">⯈ Video ansehen und nicht erneut fragen</button>';
        $out .= '</div>';
        
        return $out;
    }
}