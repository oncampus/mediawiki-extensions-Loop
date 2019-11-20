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
		global $IP, $wgXmlfo2PdfServiceUrl, $wgXmlfo2PdfServiceToken;

		$wiki_xml = LoopXml::structure2xml($structure);
		$errors = '';
		#var_dump($wiki_xml);exit;
		try {
			$xml = new DOMDocument();
			$xml->loadXML($wiki_xml);
		} catch (Exception $e) {
			$errors .= $e . "\n";
		}
	
		try {
			$xsl = new DOMDocument;
			$xsl->load($IP.'/extensions/Loop/xsl/pdf.xsl');
		} catch (Exception $e) {
			$errors .= $e . "\n";
		}
	
		try {
			$proc = new XSLTProcessor;
			$proc->registerPHPFunctions();
			$proc->importStyleSheet($xsl);
			$xmlfo = $proc->transformToXML($xml);

			if ( is_array( $modifiers ) && !empty( $xmlfo ) && $modifiers["pagetest"] == true ) { 
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
				$xmlfo = $dom->saveXML();
				
			}

		} catch (Exception $e) {
			$errors .= $e . "<br>";
		}

		#dd($xmlfo);
		$url = $wgXmlfo2PdfServiceUrl. '?token='.$wgXmlfo2PdfServiceToken;
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
		curl_setopt($ch, CURLOPT_POSTFIELDS, "$xmlfo");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$pdf = curl_exec($ch);
		curl_close($ch);
		
		if ( is_array( $modifiers ) && $modifiers["pagetest"] == true ) { 
			return array( "pdf" => $pdf, "errors" => $errors, "xmlfo" => $xmlfo );
		} else {
			if ( !empty($errors) ) {
				var_dump($errors);
			}
			if ( strpos( $pdf, "%PDF") !== 0 ) {
				#es werden keine leeren/fehlerhaften PDFs mehr heruntergeladen, solange das hier aktiv ist.
				var_dump( "Error!", $pdf, $xmlfo, $wiki_xml );exit;
			}
			#var_dump( "Debug! PDF funktioniert eigentlich. ", $xmlfo, $wiki_xml );exit;
			return $pdf;
		}
	
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
		$loopStructure->loadStructureItems();
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
				
				#$tmpXml = LoopXml::structure2xml( $fakeTmpStructure );
				$tmpPdf = LoopPdf::structure2pdf( $fakeTmpStructure, array( "pagetest" => true ) );

				if ( strpos( $tmpPdf["pdf"], "%PDF") === 0 ) {
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
					$html .= "<nowiki>". $tmpPdf["pdf"] . "</nowiki><br>";
					$html .= $tmpPdf["errors"] . "<br>";
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
				$out->addHtml( '<div class="alert alert-warning" role="alert">' . $this->msg( 'loopexport-pdf-test-notice', $this->msg("loop-tracking-category-error")->text() ) . $this->msg("loop-tracking-category-error")->text(). '</div>' );
			}

		} else {
			$html = '<div class="alert alert-warning" role="alert">' . $this->msg( 'specialpage-no-permission' ) . '</div>';
		}

		$out->addHtml($html);

	}
	protected function getGroupName() {
		return 'loop';
	}
}