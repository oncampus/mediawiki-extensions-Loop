<?php

class LoopHtml{

    protected static $_instance = null;

    public static function getInstance() {

       if (null === self::$_instance) {
           self::$_instance = new self;
       }

       return self::$_instance;

   }

   private $requestedUrls = array();
   private $exportDirectory;

    public static function structure2html(LoopStructure $loopStructure, RequestContext $context, $exportDirectory) {
        //set_time_limit(0);
//dd("todo dauert zu lange!");

        $loopStructureItems = $loopStructure->getStructureItems();

        if(is_array($loopStructureItems)) {

            global $wgOut, $wgDefaultUserOptions, $wgResourceLoaderDebug, $wgUploadDirectory;

            LoopHtml::getInstance()->exportDirectory = $wgUploadDirectory.$exportDirectory.'/'.$loopStructure->getId().'/';

            # prepare global config
            $renderModeBefore = $wgOut->getUser()->getOption( 'LoopRenderMode', $wgDefaultUserOptions['LoopRenderMode'], true );
            $debugModeBefore = $wgResourceLoaderDebug;
            $wgOut->getUser()->setOption( 'LoopRenderMode', 'offline' );
            $wgResourceLoaderDebug = true;

            # Todo $this->copyResources();

            $exportSkin = clone $context->getSkin();

            foreach($loopStructureItems as $loopStructureItem) {

                $articleId = $loopStructureItem->getArticle();

                if( isset( $articleId ) && is_numeric( $articleId )) {

                    $title = Title::newFromID( $articleId );
                    $wikiPage = WikiPage::factory( $title );
                    $revision = $wikiPage->getRevision();
                    $content = $revision->getContent( Revision::RAW );
                    
		            $localParser = new Parser();
                    $text = $localParser->parse(ContentHandler::getContentText( $content ), $title, new ParserOptions())->mText;
                    //$text = ContentHandler::getContent( $content );
                    $htmlFileName = LoopHtml::getInstance()->exportDirectory.$title->mUrlform.'.html';


                    # prepare skin
                    $exportSkin->getContext()->setTitle( $title );
                    $exportSkin->getContext()->setWikiPage( $wikiPage );
                    $exportSkin->getContext()->getOutput()->mBodytext = $text;

                    # get html with skin object
                    ob_start();
                    $exportSkin->outputPage();
                    $html = ob_get_contents();
                    ob_end_clean();

                    $html = LoopHtml::getInstance()->replaceResourceLoader($html);
                    $html = LoopHtml::getInstance()->replaceTemplateLinks($html);
                    $html = LoopHtml::getInstance()->replaceContent($html);
                    file_put_contents($htmlFileName, $html);
                   
                    //dd($html);
                }

            }
            dd($html);

        }

    }

    private function replaceResourceLoader($html) {

        global $wgServer, $wgDefaultUserOptions, $wgResourceModules;

        $requestUrls = array();

        libxml_use_internal_errors(true);
        
        # suppress error message in console for mw.loader not working
        $html = preg_replace('/mw.loader.load\(RLPAGEMODULES\);/', '/*mw.loader.load\(RLPAGEMODULES\);*/', $html);

        $doc = new DOMDocument();
        $doc->loadHtml($html);
        
        if ( !file_exists( $this->exportDirectory ) ) {
            mkdir( $this->exportDirectory, 0775, true );
        }

        $linkElements = $doc->getElementsByTagName('link');
        if( $linkElements ) {
            foreach($linkElements as $link) {
                $tmpHref = $link->getAttribute( 'href' );
                if(strpos($tmpHref, 'load.php') !== false) {
                    $requestUrls[] = $wgServer.$tmpHref;
                    $link->setAttribute('href', "resources/styles/".md5($wgServer.$tmpHref).'.css');
                }
            }
        }

        # request contents for all matched <link> urls
        $requestUrls = $this->requestContent( $requestUrls );
        foreach ( $requestUrls as $url => $content ) {
            # Undoing MW's absolute paths in CSS files
            $content = preg_replace('/(\/mediawiki\/skins\/Loop\/resources\/)/', '../', $content);
            $fileName = $this->resolveUrl( $url, '.css' );
            $this->writeFile( "resources/styles/", $fileName, $content );
        }

        # reset container for <script> hrefs
        $requestUrls = array();

        $scriptElements = $doc->getElementsByTagName('script');
        if($scriptElements) {
            foreach($scriptElements as $script) {
                $tmpScript = $script->getAttribute( 'src' );
                if(strpos($tmpScript, 'load.php') !== false) {
                    $requestUrls[] = $wgServer.$tmpScript;
                    $script->setAttribute('src', "resources/js/".md5($wgServer.$tmpScript).'.js');
                }
            }
        }

        # request contents for all matched <link> urls
        $requestUrls = $this->requestContent($requestUrls);
        foreach($requestUrls as $url => $content) {
            $fileName = $this->resolveUrl($url, '.js');
            $this->writeFile( "resources/js/", $fileName, $content );
        }

        $skinPath = $wgServer . "/mediawiki/skins/";
        $extPath = $wgServer . "/mediawiki/extensions/";

        # Files that are called from our resources (e.g. in some css or js file) need to be added manually
        # - will be extended by skin files and resource modules
        $resources = array(
            "jquery.js" => array(
                "srcpath" => $wgServer . "/mediawiki/resources/lib/jquery/jquery.js",
                "targetpath" => "resources/js/",
                "link" => "script"
            ),
            "shared.css" => array(
                "srcpath" => $wgServer . "/mediawiki/resources/src/mediawiki.legacy/shared.css",
                "targetpath" => "resources/style/",
                "link" => "style"
            ),
            "loopfont.eot" => array(
                "srcpath" => $skinPath."Loop/resources/loopicons/fonts/loopfont.eot",
                "targetpath" => "resources/loopicons/fonts/",
            ),
            "loopfont.svg" => array(
                "srcpath" => $skinPath."Loop/resources/loopicons/fonts/loopfont.svg",
                "targetpath" => "resources/loopicons/fonts/",
            ),
            "loopfont.ttf" => array(
                "srcpath" => $skinPath."Loop/resources/loopicons/fonts/loopfont.ttf",
                "targetpath" => "resources/loopicons/fonts/",
            ),
            "loopfont.woff" => array(
                "srcpath" => $skinPath."Loop/resources/loopicons/fonts/loopfont.woff",
                "targetpath" => "resources/loopicons/fonts/",
            ),
            "32px.png" => array(
                "srcpath" => $skinPath."Loop/resources/js/jstree/dist/themes/default/32px.png",
                "targetpath" => "resources/js/jstree/dist/themes/default/",
            ),
            "throbber.gif" => array(
                "srcpath" => $skinPath."Loop/resources/js/jstree/dist/themes/default/throbber.gif",
                "targetpath" => "resources/js/jstree/dist/themes/default/",
            )
        );

        $skinStyle = $wgDefaultUserOptions["LoopSkinStyle"];
        $skinFolder = "resources/styles/$skinStyle/img/";
        $skinFiles = scandir("skins/Loop/$skinFolder");
        $skinFiles = array_slice($skinFiles, 2);
        
        foreach( $skinFiles as $file => $data ) {
            $resources[$data] = array(
                "srcpath" => "skins/Loop/$skinFolder$data",
                "targetpath" => $skinFolder
            );
        
        }
        # load resourcemodules from skin and extension json
        
        $resourceModules = $wgResourceModules;
        
        $requiredModules = array("skin" => array(), "ext" => array() );
        # lines encaptured by ", start with skin.loop or ext.loop and end with .js 
        # js modules are missing, so we fetch those.
        preg_match_all('/"(([skins]{5}\.loop.*\S*\.js))"/', $html, $requiredModules["skin"]);
        preg_match_all('/"(([ext]{3}\.loop.*\S*\.js))"/', $html, $requiredModules["ext"]);


        
        # adds modules that have been declared for resourceloader on $doc to our $resources array.
        foreach ( $requiredModules as $type => $res ) { // skin or ext?

            foreach ( $res[1] as $module => $modulename ) { 
            
                if ( isset($resourceModules[$modulename]["scripts"]) ) { // does our requested module have scripts?

                    foreach( $resourceModules[$modulename]["scripts"] as $pos => $scriptpath ) { // include all scripts
                        if ( $type == "skin" ){
                            $sourcePath = $skinPath . $resourceModules[$modulename]["remoteSkinPath"]."/";
                        } else {
                            $sourcePath = $extPath . $resourceModules[$modulename]["remoteExtPath"]."/";
                        }

                        $resources[$modulename] = array(
                            "srcpath" => $sourcePath . $scriptpath,
                            "targetpath" => "resources/js/",
                            "link" => "script"
                        );
                    }
                }
            }
        }

        $headElements = $doc->getElementsByTagName('head');

        # request contents for all entries in $resources array,
        # writes file in it's targetpath and links it on output page.
        foreach( $resources as $file => $data ) {
            $tmpContent[$file]["content"] =  file_get_contents( $data["srcpath"] );
            
            $this->writeFile( $data["targetpath"], $file, $tmpContent[$file]["content"] );
            
            if ( isset ( $data["link"] ) )  { # add file to output page if requested
                if ($data["link"] == "style") {
                    $tmpNode = $doc->createElement("link");
                    $tmpNode->setAttribute('href', $data["targetpath"] . $file );
                    $tmpNode->setAttribute('rel', "stylesheet" );
                } else if ( $data["link"] == "script" ) {
                    $tmpNode = $doc->createElement("script");
                    $tmpNode->setAttribute('src', $data["targetpath"] . $file );
                }
                foreach( $headElements as $headElement) {
                    $headElement->appendChild( $tmpNode );
                }
            }

        }

        $html = $doc->saveHtml();
        libxml_clear_errors();

        return $html;
    }

    private function replaceTemplateLinks( $html ) {

        $doc = new DOMDocument();
        $doc->loadHtml($html);
        
        $loopSettings = new LoopSettings();
        $loopSettings->loadSettings();
        
        if ( !file_exists( $this->exportDirectory ) ) {
            mkdir( $this->exportDirectory, 0775, true );
        }

        # replace TOC Sidebar links
        $body = $doc->getElementsByTagName('body');
        $internalLinks = $this->getElementsByClass( $body[0], "a", "internal-link" );
        $this->bulkReplaceHref( $internalLinks );
        //dd($internalLinks);
/*
        # replace top navigation links
        $topNavigation = $doc->getElementById('top-nav');
        $navElements = $topNavigation->childNodes;
        $this->bulkReplaceHref( $navElements );
        
        # replace bottom navigation links
        $bottomNavigation = $doc->getElementById('bottom-nav');
        $navElements = $bottomNavigation->childNodes;
        $this->bulkReplaceHref( $navElements );

        # replace breadcrumb links
        $breadcrumbSection = $doc->getElementById('breadcrumb-area');
        //$breadcrumbElements = $breadcrumbSection->childNodes;
        $breadcrumbElements = $this->getElementsByClass( $breadcrumbSection, "a", "breadcrumb-link" );
        $this->bulkReplaceHref( $breadcrumbElements );

        # replace Loop Title Link
        $loopTitleLink = $doc->getElementById('loop-title');
        $loopTitleHref = $loopTitleLink->getAttribute( 'href' );
        $loopTitleLink->setAttribute( 'href', $this->makeLocalHref( $loopTitleHref ) );
        
        # replace Loop Logo Link
        $loopLogoLink = $doc->getElementById('loop-logo');
        $loopLogoHref = $loopLogoLink->getAttribute( 'href' );
        $loopLogoLink->setAttribute( 'href', $this->makeLocalHref( $loopLogoHref ) );
*/
        # apply custom logo, if given
        if ( !empty ( $loopSettings->customLogo ) ) {
            $loopLogo = $doc->getElementById('logo');
            $logoUrl = $loopSettings->customLogoFilePath;
            $logoFile = $this->requestContent( array($logoUrl) );
            $fileName = $loopSettings->customLogoFileName; 
            $this->writeFile( "resources/images/", $fileName, $logoFile[$logoUrl] );
            $loopLogo->setAttribute( 'style', 'background-image: url("resources/images/'. $fileName.'");' );
        }        

        $html = $doc->saveHtml();
        return $html;
    }

    /**
     * Requests urls and returns an array.
     * @Return Array ($url => $content)
     */
    private function requestContent (Array $urls) : Array {
        $tmpContent = array();

        foreach($urls as $url) {

            if( ! in_array( $url, $this->requestedUrls ) ) {
                $content = file_get_contents( $url );
                $this->requestedUrls[ $url ] = $content;
            }

            $tmpContent[ $url ] = $this->requestedUrls[ $url ];

        }

        return $tmpContent;

    }

    private function resolveUrl($url, $suffix) {
        global $wgServer;
        return md5($url).$suffix;
    }

    private function getElementsByClass( &$parentNode, $tagName, $className ) {

        $nodes = array();
    
        $childNodeList = $parentNode->getElementsByTagName( $tagName );
        for ( $i = 0; $i < $childNodeList->length; $i++ ) {
            $temp = $childNodeList->item( $i );
            if ( stripos( $temp->getAttribute( 'class' ), $className ) !== false ) {
                $nodes[] = $temp;
            }
        }
    
        return $nodes;
    }

    private function bulkReplaceHref ( $elements ) {

        if ( $elements ) {
            foreach ( $elements as $element ) {
                $tmpHref = $element->getAttribute( 'href' );
                $newHref = $this->makeLocalHref( $tmpHref );
                $element->setAttribute( 'href', $newHref );
            }
        }
        
        return true;
    }

    private function makeLocalHref( $href ) {

        if ( $href != '#' ) {
            $href = preg_replace( '/(.*\/index.php\/)/', '', $href ) . ".html";
        }
        return $href;
    }

    private function writeFile( $pathAddendum, $fileName, $content ) {
        
        if ( ! file_exists( $this->exportDirectory.$pathAddendum ) ) { # folder creation
            mkdir( $this->exportDirectory.$pathAddendum, 0775, true );
        }
        if ( ! file_exists( $this->exportDirectory.$fileName ) ) {
            file_put_contents($this->exportDirectory.$pathAddendum.$fileName, $content);
        }
        return true;
    }

    private function replaceContent( $html ) {
        global $wgServer;

        $doc = new DOMDocument();
        $doc->loadHtml($html);

        # replace breadcrumb links
        //$pageContent = $doc->getElementById('page-content');
        $body = $doc->getElementsByTagName('body');

        $imageElements = $this->getElementsByClass( $body[0], "img", "responsive-image" );
        $imageUrls = array();
        if ( $imageElements ) {
            foreach ( $imageElements as $element ) {

                $tmpSrc = $element->getAttribute( 'src' );
                $imageData["content"][] = $wgServer . $tmpSrc;
                preg_match('/(.*\/)(.*\.{1}.*)/', $tmpSrc, $tmpTitle);
                $imageData["names"][$wgServer . $tmpSrc] = $tmpTitle[2];
                $newSrc = "resources/images/" . $tmpTitle[2];
                $element->setAttribute( 'src', $newSrc );
            }
        }
        $imageData["content"] = $this->requestContent($imageData["content"]);
        if ( $imageData["names"] ) {
            foreach ( $imageData["names"] as $image => $data ) {
                $fileName = $imageData["names"][$image];
                $content = $imageData["content"][$image];
                $this->writeFile( 'resources/images/', $fileName, $content );
            }
        }




        $html = $doc->saveHtml();
        return $html;

    }
}
