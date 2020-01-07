<?php 
/**
 * @description Exports LOOP to PDF and test PDF side 
 * @ingroup Extensions
 * @author Marc Vorreiter @vorreiter <marc.vorreiter@th-luebeck.de>
 * @author Dennis Krohn @krohnden <dennis.krohn@th-luebeck.de>
 */
if ( !defined( 'MEDIAWIKI' ) ) {
    die( "This file cannot be run standalone.\n" );
}

use MediaWiki\MediaWikiServices;

class LoopPdf {
	
	/*
	* Turns given LoopStructure into PDF.
	* @params LoopStructure $structure
	* @params Array $modifiers
	* 			-> "pagetest" = true: return will be modified for single page test support.
	*/
	public static function structure2pdf(LoopStructure $structure, $modifiers = null) {
		global $IP;

		$wiki_xml = LoopXml::structure2xml($structure);
		$errors = '';
		
		$xmlfo = self::transformToXmlfo( $wiki_xml );
		$pdf = self::makePdfRequest( $xmlfo["xmlfo"] );
		
		if ( !empty($errors) ) {
			var_dump($errors);
		}
		if ( strpos( $pdf, "%PDF") !== 0 ) {
			#es werden keine leeren/fehlerhaften PDFs mehr heruntergeladen, solange das hier aktiv ist.
			var_dump( "Error! Anstatt eine leere PDF auszugeben, gibt es jetzt den content hier. #debug", $pdf, $xmlfo, $wiki_xml );exit; #dd ist zu JS-ressourcenintensiv
		}
		#var_dump( "Debug! PDF funktioniert eigentlich. ", $xmlfo, $wiki_xml );exit;
		return $pdf;
		
	
	}	
	
	public static function transformToXmlfo( $wiki_xml ) {
		global $IP;
		
		$errors = '';
		$xmlfo = '';
		try {
			$xml = new DOMDocument();
			$xml->loadXML($wiki_xml);
		} catch (Exception $e) {
			$errors .= $e . "\n";
		}
	
		try {
			$xsl = new DOMDocument;
			$xsl->load( $IP.'/extensions/Loop/xsl/pdf.xsl' );
		} catch ( Exception $e ) {
			$errors .= $e . "\n";
		}
	
		try {
			$proc = new XSLTProcessor;
			$proc->registerPHPFunctions();
			$proc->importStyleSheet( $xsl );
			$xmlfo = $proc->transformToXML( $xml );
		} catch (Exception $e) {
			$errors .= $e . "\n";
		}
		$return = array( "xmlfo" => $xmlfo, "errors" => $errors );

		return $return;
	}

	public static function makePdfRequest( $xmlfo ) {

		global $wgXmlfo2PdfServiceUrl, $wgXmlfo2PdfServiceToken;

		$url = $wgXmlfo2PdfServiceUrl. '?token='.$wgXmlfo2PdfServiceToken;
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
		curl_setopt($ch, CURLOPT_POSTFIELDS, "$xmlfo");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$pdf = curl_exec($ch);
		curl_close($ch);

		return $pdf;

	}
	
}

class SpecialLoopExportPdfTest extends SpecialPage {

	public function __construct() {
		parent::__construct( 'LoopExportPdfTest' );
	}
	
	public function execute( $sub ) {

		$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
		$linkRenderer->setForceArticlePath(true);
		
		$out = $this->getOutput();
		$request = $this->getRequest();
		$user = $this->getUser();
		Loop::handleLoopRequest( $out, $request, $user ); #handle editmode

		$out->setPageTitle( $this->msg( 'loopexportpdftest' ) );
		$out->addHtml ('<h1>');
		$out->addWikiMsg( 'loopexportpdftest' );
		$out->addHtml ('</h1>');
		$loopStructure = new LoopStructure();

		$query = $request->getQueryValues();
		# you use ?articleId=1 to debug to test only one page
		if ( array_key_exists( "articleId", $query ) ) {
			$title = Title::newFromId( $query["articleId"] );
			if ( isset( $title ) ) {
				$item = new LoopStructureItem();
				$item->id = 1;
				$item->article = $title->getArticleID();
				$item->tocText = $title->mTextform;
				$loopStructure->structureItems = array( $item );
			} else {
				$loopStructure->loadStructureItems();
			}
		} else {
			$loopStructure->loadStructureItems();
		}
		$error = false;

		if ( $user->isAllowed('loop-pdf-test') ) {

			$html = "<br>";
			foreach ( $loopStructure->structureItems as $item ) {
				#reset item
				$item->previousArticle = 0;
				$item->structure = 1000;
				$item->nextArticle = 0;
				$item->parentArticle = 0;
				$item->sequence = 0;
				$item->tocLevel = 0;
				# create fake structure to make pdf with (id 1 is not used")
				$fakeTmpStructure = new LoopStructure(1000);
				$fakeTmpStructure->structureItems = array( $item );
				$fakeTmpStructure->mainPage = $item->article;
				
				$xml = LoopXml::structure2xml( $fakeTmpStructure );
				$xmlfo = LoopPdf::transformToXmlfo( $xml );
				$modifiedXmlfo = self::modifyXmlfoForTest( $xmlfo["xmlfo"], $fakeTmpStructure );
				$tmpPdf = LoopPdf::makePdfRequest( $modifiedXmlfo );

				if ( strpos( $tmpPdf, "%PDF") === 0 ) {
					# pdf :)
					$html .= $item->tocNumber . " " . $item->tocText . " OK!<br>";
				} else {
					# not a pdf!
					$html .= $linkRenderer->makelink(
						Title::newFromID( $item->article ), 
						new HtmlArmor( "<b>".$item->tocNumber . " " . $item->tocText . " NOT OK!</b> (ID $item->article)" ),
						array('target' => '_blank' )
						);

					$html .= "<br><br>";
					$html .= "<nowiki>". $tmpPdf . "</nowiki><br>";
					$html .= $xmlfo["errors"] . "<br>";
					$error = $item->tocText;
					break;
				}
				
			}
			if ( !$error ) {
				$out->addHtml( '<div class="alert alert-success" role="alert">' . $this->msg( 'loopexport-pdf-test-success' ) . '</div>' );
			} else {
				$out->addHtml( '<div class="alert alert-danger" role="alert">' . $this->msg( 'loopexport-pdf-test-failure', $error ) . '</div>' );
			}
			
			$trackingCategory = Category::newFromName( $this->msg("loop-tracking-category-error")->text() );
			$trackingCategoryItems = $trackingCategory->getMembers();
	
			if ( !empty( $trackingCategoryItems ) ) {
				$link = $linkRenderer->makelink(
					Title::newFromText( $this->msg( "loop-tracking-category-error" )->text(), NS_CATEGORY ), 
					new HtmlArmor( $this->msg( "loop-tracking-category-error" )->text() ),
					array('target' => '_blank' )
					);
				$out->addHtml( '<div class="alert alert-warning" role="alert">' . $this->msg( 'loopexport-pdf-test-notice', $link )->text() . '</div>'  );
			}

		} else {
			$html = '<div class="alert alert-warning" role="alert">' . $this->msg( 'specialpage-no-permission' ) . '</div>';
		}

		$out->addHtml($html);
	}

	static function modifyXmlfoForTest( $xmlfo, $structure ) {

		if ( !empty( $xmlfo ) ) { 
			#modify content so pdf still works in single-page test mode
			$dom = new DomDocument();
			$dom->loadXML( $xmlfo );
			$linkTags = $dom->getElementsByTagNameNS ("http://www.w3.org/1999/XSL/Format", "basic-link"); 
			$refTags = $dom->getElementsByTagNameNS ("http://www.w3.org/1999/XSL/Format", "page-number-citation"); 
			$lastRefTags = $dom->getElementsByTagNameNS ("http://www.w3.org/1999/XSL/Format", "page-number-citation-last");
			foreach ( $linkTags as $tag ) {
				$tag->setAttribute( "internal-destination", "article".$structure->mainPage );
			}
			foreach ( $refTags as $tag ) {
				$tag->setAttribute( "ref-id", "article".$structure->mainPage );
			}
			foreach ( $lastRefTags as $tag ) {
				$tag->setAttribute( "ref-id", "article".$structure->mainPage );
			}
			$modifiedXmlfo = $dom->saveXML();
			
			return $modifiedXmlfo;
		}
		return false;
	}

	protected function getGroupName() {
		return 'loop';
	}
}