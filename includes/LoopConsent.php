<?php
/**
  * @description Consent prompt for YouTube, Vimeo and H5P
  * @author Dustin Neß <dustin.ness@th-luebeck.de>
  */

if( !defined( 'MEDIAWIKI' ) ) { die( "This file cannot be run standalone.\n" ); }

class LoopConsent {
    
    public static function onPageContentSave( $wikiPage, $user, $content, &$summary, $isMinor, $isWatch, $section, $flags, $status ) {
        $tags = ['ev:youtube', 'embedvideo', 'ev', 'evt', 'evu'];

        foreach( $tags as $tag) {
            if( strpos('<'.$content->getText(), $tag) || strpos('{{'.$content->getText(), $tag)) {
                LoopConsent::updateThumbnails($content->getText(), $wikiPage->getTitle()->getArticleID());
            }
        }
    }


    public static function onParserBeforeStrip( &$parser ) {   
        
        global $wgH5PHostUrl;

        if( !isset( $_COOKIE['LoopConsent'] )) {
            $parser->setHook( 'youtube', 'LoopConsent::parseTag' );     // <youtube>
            $parser->setHook( 'embedvideo', 'LoopConsent::parseTag' );  // <embedvideo>
        
            if( $wgH5PHostUrl == 'https://h5p.com/h5p/embed/' ) {
                $parser->setHook('h5p', 'LoopConsent::parseH5P');           // <h5p>
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


    public static function parseTag( $input, array $args, Parser $parser, PPFrame $frame ) {
        $lc = new LoopConsent();
        if($args['service'] == 'vimeo') {
            return $lc->renderOutput( $lc->getVimeoId( $input ), 'vimeo' );
        } else {
            return $lc->renderOutput( $lc->getYouTubeId( $input ) );
        }
    }


    public static function parseEv( $parser, $callback, $flags ) {
        $lc = new LoopConsent();

        if( $callback == 'youtube' ) {
            return [
                $lc->renderOutput( $lc->getYouTubeId( $flags ), $callback ),
                'noparse'=> true,
                'isHTML' => true
            ];
        } else if ( $callback == 'vimeo' ) {
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
            $url = $wgServer . '/images/videothumbs/' . $id . '.jpg';

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
        } else {
            return $url; //already extracted youtube video ID
        }

        return false;
    }

    private function getVimeoId( $url ) {
        if ( preg_match( '/(?:www\.|player\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/(?:[^\/]*)\/videos\/|album\/(?:\d+)\/video\/|video\/|)(\d+)(?:[a-zA-Z0-9_\-]+)?/i', $url, $match ) ) {
            return $match[1];
        } else {
            return $url; //already extracted youtube video ID
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

    public static function updateThumbnails( $content, $articleId ) {

        global $wgResourceBasePath;

        $return = [];
        $curlyMatches = [];
        $angleMatches = [];

        // get {{}} tags
        if ( preg_match_all( '/{{([^}]+)}}/', $content, $matches ) ) {
            $curlyMatches = $matches[1];

            foreach( $curlyMatches as $match ) {
                if( strpos( $match, 'vimeo' ) ) {
                    $id = explode( '|', $match )[1];
                    $return['vimeo'][] = $id;
                }

                if( strpos( $match, 'youtube' ) ) {
                    $id = explode( '|', $match )[1];
                    $return['youtube'][] = $id;
                }
            }
            
        }

        // get <> tags
        $allowedTags = ['youtube', 'embedvideo'];

        foreach($allowedTags as $tag) {
            $tag = preg_quote($tag);
            if ( preg_match_all( "/(<$tag.*?>)(.*?)(<\/$tag>)/", $content, $matches ) ) {
                $angleMatches = $matches[2];
    
                foreach($angleMatches as $match) {
                    if( strpos( $match, 'youtube' ) ) {
                        $return['youtube'][] = substr($match, strrpos($match, '=') + 1);
                    }
                    if( strpos( $match, 'vimeo' ) ) {
                        $return['vimeo'][] = substr($match, strrpos($match, '/') + 1);
                    }
                }
            }
        }

        $thumbStorePath = getcwd() . '/images/videothumbs';

        if ( !file_exists( $thumbStorePath ) ) {
            mkdir( $thumbStorePath, 0755, true );
        }

        // update local thumbnails
        foreach ( $return as $key => $value ) {
            if( $key === 'vimeo' ) {
                foreach( $value as $v ) {
                    $vimeoApi = json_decode( file_get_contents( 'http://vimeo.com/api/oembed.json?url=http://www.vimeo.com/' . $v ) );
                    file_put_contents(
                        $thumbStorePath . '/' . $v . '.jpg',
                        file_get_contents( $vimeoApi->thumbnail_url ),
                        FILE_APPEND
                    );
                }
            } else { // assume youtube
                foreach( $value as $v ) {
                    file_put_contents(
                        $thumbStorePath . '/' . $v . '.jpg',
                        file_get_contents( 'https://img.youtube.com/vi/' . $v . '/maxresdefault.jpg' ),
                        FILE_APPEND
                    );
                }
            }
        }

        return $return;
    }

}