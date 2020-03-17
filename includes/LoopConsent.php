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
        
        if( !isset( $_COOKIE['LoopConsent'] )) {
            $parser->setHook( 'youtube', 'LoopConsent::parseYoutube' );     // <youtube>
            $parser->setHook( 'embedvideo', 'LoopConsent::parseYoutube' );  // <embedvideo>
            $parser->setHook('h5p', 'LoopConsent::parseH5P');               // <h5p>
            $parser->setFunctionHook( 'ev', 'LoopConsent::parseEv' );       // {{#ev}}
            $parser->setFunctionHook( 'evt', 'LoopConsent::parseEv' );      // {{#evt}}
            $parser->setFunctionHook( 'evu', 'LoopConsent::parseEvu' );     // {{#evu}}

            return true;
        } else {
            global $wgOut;
            $wgOut->enableClientCache(false);
            //zum testen Inhalt von onPageRenderingHash() auskommentieren 

            // if(filter_input(INPUT_GET, 'consent', FILTER_SANITIZE_URL)) {
            //     if($parser->getTitle()->mArticleID != 0) {
            //         $page = WikiPage::factory( $parser->getTitle() );
            //         $page->doPurge();
            //         header("Refresh:0; url=" . $_SERVER['PHP_SELF']); // also strips URL params
            //     }
            // }
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

    
    private function renderOutput( $id, $service = 'youtube' ) {

        global $wgResourceBasePath;
        
        $url = '';
        $title = '';

        if ( $id == 'h5p' ) {
            $title = 'H5P';
            $url = $wgResourceBasePath.'/skins/Loop/resources/img/bg_h5p.jpg';
        } else {
            if( $service == 'youtube' ) {
                $url = "https://img.youtube.com/vi/{$id}/maxresdefault.jpg";
                $title = 'YouTube';
            } else if ( $service == 'vimeo' ) {
                $vimeoApi = json_decode( file_get_contents( 'http://vimeo.com/api/oembed.json?url=http://www.vimeo.com/' . $id ) );
                $url = $vimeoApi->thumbnail_url;
                $title = 'Vimeo';   
            }
        }
        
        $out = '<div class="loop_consent" style="background-image: url(' . $url . ')">';
        $out .= '<div class="loop_consent_text"><h4>' . $title . '</h4><p>' . wfMessage('loopconsent-text') . '</p>';
        $out .= '<button class="btn btn-dark btn-block border-0 loop_consent_agree"><span class="ic ic-page-next"></span> ' . wfMessage('loopconsent-button') . '</button>';
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

        // nur eingeloggte User, nicht für Nichteingeloggte
        // Cache manuell löschen wenn Consent-Button geklickt, funktioniert dann

       # $loopConsentVal  = $user->getOption( 'LoopConsent' );
      
        // if( isset( $loopConsentVal ) && ( in_array( $loopConsentVal, array( "0", "1" ) ) ) ) {
        //     if( isset( $_COOKIE['LoopConsent'] ) ) {
        //         $confstr .= "!LoopConsent=true";
        //     } else {
        //         $confstr .= "!LoopConsent=false";
        //     }
        // }
        #if( isset( $loopConsentVal ) && ( in_array( $loopConsentVal, array( "0", "1" ) ) ) ) {
        if( isset( $_COOKIE['LoopConsent'] ) ) {
            $confstr .= "!loopconsent=true";
        } else {
            $confstr .= "!loopconsent=false";
        }
        #}

       # dd($optionsUsed,$confstr);
      return true;
    }

    public static function onParserOptionsRegister( &$defaults, &$inCacheKey, &$lazyLoad ) {
        #();
        $defaults["loopconsent"] = false;
        if( isset( $_COOKIE['LoopConsent'] ) ) {
            $inCacheKey["loopconsent"] = true;
        } else {
            $inCacheKey["loopconsent"] = false;
        }
       # dd(isset( $_COOKIE['LoopConsent'] ), $defaults, $inCacheKey, $lazyLoad);
        #return true;
    }
}