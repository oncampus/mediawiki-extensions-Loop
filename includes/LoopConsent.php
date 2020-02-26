<?php
/**
  * @description Consent prompt for YouTube, Vimeo and H5P
  * @author Dustin Neß <dustin.ness@th-luebeck.de>
  */

  /*
  TODOS:
  - i18n für ES und SV
  */

if( !defined( 'MEDIAWIKI' ) ) { die( "This file cannot be run standalone.\n" ); }

class LoopConsent {

    public static function onParserBeforeStrip( &$parser ) {
        if( !isset( $_COOKIE['LoopYtConsent'] ) ) {
            $parser->setHook( 'youtube', 'LoopConsent::parseYoutube' );     // <youtube>
            $parser->setHook( 'embedvideo', 'LoopConsent::parseYoutube' );  // <embedvideo>
            $parser->setHook('h5p', 'LoopConsent::parseH5P');               // <h5p>
            $parser->setFunctionHook( 'ev', 'LoopConsent::parseEv' );       // {{#ev}}
            $parser->setFunctionHook( 'evt', 'LoopConsent::parseEv' );      // {{#evt}}
            $parser->setFunctionHook( 'evu', 'LoopConsent::parseEvu' );     // {{#evu}}
        }
    }


    public static function parseH5P( $input, array $args, Parser $parser, PPFrame $frame ) {
        $lc = new LoopConsent();

        return $lc->renderOutput( 'h5p' );

    }


    public static function parseYoutube( $input, array $args, Parser $parser, PPFrame $frame ) {
        $lc = new LoopConsent();

        return $lc->renderOutput( $lc->getYouTubeId( $input ) );
    }


    public static function parseEv( $parser, $callback, $flags) {
        $lc = new LoopConsent();

        if($callback == 'youtube') {
            return [
                $lc->renderOutput( $lc->getYouTubeId( $flags ), $callback ),
                'noparse'=> true,
                'isHTML' => true
            ];
        } else if ($callback == 'vimeo') {
            return [
                $lc->renderOutput( $flags, $callback ),
                'noparse'=> true,
                'isHTML' => true
            ];
        }
    }


    public static function parseEvu( $parser, $callback, $flags ) {
        $lc = new LoopConsent();

        return [
            $lc->renderOutput( $lc->getYouTubeId( $callback ) ),
            'noparse'=> true,
            'isHTML' => true
        ];
    }

    
    private function renderOutput( $id, $service = '' ) {

        $url = '';
        $title = '';

        if( $service == 'youtube' ) {
            $url = "https://img.youtube.com/vi/{$id}/maxresdefault.jpg";
            $title = 'YouTube';
        } else if ( $service == 'vimeo' ) {
            $vimeoApi = json_decode( file_get_contents( 'http://vimeo.com/api/oembed.json?url=http://www.vimeo.com/' . $id ) );
            $url = $vimeoApi->thumbnail_url;
            $title = 'Vimeo';   
        } else if ( $id == 'h5p' ) {
            $title = 'H5P';
        }

        $out = '<div class="loop_consent" style="background-image: url(' . $url . ')">';
        $out .= '<div class="loop_consent_text"><h4>' . $title . '</h4><p>' . wfMessage('loopconsent-text') . '</p>';
        $out .= '<button class="btn btn-dark btn-block border-0 loop_consent_agree">⯈ ' . wfMessage('loopconsent-button') . '</button>';
        $out .= '</div></div>';
 
        return $out;
    }


    private function getYouTubeId( $url ) {
        if ( preg_match( '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/\s]{11})%i', $url, $match ) ) {
            return $match[1];
        }

        return false;
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