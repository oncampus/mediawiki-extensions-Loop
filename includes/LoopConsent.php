<?php
/**
  * @description Consent prompt for YouTube
  * @author Dustin Neß <dustin.ness@th-luebeck.de>
  */


 //include $_SERVER['DOCUMENT_ROOT'].'/extensions/EmbedVideo/EmbedVideo.hooks.php';

if( !defined( 'MEDIAWIKI' ) ) { die( "This file cannot be run standalone.\n" ); }

class LoopConsent {
	public static function onParserBeforeStrip( &$parser ) {
    global $wgOut;

    if(!isset($_COOKIE['loopYtConsent'])) {
       $parser->setHook( 'ev', 'LoopConsent::handleConsentPrompt' );
       $parser->setHook( 'youtube', 'LoopConsent::handleConsentPrompt' );
      }
    
      // $setting1= (int) $parser->getUser()->getOption('loopYtConsent');
      // $parser->getOptions()->optionUsed( 'loopYtConsent' );

      
		$user = $wgOut->getUser();
		$consent = $user->getOption( 'loopYtConsent', false, true );
    $parser->getOptions()->optionUsed( 'loopYtConsent' );
    

    }
    
    public static function handleConsentPrompt( $input, array $args, Parser $parser, PPFrame $frame ) {
      if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/\s]{11})%i', $input, $match)) {
        $video_id = $match[1];
      }
        
        $out = '<div class="loop_consent" style="background-image: url(https://img.youtube.com/vi/'. $video_id .'/maxresdefault.jpg)">';
        $out .= '<div class="loop_consent_text"><p>Wenn Sie das Video starten, werden Inhalte von YouTube geladen und dadurch Ihre IP-Adresse an YouTube übertragen.</p>';
        $out .= '<button class="btn btn-dark btn-block border-0 loop_consent_agree">⯈ Video ansehen und nicht erneut fragen</button>';
        $out .= '</div></div>';
        
        return $out;
    }

    public static function onPageRenderingHash( &$confstr, $user, $optionsUsed ) {
    
      global $wgDefaultUserOptions;

      $consent = $user->getOption( 'loopYtConsent', false, true );
  
      if ( $consent ) {
        $confstr .= "!loopYtConsent=true";
      } else {
        $confstr .= "!loopYtConsent=false";
      }

    }
}