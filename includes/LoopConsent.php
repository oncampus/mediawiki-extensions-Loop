<?php
/**
  * @description Consent prompt for YouTube, Vimeo and H5P
  * @author Dustin Neß <dustin.ness@th-luebeck.de>
  */

  /*
  TODOS:
  - i18n für ES und SV
  thumbPath
  */

if( !defined( 'MEDIAWIKI' ) ) { die( "This file cannot be run standalone.\n" ); }

class LoopConsent {

    private $thumbPath = '/images/videothumbs';
    
    public static function onPageContentSave( $wikiPage, $user, $content, &$summary, $isMinor, $isWatch, $section, $flags, $status ) {
        $tags = ['ev:youtube', 'embedvideo', 'ev', 'evt', 'evu'];

        //check if video tag exists on article
        foreach( $tags as $tag) {
            if( strpos('<'.$content->getText(), $tag) || strpos('{{'.$content->getText(), $tag)) {
                LoopConsent::updateThumbnails($content->getText(), $wikiPage->getTitle()->getArticleID());
            }
        }

    }

    public static function updateThumbnails( $content, $articleId ) {

        global $wgResourceBasePath;

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
        
        $thumbPath = getcwd() . $this->thumbPath;

        if (!file_exists(getcwd() . thumbPath)) {
            mkdir(getcwd() . thumbPath, 0755, true);
        }

        // update local thumbnails
        foreach ($return as $key => $value) {
            if($key === 'vimeo') {
                foreach($value as $v) {
                    $vimeoApi = json_decode( file_get_contents( 'http://vimeo.com/api/oembed.json?url=http://www.vimeo.com/' . $v ) );
                    file_put_contents(thumbPath.'/'.$v.'.jpg', file_get_contents($vimeoApi->thumbnail_url));
                }
            } else { // assume youtube
                foreach($value as $v) {
                    file_put_contents(thumbPath.'/'.$v.'.jpg', file_get_contents('https://img.youtube.com/vi/'.$v.'/maxresdefault.jpg'));
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

        global $wgResourceBasePath, $wgOut, $wgServer;

        $url = '';
        $title = '';

        if ( $id == 'h5p' ) {
            $title = 'H5P';
            $url = $wgResourceBasePath.'/skins/Loop/resources/img/bg_h5p.jpg';
        } else {
            $url = $wgServer . $this->thumbPath . '/' . $id . '.jpg';

            // no thumbnail
            if (strpos($url, '/.jpg')) {
                $url = '';
            }

            if( $service == 'youtube' ) {
                $title = 'YouTube';
            } else if ( $service == 'vimeo' ) {
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