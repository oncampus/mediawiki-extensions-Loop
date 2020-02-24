<?php
/**
  * @description Consent prompt for YouTube
  * @author Dustin Neß <dustin.ness@th-luebeck.de>
  */

  /*
  TODOS:
  - i18n für ES und SV
  - ggf. Support ausweiten neben <youtube>-Tag
  */

if( !defined( 'MEDIAWIKI' ) ) { die( "This file cannot be run standalone.\n" ); }

class LoopConsent {
    public static function onParserBeforeStrip( &$parser ) {
        if( !isset( $_COOKIE['LoopYtConsent'] ) ) {
            $parser->setHook( 'youtube', 'LoopConsent::handleConsentPrompt' );
        }
    }

    public static function handleConsentPrompt( $input, array $args, Parser $parser, PPFrame $frame ) {
        if ( preg_match( '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/\s]{11})%i', $input, $match ) ) {
            $video_id = $match[1];
        }
        
        $out = '<div class="loop_consent" style="background-image: url(https://img.youtube.com/vi/'. $video_id .'/maxresdefault.jpg)">';
        $out .= '<div class="loop_consent_text"><p>' . wfMessage('loopconsent-text') . '</p>';
        $out .= '<button class="btn btn-dark btn-block border-0 loop_consent_agree">⯈ ' . wfMessage('loopconsent-button') . '</button>';
        $out .= '</div></div>';
        
        return $out;
    }

    public static function onPageRenderingHash( &$confstr, $user, &$optionsUsed ) {
        $loopYtConsentVal  = $user->getOption( 'LoopYtConsent' );
      
        if( isset( $loopYtConsentVal ) && ( in_array( $loopYtConsentVal, array( "0", "1" ) ) ) ) {
            if( isset( $_COOKIE['LoopYtConsent'] ) ) {
                $confstr .= "!LoopYtConsent=true";
            } else {
                $confstr .= "!LoopYtConsent=false";
            }
        }

      return true;
    }
}