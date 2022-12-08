<?php
/**
 * @description Exports LOOP to HTML offline version
 * @ingroup Extensions
 * @author Dennis Krohn @krohnden <dennis.krohn@th-luebeck.de>
 */
if ( !defined( 'MEDIAWIKI' ) ) die ( "This file cannot be run standalone.\n" );

use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\SlotRecord;

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
    private $imprintHref; # needed on every page - requesting external imprint service for every page would be bad.
    private $privacyHref;

    public static function structure2html(LoopStructure $loopStructure, RequestContext $context, $exportDirectory) {

        set_time_limit(1800);

        $loopStructureItems = $loopStructure->getStructureItems();

        if(is_array($loopStructureItems)) {

            global $wgOut, $wgDefaultUserOptions, $wgResourceLoaderDebug, $wgUploadDirectory, $wgArticlePath,
            $wgLoopImprintLink, $wgLoopPrivacyLink;

            $loopSettings = new LoopSettings();
            $loopSettings->loadSettings();

            $exportHtmlDirectory = $wgUploadDirectory.$exportDirectory;
            $loopHtml = LoopHtml::getInstance();
            $loopHtml->startDirectory = $exportHtmlDirectory.'/'.$loopStructure->getId().'/';
            $loopHtml->exportDirectory = $exportHtmlDirectory.'/'.$loopStructure->getId().'/files/';
            $loopHtml->imprintHref = LoopHtml::getImprintPrivacyLinks("imprint");
            $loopHtml->privacyHref = LoopHtml::getImprintPrivacyLinks("privacy");
            #dd(LoopHtml::getInstance());
            //$articlePath = preg_replace('/(\/)/', '\/', $wgArticlePath);
            //LoopHtml::getInstance()->articlePathRegEx = preg_replace('/(\$1)/', '', $articlePath);

			$user = $wgOut->getUser();
            # prepare global config
			$userOptionsLookup = MediaWikiServices::getInstance()->getUserOptionsLookup();
			$userOptionsManager = MediaWikiServices::getInstance()->getUserOptionsManager();
			$editModeBefore = $userOptionsLookup->getOption( $user, 'LoopEditMode', false, true );
			$renderModeBefore = $userOptionsLookup->getOption( $user, 'LoopRenderMode', $wgDefaultUserOptions['LoopRenderMode'], true );

            $debugModeBefore = $wgResourceLoaderDebug;
            $userOptionsManager->setOption( $user, 'LoopRenderMode', 'offline' );
            $userOptionsManager->setOption( $user, 'LoopEditMode', false );
            $wgResourceLoaderDebug = true;

            $exportSkin = clone $context->getSkin();

            # Create start file
            $mainPage = $context->getTitle()->newMainPage(); # Content of Mediawiki:Mainpage. Might not exist and cause error

            $wikiPage = MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle( $mainPage );
            $revision = $wikiPage->getRevisionRecord();
            if ( $revision != null ) {
                LoopHtml::writeArticleToFile( $mainPage, "files/", $exportSkin );
            } else {
                $mainPage = $loopStructure->mainPage;
                LoopHtml::writeArticleToFile( $mainPage, "files/", $exportSkin );
            }

            # Create special page files
            $specialPages = array ( 'LoopStructure', 'LoopFigures', 'LoopFormulas', 'LoopMedia', 'LoopListings', 'LoopLiterature', 'LoopTables', 'LoopTasks', 'LoopGlossary', 'LoopIndex', 'LoopTerminology' );
            foreach( $specialPages as $page ) {
                $tmpTitle = Title::newFromText( $page, NS_SPECIAL );
                LoopHtml::writeSpecialPageToFile( $tmpTitle, "", $exportSkin );
            }
            foreach($loopStructureItems as $loopStructureItem) {

                $articleId = $loopStructureItem->getArticle();
                if( isset( $articleId ) && is_numeric( $articleId )) {

                    $title = Title::newFromID( $articleId );
                    $html = LoopHtml::writeArticleToFile( $title, "", $exportSkin );

                }

            }
            $glossaryPages = LoopGlossary::getGlossaryPages();
            foreach( $glossaryPages as $title ) {
                LoopHtml::writeArticleToFile( $title, "", $exportSkin );
            }

            global $wgLoopExternalImprintPrivacy, $wgLoopExternalImprintUrl, $wgLoopExternalPrivacyUrl;
            $specialPageImprintContent = "";
            if ( $wgLoopExternalImprintPrivacy && !empty ( $wgLoopExternalImprintUrl ) ) {
                $specialPageImprintContent = SpecialLoopImprint::renderLoopImprintSpecialPage();
                if ( !empty ( $specialPageImprintContent ) ) {
                    $imprintTitle = Title::newFromText( "LoopImprint", NS_SPECIAL );
                    LoopHtml::writeSpecialPageToFile( $imprintTitle, "", $exportSkin, $specialPageImprintContent );
                }
            }
            if ( $specialPageImprintContent == "" && filter_var( htmlspecialchars_decode( $wgLoopImprintLink ), FILTER_VALIDATE_URL ) == false ) {
                $imprintTitle = Title::newFromText( $wgLoopImprintLink );
                if ( ! empty ( $imprintTitle->getText() ) ) {
                    $wikiPage = MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle( $imprintTitle );
                    $revision = $wikiPage->getRevisionRecord();
                    if ( $revision != null ) {
                        LoopHtml::writeArticleToFile( $imprintTitle, "", $exportSkin );
                    }
                }
            }

            $specialPagePrivacyContent = "";
            if ( $wgLoopExternalImprintPrivacy && !empty ( $wgLoopExternalPrivacyUrl ) ) {
                $specialPagePrivacyContent = SpecialLoopPrivacy::renderLoopPrivacySpecialPage();
                if ( !empty ( $specialPagePrivacyContent ) ) {
                    $privacyTitle = Title::newFromText( "LoopPrivacy", NS_SPECIAL );
                    LoopHtml::writeSpecialPageToFile( $privacyTitle, "", $exportSkin, $specialPagePrivacyContent );
                }
            }

            if ( $specialPagePrivacyContent == "" && filter_var( htmlspecialchars_decode( $wgLoopPrivacyLink ), FILTER_VALIDATE_URL ) == false ) {
                $privacyTitle = Title::newFromText( $wgLoopPrivacyLink );
                if ( ! empty ( $privacyTitle->getText() ) ) {
                    $wikiPage = MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle( $privacyTitle );
                    $revision = $wikiPage->getRevisionRecord();
                    if ( $revision != null ) {
                        LoopHtml::writeArticleToFile( $privacyTitle, "", $exportSkin );
                    }
                }
            }

            # add pdf to zip
            if ( LoopExportPdf::isAvailable( $loopSettings ) ) {
                # get pdf
                $pdfdir = LoopHtml::getInstance()->exportDirectory . "resources/pdf";
                if ( !file_exists( $pdfdir ) ) {
                    mkdir( $pdfdir, 0775, true );
                }

                $exportPdf = new LoopExportPdf( $loopStructure );
                if ( !$exportPdf->getExistingExportFile() ) {
                    $exportPdf->generateExportContent();
                    $content = $exportPdf->exportContent;
                } else {
                    $existingFile = $exportPdf->getExistingExportFile();
                    $content = file_get_contents( $existingFile );
                }

                $fileName = $exportPdf->getExportFilename();
                file_put_contents($pdfdir . "/" . $fileName, $content);
            }

            //dd($html);
            $tmpZipPath = $exportHtmlDirectory.'/tmpfile.zip';
            $tmpDirectoryToZip = $exportHtmlDirectory.'/'.$loopStructure->getId();

            $zip = new ZipArchive();
            $zip->open( $tmpZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator( $tmpDirectoryToZip ),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ( $files as $name => $file ) {
                if ( ! $file->isDir() ) {
                    $tmpFilePath = $file->getRealPath();
                    $tmpRelativePath = substr($tmpFilePath, strlen($tmpDirectoryToZip) + 1);
                    $zip->addFile( $tmpFilePath, $tmpRelativePath );
                    $filesToDelete[] = $tmpFilePath;
                }
            }
            $zip->close();
            $zip = file_get_contents( $tmpZipPath );

            foreach ($filesToDelete as $file) {
                unlink($file);
            }

            unlink( $tmpZipPath );


            # reset global config
            $wgOut->getUser()->setOption( 'LoopRenderMode', $renderModeBefore );
            $wgOut->getUser()->setOption( 'LoopEditMode', $editModeBefore );
            $wgResourceLoaderDebug = $debugModeBefore;

            return $zip;

        } else {
            return false;
        }

    }

    private static function getImprintPrivacyLinks( $mode ) {

		global $wgLoopExternalImprintPrivacy, $wgLoopExternalPrivacyUrl, $wgLoopExternalImprintUrl, $wgLoopImprintLink, $wgLoopPrivacyLink;

        if ( $mode == "imprint" ) {
            $externalUrl = $wgLoopExternalImprintUrl;
            $loopSettingsLink = $wgLoopImprintLink;
        } else {
            $externalUrl = $wgLoopExternalPrivacyUrl;
            $loopSettingsLink = $wgLoopPrivacyLink;
        }

        if ( $wgLoopExternalImprintPrivacy && !empty ( $externalUrl ) ) {
            if ( $mode == "imprint" ) {
                if ( !empty( SpecialLoopImprint::renderLoopImprintSpecialPage() ) ) {
                    $title = Title::newFromText( "LoopImprint", NS_SPECIAL );
                    return wfMessage( strtolower( $title->getText() ) )->text() . ".html";
                }
            } else {
                if ( !empty( SpecialLoopPrivacy::renderLoopPrivacySpecialPage() ) ) {
                    $title = Title::newFromText( "LoopPrivacy", NS_SPECIAL );
                    return wfMessage( strtolower( $title->getText() ) )->text() . ".html";
                }
            }
        }
        if ( filter_var( htmlspecialchars_decode( $loopSettingsLink ), FILTER_VALIDATE_URL ) ) {
            # Given link is a URL to a webpage
            return $loopSettingsLink;
        } else {
            # Link is a local page
            $title = Title::newFromText( $loopSettingsLink );
            return LoopHtml::getInstance()->resolveUrl( $title->mUrlform, '.html');
        }

    }

     /**
     * Write Special Page to file, with all given resources
     * @param Title $specialPage
     * @param string $prependHref for start file
     * @param $exportSkin
     *
     * @Return string html
     */
    private static function writeSpecialPageToFile( $specialPage, $prependHref, $exportSkin ) {

       # $loopStructure = new LoopStructure;
        #$loopStructure->loadStructureItems();
        #$text = $loopStructure->render();

       # global $wgExtensionMessagesFiles, $wgLanguageCode;

        $tmpTextform = wfMessage( strtolower( $specialPage->getText() ) )->text();
        #dd($tmpTextform, $specialPage);
        #$specialPage->getText() = $tmpTextform;
        $tmpFileName = $tmpTextform.'.html';
        switch ( $specialPage->getText() ) {
            case "LoopLiterature":
                $content = SpecialLoopLiterature::renderLoopLiteratureSpecialPage();
                break;
            case "LoopGlossary":
                $content = SpecialLoopGlossary::renderLoopGlossarySpecialPage();
                break;
            case "LoopFigures":
                $content = SpecialLoopFigures::renderLoopFigureSpecialPage();
                break;
            case "LoopFormulas":
                $content = SpecialLoopFormulas::renderLoopFormulaSpecialPage();
                break;
            case "LoopListings":
                $content = SpecialLoopListings::renderLoopListingSpecialPage();
                break;
            case "LoopMedia":
                $content = SpecialLoopMedia::renderLoopMediaSpecialPage();
                break;
            case "LoopTables":
                $content = SpecialLoopTables::renderLoopTableSpecialPage();
                break;
            case "LoopTasks":
                $content = SpecialLoopTasks::renderLoopTaskSpecialPage();
                break;
            case "LoopStructure":
                $content = SpecialLoopStructure::renderLoopStructureSpecialPage();
                break;
            case "LoopIndex":
                $content = SpecialLoopIndex::renderLoopIndexSpecialPage();
                break;
            case "LoopTerminology":
                $content = SpecialLoopTerminology::renderLoopTerminologySpecialPage();
                break;
            default:
                $content = '';
                break;
        }

        $htmlFileName = LoopHtml::getInstance()->exportDirectory.$tmpFileName;

        $exportSkin->getContext()->setTitle( $specialPage );
        $exportSkin->getContext()->getOutput()->setPageTitle($specialPage);
        $exportSkin->getContext()->getOutput()->mBodytext = $content;
        #dd($text, $specialPage, $exportSkin->getContext()->getOutput()->mBodytext, $exportSkin, $specialPage);
        # get html with skin object
        ob_start();
        $exportSkin->outputPage();
        $html = ob_get_contents();
        ob_end_clean();

        $html = LoopHtml::getInstance()->replaceResourceLoader($html, $prependHref);
        $html = LoopHtml::getInstance()->replaceManualLinks($html, $prependHref);
        $html = LoopHtml::getInstance()->replaceContentHrefs($html, $prependHref);
        file_put_contents($htmlFileName, $html);

        return $html;

    }

     /**
     * Write article from structure to file, with all given resources
     * @param Title $title
     * @param string $prependHref for start file
     * @param $exportSkin
     *
     * @Return string html
     */
    private static function writeArticleToFile( $title, $prependHref, $exportSkin ) {
        if ( getType( $title ) == "string" ) {
            $title = Title::newFromId($title);
        }
        $wikiPage = MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle( $title );
        $revision = $wikiPage->getRevisionRecord();
        $content = $revision->getContent( SlotRecord::MAIN );

		$parserFactory = MediaWikiServices::getInstance()->getParserFactory();
        $parser = $parserFactory->create();
		$tmpUser = new User();
        $text = $parser->parse( ContentHandler::getContentText( $content ), $title, new ParserOptions( $tmpUser ) )->mText;

        # regular articles are in ZIP/files/ folder, start article in ZIP/
        if ( $prependHref == "" ) {
            if ( $title->getNamespace() == NS_MAIN ) {
                $tmpFileName = LoopHtml::getInstance()->resolveUrl($title->mUrlform, '.html');
                $htmlFileName = LoopHtml::getInstance()->exportDirectory.$tmpFileName;
            } elseif( $title->getNamespace() == NS_GLOSSARY ) {
                $tmpFileName = LoopHtml::getInstance()->resolveUrl( wfMessage("loop-glossary-namespace")->text() . ":" . $title->getText(), '.html');
                $htmlFileName = LoopHtml::getInstance()->exportDirectory.$tmpFileName;
            }
        } else {
            $htmlFileName = LoopHtml::getInstance()->startDirectory.$title->mUrlform.'.html'; # TODO name start file
        }

        # prepare skin
        $exportSkin->getContext()->setTitle( $title );
        $exportSkin->getContext()->setWikiPage( $wikiPage );
        $exportSkin->getContext()->getOutput()->mBodytext = $text;

        # get html with skin object
        ob_start();
        $exportSkin->outputPage();
        $html = ob_get_contents();
        ob_end_clean();

        $html = LoopHtml::getInstance()->replaceResourceLoader($html, $prependHref);
        $html = LoopHtml::getInstance()->replaceManualLinks($html, $prependHref);
        $html = LoopHtml::getInstance()->replaceContentHrefs($html, $prependHref);
        file_put_contents($htmlFileName, $html);

        return $html;

    }

     /**
     * Replaces resources provided by resource loader
     * @param string $html
     * @param string $prependHref for start file
     *
     * @Return string html
     */
    private function replaceResourceLoader($html, $prependHref = "") {

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
                    $link->setAttribute('href', $prependHref."resources/styles/".md5($wgServer.$tmpHref).'.css');
                }
            }
        }

        # request contents for all matched <link> urls
        $requestUrls = $this->requestContent( $requestUrls );
        foreach ( $requestUrls as $url => $content ) {
            # Undoing MW's absolute paths in CSS files
            $content = preg_replace('/(\/mediawiki\/skins\/Loop\/resources\/)/', '../', $content); #css replacement links
            $content = preg_replace('/(\/skins\/Loop\/resources\/)/', '../', $content);
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
                    $script->setAttribute('src', $prependHref."resources/js/".md5($wgServer.$tmpScript).'.js');
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
        # Mediawiki:Common.css is already included
        $resources = array(
            "jquery.js" => array(
                "srcpath" => $wgServer . "/mediawiki/resources/lib/jquery/jquery.js",
                "targetpath" => "resources/js/",
                "link" => "script"
            ),
            "loopprint.js" => array(
                "srcpath" => $wgServer . "/mediawiki/extensions/Loop/resources/js/loop.printtag.js",
                "targetpath" => "resources/js/",
                "link" => "script-btm"
            ),
            "loopspoiler.js" => array(
                "srcpath" => $wgServer . "/mediawiki/extensions/Loop/resources/js/loop.spoiler.js",
                "targetpath" => "resources/js/",
                "link" => "script-btm"
            ),
            "mw.shared.css" => array(
                "srcpath" => $wgServer . "/mediawiki/resources/src/mediawiki.legacy/oldshared.css",
                "targetpath" => "resources/styles/",
                "link" => "style"
            ),
            "syntaxhighlight.generated.css" => array(
                "srcpath" => $wgServer."/mediawiki/extensions/SyntaxHighlight_GeSHi/modules/pygments.generated.css",
                "targetpath" => "resources/styles/",
                "link" => "style"
            ),
            "syntaxhighlight.wrapper.css" => array(
                "srcpath" => $wgServer."/mediawiki/extensions/SyntaxHighlight_GeSHi/modules/pygments.wrapper.css",
                "targetpath" => "resources/styles/",
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
            "tree.png" => array(
                "srcpath" => $skinPath."Loop/resources/img/tree.png",
                "targetpath" => "resources/img/",
            ),
            "skins.loop-resizer.js" => array(
                "srcpath" => $skinPath."Loop/resources/js/iframeresizer.js",
                "targetpath" => "resources/js/",
                "link" => "script-btm"
            ),
            "skins.loop-h5p-resizer.js" => array(
                "srcpath" => $skinPath."Loop/resources/js/h5presizer.js",
                "targetpath" => "resources/js/",
                "link" => "script-btm"
            ),
            "skins.featherlight.js" => array(
                "srcpath" => $skinPath."Loop/node_modules/featherlight/release/featherlight.min.js",
                "targetpath" => "resources/js/",
                "link" => "script-btm"
            ),
        );

        $skinStyle = str_replace( "style-", "loop-", $wgDefaultUserOptions["LoopSkinStyle"]);
        $skinFolder = "resources/styles/less/skins/common/$skinStyle/img/";
        $folderPath = "skins/Loop/$skinFolder";
        $addSkinFiles = true;

        if ( ! is_dir( $folderPath ) ) {
            $skinFolder = "resources/styles/less/skins/custom/$skinStyle/img/";
            $folderPath = "skins/Loop/$skinFolder";
			if ( ! is_dir( $folderPath ) ) {
				$addSkinFiles = false;
			}
        }
        if ($addSkinFiles) {

			$skinFiles = scandir("skins/Loop/$skinFolder");
			$skinFiles = array_slice($skinFiles, 2);

			foreach( $skinFiles as $file => $data ) {
				$resources[$data] = array(
					"srcpath" => "skins/Loop/$skinFolder$data",
					"targetpath" => $skinFolder
				);
			}
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
        $bodyElements = $doc->getElementsByTagName('body');

        # request contents for all entries in $resources array,
        # writes file in it's targetpath and links it on output page.
        foreach( $resources as $file => $data ) {
            $tmpContent[$file]["content"] =  file_get_contents( $data["srcpath"] );
            #if ( ! is_file($this->exportDirectory.$data["targetpath"].$file) ) {
                #dd( is_file($this->exportDirectory.$data["targetpath"].$file),$this->exportDirectory.$data["targetpath"].$file );
                #var_dump($data["srcpath"]);
                $this->writeFile( $data["targetpath"], $file, $tmpContent[$file]["content"] );
            #}

            if ( isset ( $data["link"] ) )  { # add file to output page if requested
                if ($data["link"] == "style") {
                    $tmpNode = $doc->createElement("link");
                    $tmpNode->setAttribute('href', $prependHref.$data["targetpath"] . $file );
                    $tmpNode->setAttribute('rel', "stylesheet" );
                    $headElements[0]->appendChild( $tmpNode );
                } else if ( $data["link"] == "script" ) {
                    $tmpNode = $doc->createElement("script");
                    $tmpNode->setAttribute('src', $prependHref.$data["targetpath"] . $file );
                    $headElements[0]->appendChild( $tmpNode );
                }

                if ( $data["link"] == "script-btm" ) {
                    $tmpNode = $doc->createElement("script");
                    $tmpNode->setAttribute('src', $prependHref.$data["targetpath"] . $file );
                    $bodyElements[0]->appendChild( $tmpNode );
                }
            }

        }

        $html = $doc->saveHtml();
        libxml_clear_errors();

        return $html;
    }

     /**
     * Replaces internal link href by class "local-link" and template links.
     * @param string $html
     * @param string $prependHref for start file
     *
     * @Return string html
     */
    private function replaceManualLinks( $html, $prependHref = "" ) {

        global $wgServer, $wgDefaultUserOptions, $wgLoopEditableSkinStyles, $wgLoopCustomLogo;
        $doc = new DOMDocument();
        $doc->loadHtml($html);
        $body = $doc->getElementsByTagName('body');

        if ( !empty( $prependHref ) ) { # ONLY for start file - add folder to path
            $internalLinks = $this->getElementsByClass( $body[0], "a", "local-link" );

            if ( $internalLinks ) {
                foreach ( $internalLinks as $element ) {

                    $tmpHref = $element->getAttribute( 'href' );
                    if ( isset ( $tmpHref ) && $tmpHref != '#' ) {
                        $element->setAttribute( 'href', $prependHref.$tmpHref );
                    }

                }
            }
        }

        # links to non-existing internal pages lose their href and look like normal text
        # TODO make hook

        $newLinks = $this->getElementsByClass( $body[0], "a", "new" );
        if ( $newLinks ) {
            foreach ( $newLinks as $element ) {
                $element->removeAttribute( 'href' );
                $element->removeAttribute( 'title' );
            }
        }

        # apply custom logo, if given
        $skinStyle = $wgDefaultUserOptions["LoopSkinStyle"];
        if ( !empty ( $wgLoopCustomLogo["useCustomLogo"] ) && in_array( $skinStyle, $wgLoopEditableSkinStyles ) ) {
            $loopLogo = $doc->getElementById('logo');
            $logoUrl = $wgLoopCustomLogo["customFilePath"];
            $logoFile = $this->requestContent( array($logoUrl) );

            preg_match('/(.*)(\.{1})(.*)/', $wgLoopCustomLogo["customFileName"], $fileData);
            $fileName = $this->resolveUrl($fileData[1], '.'.$fileData[3]);

            $this->writeFile( "resources/images/", $fileName, $logoFile[$logoUrl] );
            $loopLogo->setAttribute( 'style', 'background-image: url("'.$prependHref.'resources/images/'. $fileName.'");' );
        }

        # download linked ZIP file contents from loop_zip iframes
        $loopzips = $this->getElementsByClass( $body[0], "iframe", "loop-zip" );
        if ( $loopzips ) {
            foreach ( $loopzips as $element ) {
                $src = $element->getAttribute( 'src' );
                preg_match('/(mediawiki\/)(.*\/)(.*\.zip.extracted)(\/)(.*)/i', $src, $output_array); # gets the zipfile.zip.extracted folder name
                if ( isset ( $output_array[2] ) ) {
                    global $IP;

                    $extractedFolderPath = $output_array[2];
                    $extractedFolderName = $output_array[3];
                    $startFile = $output_array[5];
                    $sourceFolder = $IP  .'/'. $extractedFolderPath. $extractedFolderName;
                    $requestUrls = self::listFolderFiles( $sourceFolder );
                    $folderName = $this->resolveUrl($extractedFolderName, '');
                    $folderPath = "resources/img/$folderName/";
                    $requestUrlsContent = $this->requestContent($requestUrls);

                    foreach( $requestUrlsContent as $url => $content ) {
                        $fileName = array_search($url, $requestUrls);
                        $addendum = str_replace( $sourceFolder."/", "", $url );
                        $addendum = str_replace( $fileName, "", $addendum );
                        $this->writeFile( $folderPath . $addendum, $fileName, $content );
                    }
                    $element->removeAttribute( 'src' );
                    $newSrc = $prependHref . $folderPath . $startFile;
                    $element->setAttribute( 'src', $newSrc );
                }
            }
        }

        # manual replacement of imagemap links as the extension does not appear use the linkrenderer
        $imageMapLinks = $this->getElementsByClass( $body[0], "div", "noresize" );
        if ( $imageMapLinks ) {
            global $wgArticlePath;
            $articlePath = str_replace('$1', "", $wgArticlePath);
            foreach ( $imageMapLinks as $element ) {
                foreach ( $element->childNodes as $child ) {
                    if ( $child->nodeName == 'a' ) {
                        $href = $child->getAttribute( "href" );
                        if ( strpos( $href, $articlePath ) !== false ) {
                            $newHref = str_replace( $articlePath, "", $href );
                            $lsi = LoopStructureItem::newFromText($newHref);
                            if ( $lsi ) {
                                $filename = $this->resolveUrl($newHref, '.html');
                                $child->setAttribute( 'href', $prependHref . $filename );
                            }
                        }
                    }
                }
            }
        }
        # edit links from cite extension
        $citeLinks = $this->getElementsByClass( $body[0], "sup", "reference" );
        $citeLinks2 = $this->getElementsByClass( $body[0], "span", "mw-cite-backlink" );
        $citeLinks = array_merge( $citeLinks, $citeLinks2 );
        if ( $citeLinks ) {
            foreach ( $citeLinks as $element ) {
                foreach ( $element->childNodes as $child ) {
                    $newhref = strstr($child->getAttribute( 'href' ), "#");
                    $child->setAttribute( 'href', $newhref );
                }
            }
        }

        $pdfLink = $doc->getElementByID( "loop-pdf-download" );
        if ( !empty( $pdfLink ) ) {
            $loopStructure = new LoopStructure();
            $loopStructure->loadStructureItems();
            $exportPdf = new LoopExportPdf( $loopStructure );
            $fileLink = $prependHref . "resources/pdf/" . $exportPdf->getExportFilename();
            $pdfLink->setAttribute( "href", $fileLink );
        }

        $mp3Link = $doc->getElementByID( "loop-mp3-download" );
        if ( !empty( $mp3Link ) ) {
            $loopStructure = new LoopStructure();
            $loopStructure->loadStructureItems();
            $exportMp3 = new LoopExportMp3( $loopStructure );
            $fileLink = $prependHref . "resources/mp3/" . $exportMp3->getExportFilename();
            $mp3Link->setAttribute( "href", $fileLink );
        }

        $imprintLink = $doc->getElementByID( "imprintlink" );
        if ( filter_var( htmlspecialchars_decode( $this->imprintHref ), FILTER_VALIDATE_URL ) ) {
            $imprintLink->setAttribute( "href", $this->imprintHref );
        } else {
            $imprintLink->setAttribute( "href", $prependHref.$this->imprintHref );
        }

        $privacyLink = $doc->getElementByID( "privacylink" );
        if ( filter_var( htmlspecialchars_decode( $this->privacyHref ), FILTER_VALIDATE_URL ) ) {
            $privacyLink->setAttribute( "href", $this->privacyHref );
        } else {
            $privacyLink->setAttribute( "href", $prependHref.$this->privacyHref );
        }

        $html = $doc->saveHtml();
        return $html;
    }

    public static function listFolderFiles( $dir ){
        $dirContent = scandir($dir);

        unset($dirContent[array_search('.', $dirContent, true)]);
        unset($dirContent[array_search('..', $dirContent, true)]);

        if ( count($dirContent) < 1 )
            return;

        foreach ( $dirContent as $file ){
           $arr[$file] = $dir .'/'. $file;
            if ( is_dir( $dir .'/'. $file) ) {
                $arr = array_merge ( $arr, self::listFolderFiles( $dir .'/'. $file, "$dir/$file/" ) );
            }
        }
       return $arr;
    }

    /**
     * Requests urls and returns an array.
     * @Return Array ($url => $content)
     */
    function requestContent (Array $urls) : Array {
        $tmpContent = array();

        foreach($urls as $url) {

            if( ! in_array( $url, $this->requestedUrls ) ) {
                if ( !is_dir( $url ) ) {
                    $content = file_get_contents( $url );
                    $this->requestedUrls[ $url ] = $content;
                }
            }
            if ( isset ( $this->requestedUrls[ $url ] ) ) {
                $tmpContent[ $url ] = $this->requestedUrls[ $url ];
            }

        }

        return $tmpContent;

    }

     /**
     * Creates md5 filename for load.php files
     * @param string $url Node which to look inside
     * @param string $suffix file suffix
     *
     * @Return string
     */
    public function resolveUrl($url, $suffix) {
        return md5($url).$suffix;
    }

     /**
     * Looks for nodes with specific class name.
     * @param $parentNode Node which to look inside
     * @param string $tagName tag to look for
     * @param string $className class to look for
     *
     * @Return Array $nodes
     */
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

     /**
     * Writes file with given data
     * @param string $pathAddendum changes destination
     * @param string $fileName
     * @param string $content file content
     *
     * @Return true
     */
    function writeFile( $pathAddendum, $fileName, $content ) {

        if ( ! file_exists( $this->exportDirectory.$pathAddendum ) ) { # folder creation
            mkdir( $this->exportDirectory.$pathAddendum, 0775, true );
            #error_log($this->exportDirectory.$pathAddendum);
        }
        if ( ! file_exists( $this->exportDirectory.$pathAddendum.$fileName ) ) {
            file_put_contents($this->exportDirectory.$pathAddendum.$fileName, $content);
        }
        return true;
    }

     /**
     * Replaces href and src from files and other content
     * @param string $html
     * @param string $prependHref for start file
     *
     * @Return string $html
     */

    private function replaceContentHrefs( $html, $prependHref = "" ) {
        global $wgCanonicalServer;

        $doc = new DOMDocument();
        $doc->loadHtml($html);

        $body = $doc->getElementsByTagName('body');
        $downloadElements = array();

        $imageElements = $this->getElementsByClass( $body[0], "img", "responsive-image" );
        $videoElements = $this->getElementsByClass( $body[0], "video", "responsive-video" );
        $audioElements = $this->getElementsByClass( $body[0], "audio", "responsive-audio" );
        $scoreExtElements = $this->getElementsByClass( $body[0], "div", "mw-ext-score" ); # add images generated by score
        $mathExtElements = $this->getElementsByClass( $body[0], "img", "mwe-math-fallback-image-inline" ); # add images generated by math
        if ( !empty( $scoreExtElements ) ) {
            $scoreImgElements = array();
            foreach ( $scoreExtElements as $element ) {
                $scoreImgElements[] = $element->firstChild;
            }
            $downloadElements = array_merge($downloadElements, $scoreImgElements);
        }
        $downloadElements = array_merge($downloadElements, $imageElements);
        $downloadElements = array_merge($downloadElements, $videoElements);
        $downloadElements = array_merge($downloadElements, $audioElements);
        $downloadElements = array_merge($downloadElements, $mathExtElements);

        $imageUrls = array();
        if ( !empty( $downloadElements ) ) {
            foreach ( $downloadElements as $element ) {
                $posterData = array();
                $tmpSrc = $element->getAttribute( 'src' );
                if ( !empty ($tmpSrc) ) {
                    preg_match('/(.*\/)(.*)(\.{1})(.*)/', $tmpSrc, $tmpTitle);

                    if ( strpos( $element->getAttribute( 'class' ),  "mwe-math-fallback-image-inline" ) !== false ) { #handle external (restbase) images of math extension
                        $prependServer = "";
                        $fileData["suffix"][$prependServer . $tmpSrc] = 'svg';
                        $fileData["name"][$prependServer . $tmpSrc] = $this->resolveUrl( $element->getAttribute( 'alt' ), "");
                    } else {
                        $prependServer = $wgCanonicalServer;
                        if ( isset( $tmpTitle[2] ) && isset( $tmpTitle[4] ) ) {
                            if ( filter_var( $tmpSrc, FILTER_VALIDATE_URL ) ) { #video files have their urls with server
                                $prependServer= '';
                                $tmpPoster = $element->getAttribute( 'poster' );
                                if ( !empty ($tmpPoster) ) { # download poster images, too
                                    preg_match('/(.*\/)(.*)(\.{1})(.*)/', $tmpPoster, $tmpPosterTitle);
                                    if (isset( $tmpPosterTitle[2] ) && isset( $tmpPosterTitle[4] )) {
                                        $fileData["suffix"][$tmpPoster] = $tmpPosterTitle[4];
                                        $fileData["name"][$tmpPoster] = $tmpPosterTitle[2];
                                        $posterData["suffix"][$tmpPoster] = $tmpPosterTitle[4];
                                        $posterData["name"][$tmpPoster] = $tmpPosterTitle[2];
                                    }
                                }
                            }
                            $fileData["suffix"][$prependServer . $tmpSrc] = $tmpTitle[4];
                            $fileData["name"][$prependServer . $tmpSrc] = $tmpTitle[2];
                        }
                    }

                    if ( isset ($fileData["name"][$prependServer . $tmpSrc]) && isset ($fileData["suffix"][$prependServer . $tmpSrc]) ) {

                        $fileData["content"][] = $prependServer . $tmpSrc;
                        $newSrc = $prependHref."resources/images/" . $this->resolveUrl(  $fileData["name"][$prependServer . $tmpSrc], '.'. $fileData["suffix"][$prependServer . $tmpSrc] );
                        $element->setAttribute( 'src', $newSrc );
                        if ( !empty($posterData) ) {
                            $fileData["content"][] = $tmpPoster;
                            $newSrc = $prependHref."resources/images/" . $this->resolveUrl(  $posterData["name"][ $tmpPoster ], '.'. $posterData["suffix"][ $tmpPoster ] );
                            $element->setAttribute( 'poster', $newSrc );
                        }
                    }
                }
            }
            if ( $fileData["name"] && $fileData["suffix"] ) {
                $fileData["content"] = $this->requestContent($fileData["content"]);
                foreach ( $fileData["name"] as $image => $data ) {
                    $fileName = $this->resolveUrl($fileData["name"][$image], '.'.$fileData["suffix"][$image] );
                    $content = $fileData["content"][$image];
                    $this->writeFile( 'resources/images/', $fileName, $content );
                }
            }
        }

        $embedVideoElements = $this->getElementsByClass( $body[0], "div", "embedvideowrap" ); #fix errors in embedvideo urls (they start with //, not with https. this won't work with local files)
        foreach ( $embedVideoElements as $element ) {
            $iframe = $element->getElementsByTagName("iframe")->item(0);
            $iframeSrc = $iframe->getAttribute( "src" );
            $iframe->setAttribute( "src", "https:" . $iframeSrc );
        }

        $html = $doc->saveHtml();
        return $html;

    }
}
