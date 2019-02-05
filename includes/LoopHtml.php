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

//dd("todo dauert zu lange!");

        $loopStructureItems = $loopStructure->getStructureItems();

        if(is_array($loopStructureItems)) {

            global $wgOut, $wgDefaultUserOptions, $wgResourceLoaderDebug, $wgUploadDirectory;

            LoopHtml::getInstance()->exportDirectory = $wgUploadDirectory.$exportDirectory.'/'.$loopStructure->getId().'/';

            # prepare global config
            $renderModeBefore = $wgOut->getUser()->getOption( 'LoopRenderMode', $wgDefaultUserOptions['LoopRenderMode'], true );;
            $debugModeBefore = $wgResourceLoaderDebug;
            $wgDefaultUserOptions['LoopRenderMode'] = 'offline';
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
                    $text = ContentHandler::getContentText( $content );
                    $htmlFileName = LoopHtml::getInstance()->exportDirectory.$title->mTextform.'.html';


                    # prepare skin
                    $exportSkin->getContext()->setTitle( $title );
                    $exportSkin->getContext()->setWikiPage( $wikiPage );
                    $exportSkin->getContext()->getOutput()->mBodytext = $text;

                    # get html with skin object
                    ob_start();
                    $exportSkin->outputPage();
                    $html = ob_get_contents();
                    ob_end_clean();

                    $html = LoopHtml::getInstance()->replaceLoadPhp($html);
                    file_put_contents($htmlFileName, $html);
dd($html);
                }

            }

        }

    }

    private function replaceLoadPhp($html) {

        global $wgServer;

        $requestUrls = array();

        libxml_use_internal_errors(true);
        $doc = new DOMDocument();
        $doc->loadHtml($html);

        $linkElements = $doc->getElementsByTagName('link');
        if($linkElements) {
            foreach($linkElements as $link) {
                $tmpHref = $link->getAttribute( 'href' );
                if(strpos($tmpHref, 'load.php') !== false) {
                    $requestUrls[] = $wgServer.$tmpHref;
                    $link->setAttribute('href', md5($wgServer.$tmpHref).'.css');
                }
            }
        }

        # request contents for all matched <link> urls
        $requestUrls = $this->requestContent($requestUrls);
        foreach($requestUrls as $url => $content) {
            $fileName = $this->resolveUrl($url, '.css');
            if(!file_exists($this->exportDirectory.$fileName)) {
                file_put_contents($this->exportDirectory.$fileName, $content);
            }
        }

        # reset container for <script> hrefs
        $requestUrls = array();

        $scriptElements = $doc->getElementsByTagName('script');
        if($scriptElements) {
            foreach($scriptElements as $script) {
                $tmpScript = $script->getAttribute( 'src' );
                if(strpos($tmpScript, 'load.php') !== false) {
                    $requestUrls[] = $wgServer.$tmpScript;
                    $script->setAttribute('src', md5($wgServer.$tmpScript).'.js');
                }
            }
        }

        # request contents for all matched <link> urls
        $requestUrls = $this->requestContent($requestUrls);
        foreach($requestUrls as $url => $content) {
            $fileName = $this->resolveUrl($url, '.js');
            if(!file_exists($this->exportDirectory.$fileName)) {
                file_put_contents($this->exportDirectory.$fileName, $content);
            }
        }

        $html = $doc->saveHtml();
        libxml_clear_errors();

        return $html;
    }

    /**
     * Requests urls and returns an array.
     * @Return Array ($url => $content)
     */
    private function requestContent(Array $urls) : Array {

        $tmpContent = array();

        foreach($urls as $url) {

            if(!in_array($url, $this->requestedUrls)) {
                $content = file_get_contents($url);
                $this->requestedUrls[$url] = $content;
            }

            $tmpContent[$url] = $this->requestedUrls[$url];

        }

        return $tmpContent;

    }

    private function resolveUrl($url, $suffix) {
        global $wgServer;
        return md5($wgServer.$url).$suffix;
    }


}
