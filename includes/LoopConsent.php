<?php
/**
  * @description Consent prompt for YouTube, Vimeo and H5P
  * @author Dustin Neß <dustin.ness@th-luebeck.de>
  */

if( !defined( 'MEDIAWIKI' ) ) { die( "This file cannot be run standalone.\n" ); }

class LoopConsent {
    
    public static function onPageContentSave( $wikiPage, $user, $content, &$summary, $isMinor, $isWatch, $section, $flags, $status ) {
        $tags = [ '<youtube', 'service="youtube"', '#ev:youtube' ];

        foreach( $tags as $tag ) {
            if( strpos( $content->getText(), $tag ) !== false ) {
                LoopConsent::updateThumbnails( $content->getText(), $wikiPage->getTitle()->getArticleID() );
            }
        }
    }


    public static function onParserBeforeStrip( &$parser ) {   
        global $wgH5PHostUrl;

        if( !isset( $_COOKIE['LoopConsent'] )) {
            $parser->setHook( 'youtube', 'LoopConsent::parseTag' );     // <youtube>
            $parser->setHook( 'embedvideo', 'LoopConsent::parseTag' );  // <embedvideo>
            
            if( $wgH5PHostUrl == 'https://h5p.com/h5p/embed/' ) {
                $parser->setHook('h5p', 'LoopConsent::parseH5P');       // <h5p>
            }
            
            $parser->setFunctionHook( 'ev', 'LoopConsent::parseEv' );   // {{#ev}}
            $parser->setFunctionHook( 'evt', 'LoopConsent::parseEvt' ); // {{#evt}}
            $parser->setFunctionHook( 'evu', 'LoopConsent::parseEvu' ); // {{#evu}}

            return true;
        }
    }


    public static function parseH5P( $input, array $args, Parser $parser, PPFrame $frame ) {
        $lc = new LoopConsent();

        return $lc->renderOutput( 'h5p' );
    }


    public static function parseTag( $input, array $args, Parser $parser, PPFrame $frame ) {
        $lc = new LoopConsent();

        if( isset( $args['service'] ) ) {
            if( $args['service'] == 'vimeo' ) {
                return $lc->renderOutput( $lc->getVimeoId( $input ), 'vimeo' );
            } else {
                return $lc->renderOutput( $lc->getYouTubeId( $input ) );
            }
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
        } else if( $callback == 'vimeo' ) {
            return [
                $lc->renderOutput( $lc->getVimeoId( $flags ), $callback ),
                'noparse'=> true,
                'isHTML' => true
            ];
        }
    }


    public static function parseEvt( $parser, $callback, $flags ) {
        $lc = new LoopConsent();
     
        if( strpos( $callback, 'youtube' ) !== false) {
            return [
                $lc->renderOutput( $lc->getYouTubeId( $flags ), 'youtube' ),
                'noparse'=> true,
                'isHTML' => true
            ];
        } else if ( strpos( $callback, 'vimeo' ) !== false ) {
            return [
                $lc->renderOutput( $lc->getVimeoId( $flags ), 'vimeo' ),
                'noparse'=> true,
                'isHTML' => true
            ];
        }
    }


    public static function parseEvu( $parser, $callback ) {
        $lc = new LoopConsent();

        if( strpos( $callback, 'youtube' ) !== false ) {
            return [
                $lc->renderOutput( $lc->getYouTubeId( $callback ), 'youtube' ),
                'noparse'=> true,
                'isHTML' => true
            ];
        } else if ( strpos( $callback, 'vimeo' ) !== false ) {
            return [
                $lc->renderOutput( $lc->getVimeoId( $callback ), 'vimeo' ),
                'noparse'=> true,
                'isHTML' => true
            ];
        }
    }

    
    private function renderOutput( $id, $service = 'youtube' ) {
        global $wgCanonicalServer, $wgUploadPath;

        $url = '';
        $title = '';
        $bgColor = '';
        $h5pClass = '';

        if ( $id == 'h5p' ) {
            $title = '<span class="ic ic-h5p"></span>';
            $bgColor = '2575be';
            $h5pClass = 'is_h5p';
        } else {            
            // no thumbnail
            if ( strpos( $url, '/.jpg' ) || $service == 'vimeo' || $id == 'h5p' ) {
                $url = '';
            } else {
                $url = $wgCanonicalServer . $wgUploadPath . '/videothumbs/' . $id . '.jpg';
            }

            if( $service == 'youtube' ) {
                $title = 'YouTube';
                $bgColor = 'ff0000';
            } else if ( $service == 'vimeo' ) {
                $title = 'Vimeo';
                $bgColor = '1ab7ea';
            }
        }
        
        $out = '<div class="loop_consent ' . $h5pClass . '" style="background-image: url(' . $url . '); background-color: #' . $bgColor . ';">';
        $out .= '<div class="loop_consent_text"><h4>' . $title . '</h4><p>' . wfMessage('loopconsent-text')->text() . '</p>';
        $out .= '<button class="btn btn-dark btn-block border-0 loop_consent_agree"><span class="ic ic-page-next"></span> ' . wfMessage('loopconsent-button')->text() . '</button>';
        $out .= '</div></div>';
 
        return $out;
    }


    private function getYouTubeId( $url ) {
        if ( preg_match( '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/\s]{11})%i', $url, $match ) ) {
            return $match[1];
        } else {
            return $url; //assume already extracted youtube video ID
        }

        return false;
    }


    private function getVimeoId( $url ) {
        if ( preg_match( '/(?:www\.|player\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/(?:[^\/]*)\/videos\/|album\/(?:\d+)\/video\/|video\/|)(\d+)(?:[a-zA-Z0-9_\-]+)?/i', $url, $match ) ) {
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


    public static function updateThumbnails( $content, $articleId ) {
        global $wgUploadDirectory;

        $return = [];
        $curlyMatches = [];
        $angleMatches = [];

        // get {{}} tags
        if ( preg_match_all( '/{{([^}]+)}}/', $content, $matches ) ) {
            $curlyMatches = $matches[1];

            foreach( $curlyMatches as $match ) {
                if( strpos( $match, 'youtube' ) ) {
                    if( strpos($match, '#evt:') !== false ) {
                        $id = explode('|', $match)[1];
                        $id = preg_replace( "/[\n\r]/","", $id );
                        $return['youtube'][] = substr($id, strrpos($id, '=') + 1);
                    } else if( strpos($match, '#ev:') !== false ) {
                        $id = explode( '|', $match )[1];
                        $return['youtube'][] = $id;
                    } else { // evu
                        $lc = new LoopConsent();
                        $return['youtube'][] = $lc->getYouTubeId( $match );
                    }
                }
            }
        }

        // get <> tags
        $allowedTags = ['youtube', 'embedvideo'];

        foreach( $allowedTags as $tag ) {
            $tag = preg_quote($tag);
            if ( preg_match_all( "/(<$tag.*?>)(.*?)(<\/$tag>)/", $content, $matches ) ) {
                $angleMatches = $matches[2];
    
                foreach($angleMatches as $match) {
                    if( strpos( $match, 'youtube' ) ) {
                        $return['youtube'][] = substr($match, strrpos($match, '=') + 1);
                    }
                }
            }
        }

        $thumbStorePath = $wgUploadDirectory . '/videothumbs';

        if ( !file_exists( $thumbStorePath ) ) {
            mkdir( $thumbStorePath, 0755, true );
        }

        // update local thumbnails
        foreach ( $return as $key => $value ) {
            foreach( $value as $v ) {
                file_put_contents(
                    $thumbStorePath . '/' . $v . '.jpg',
                    file_get_contents( 'https://img.youtube.com/vi/' . $v . '/maxresdefault.jpg' ),
                    FILE_APPEND
                );
            }
        }

        return $return;
    }

}