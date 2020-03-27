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

    public static function onPageContentSave( $wikiPage, $user, $content, &$summary, $isMinor, $isWatch, $section, $flags, $status ) {
        $tags = ['ev:youtube', 'embedvideo', 'ev', 'evt', 'evu'];
        // dd($wikiPage, $user, $content,  $summary, $isMinor, $isWatch, $section, $flags, $status);
        //check if video tag exists on article
        foreach( $tags as $tag) {
            if( strpos('<'.$content->getText(), $tag) || strpos('{{'.$content->getText(), $tag)) {
                // dd($wikiPage);
                dd(LoopConsent::getTags($content->getText()));
            }
        }

    }

    public static function getTags( $content ) {
        $return = [];
        $curlyMatches = [];
        $angleMatches = [];

        // get {{}} tags
        if ( preg_match_all( '/{{([^}]+)}}/', $content, $matches ) ) {
            $curlyMatches = $matches[1];

            foreach( $curlyMatches as $m ) {
                if( strpos( $m, 'vimeo' ) ) {
                    $id = explode( '|', $m )[1];
                    $return['vimeo'][] = $id;
                }

                if( strpos( $m, 'youtube' ) ) {
                    $id = explode( '|', $m )[1];
                    $return['youtube'][] = substr( strstr( $id, '=' ), 1 );
                }
            }
            
        }

        // get <> tags
        $allowedTags = ['youtube', 'embedvideo'];

        foreach($allowedTags as $tag) {
            $tag = preg_quote($tag);
            if ( preg_match_all( "/(<$tag.*?>)(.*?)(<\/$tag>)/", $content, $matches ) ) {
                $angleMatches = $matches[2];
    
                foreach($angleMatches as $m) {
                    if( strpos( $m, 'youtube' ) ) {
                        $return['youtube'][] = substr($m, strrpos($m, '=') + 1);
                    }
                    if( strpos( $m, 'vimeo' ) ) {
                        $return['vimeo'][] = substr($m, strrpos($m, '/') + 1);
                    }
                }
            }
        }

        return $return;
    }

    public static function onParserBeforeStrip( &$parser ) {   
        
        global $wgH5PHostUrl;

        if( !isset( $_COOKIE['LoopConsent'] )) {
            $parser->setHook( 'youtube', 'LoopConsent::parseYoutube' );     // <youtube>
            $parser->setHook( 'embedvideo', 'LoopConsent::parseYoutube' );  // <embedvideo>
        
            if( $wgH5PHostUrl == 'https://h5p.com/h5p/embed/' ) {
                $parser->setHook('h5p', 'LoopConsent::parseH5P');               // <h5p>
            }
            
            $parser->setFunctionHook( 'ev', 'LoopConsent::parseEv' );       // {{#ev}}
            $parser->setFunctionHook( 'evt', 'LoopConsent::parseEv' );      // {{#evt}}
            $parser->setFunctionHook( 'evu', 'LoopConsent::parseEvu' );     // {{#evu}}

            return true;
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

        global $wgResourceBasePath, $wgOut;

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
        $out .= '</div></div>z';
 
        return $out;
    }


    private function getYouTubeId( $url ) {
        if ( preg_match( '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/\s]{11})%i', $url, $match ) ) {
            return $match[1];
        }

        return false;
    }


    public static function onPageRenderingHash( &$confstr, $user, &$optionsUsed ) {
        if ( isset( $_COOKIE['LoopConsent'] ) ) {
            $confstr .= "!loopconsent=true";
        } else {
            $confstr .= "!loopconsent=false";
        }
        
        return true;
    }


    public static function onParserOptionsRegister( &$defaults, &$inCacheKey, &$lazyLoad ) {
        $defaults["loopconsent"] = false;
        $inCacheKey["loopconsent"] = isset( $_COOKIE['LoopConsent'] ) ? true : false;
    }

}