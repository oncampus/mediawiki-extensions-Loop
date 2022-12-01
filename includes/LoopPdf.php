<?php
/**
 * @description Exports LOOP to PDF and test PDF side
 * @ingroup Extensions
 * @author Marc Vorreiter @vorreiter <marc.vorreiter@th-luebeck.de>
 * @author Dennis Krohn @krohnden <dennis.krohn@th-luebeck.de>
 */
if ( !defined( 'MEDIAWIKI' ) ) die ( "This file cannot be run standalone.\n" );

use MediaWiki\MediaWikiServices;

class LoopPdf {

	/*
	* Turns given LoopStructure into PDF.
	* @params LoopStructure $structure
	* @params Array $modifiers
	* 			-> "pagetest" = true: return will be modified for single page test support.
	*/
	public static function structure2pdf(LoopStructure $structure, $modifiers = []) {
		global $IP, $wgLoopExportDebug;

		if ( $wgLoopExportDebug ) {
			global $wgServerName;
			error_log("$wgServerName PDF Debug: structure2pdf started");
		}
		set_time_limit(1201);

		$wiki_xml = LoopXml::structure2xml($structure);
		$errors = '';

		$xmlfo = self::transformToXmlfo( $wiki_xml );
		$pdf = self::makePdfRequest( $xmlfo["xmlfo"] );

		if ( !empty($xmlfo["errors"]) ) {
			var_dump($xmlfo["errors"]);
		}

		if ( strpos( $pdf, "%PDF") !== 0 ) { # error!
			if ( $wgLoopExportDebug ) {
				global $wgServerName;
				error_log("$wgServerName PDF Debug: failed");
			}
			return [$pdf, $xmlfo, $wiki_xml];
		}
		if ( $wgLoopExportDebug ) {
			global $wgServerName;
			error_log("$wgServerName PDF Debug: structure2pdf success");
		}
		#var_dump( "Debug! PDF funktioniert eigentlich. ", $xmlfo, $wiki_xml );exit;
		return $pdf;


	}

	public static function transformToXmlfo( $wiki_xml ) {
		global $IP, $wgLoopExportDebug;

		if ( $wgLoopExportDebug ) {
			global $wgServerName;
			error_log("$wgServerName PDF Debug: transformation to xmlfo started");
		}
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
		if ( $wgLoopExportDebug ) {
			global $wgServerName;
			error_log("$wgServerName PDF Debug: transformed to xmlfo");
		}
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
				$item->tocText = $title->getText();
				$loopStructure->structureItems = array( $item );
			} else {
				$loopStructure->loadStructureItems();
			}
		} else {
			$loopStructure->loadStructureItems();
		}
		$error = array();
		$permissionManager = MediaWikiServices::getInstance()->getPermissionManager();
		if ( $permissionManager->userHasRight( $user, 'loop-pdf-test') ) {

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

				echo "<script>console.log($item->article + ' started');</script>";

				$xml = LoopXml::structure2xml( $fakeTmpStructure );
				$xmlfo = LoopPdf::transformToXmlfo( $xml );
				$modifiedXmlfo = self::modifyXmlfoForTest( $xmlfo["xmlfo"], $fakeTmpStructure );
				$tmpPdf = LoopPdf::makePdfRequest( $modifiedXmlfo );

				if ( strpos( $tmpPdf, "%PDF") === 0 ) {
					# pdf :)
					$html .= $item->tocNumber . " " . $item->tocText . ": <span class='text-success'>OK!</span><br>";
					echo "<script>console.log('OK');</script>";
				} else {
					echo "<script>console.log($item->article + ' FAILED ( $item->tocText )');</script>";
					# not a pdf!
					$html .= $linkRenderer->makelink(
						Title::newFromID( $item->article ),
						new HtmlArmor( "<b>".$item->tocNumber . " " . $item->tocText . ": Error!</b> (ID $item->article)" ),
						array('target' => '_blank' )
						);
					$errorid = uniqid();
					$html .= '<a class="" data-toggle="collapse" href="#err'.$errorid.'" role="button" aria-expanded="false" aria-controls="err'.$errorid.'"> ▼</a>';

					#$html .= "<br><br>";

					$html .= "<pre class='alert alert-danger collapse' id='err$errorid'>". implode("\n", array_slice(explode("\n", $tmpPdf), 1)) . "</pre><br>";
					$error[] = "[[". $item->tocText . "]]";
				}

			}
			if ( empty( $error ) ) {
				$out->addHtml( '<div class="alert alert-success" role="alert">' . $this->msg( 'loopexport-pdf-test-success' ) . '</div>' );
			} else {
				$out->addHtml( '<div class="alert alert-danger" role="alert">' . $this->msg( 'loopexport-pdf-test-failure', implode ( ", ",$error ) ) . '</div>' );
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
