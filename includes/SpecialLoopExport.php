<?php

use MediaWiki\MediaWikiServices;

class SpecialLoopExport extends SpecialPage {
	public function __construct() {
		parent::__construct( 'LoopExport' );
	}

	public function execute( $sub ) {

		global $wgText2SpeechServiceUrl, $wgText2Speech, $wgXmlfo2PdfServiceUrl, $wgXmlfo2PdfServiceToken;

		$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();

		$user = $this->getUser();
		$config = $this->getConfig();
		$request = $this->getRequest();
		$context = $this->getContext();
		$out = $this->getOutput();

		$out->setPageTitle( $this->msg( 'loopexport-specialpage-title' ) );

		$out->addHtml ('<h1>');
		$out->addWikiMsg( 'loopexport-specialpage-title' );
		$out->addHtml ('</h1>');

		$out->addHtml ($sub);
		//dd($sub, $request);

		$structure = new LoopStructure();

		$sub = mb_strtolower($sub);

		$export = false;
		switch ($sub) {
			case 'xml':
				if ($user->isAllowed( 'loop-export-xml' )) {
					$export = new LoopExportXml($structure, $request);
				}
				break;
			case 'pdf':
				if ($user->isAllowed( 'loop-export-pdf' )) {
					$export = new LoopExportPdf($structure);
				}
				break;
			case 'mp3':
				if ($user->isAllowed( 'loop-export-mp3' )) {
					$export = new LoopExportMp3($structure);
				}
				break;
			case 'html':
				if ($user->isAllowed( 'loop-export-html' )) {
					$export = new LoopExportHtml($structure, $context);
				}
			break;
			case 'epub':
				if ($user->isAllowed( 'loop-export-epub' )) {
					$export = new LoopExportEpub($structure);
				}
			break;
			case 'pageaudio':
				if ($user->isAllowed( 'loop-pageaudio' )) {
					$export = new LoopExportPageMp3($structure, $request);
				}
				break;
		}
		
		if ( $export != false ) {

			if ( $export->getExistingExportFile() && $export->fileExtension != "mp3" ) {
					$export->getExistingExportFile();
			} else {
				$export->generateExportContent();
					
				if ( $export->exportDirectory != "/export/html" && $export->fileExtension != "mp3" ) { # don't cache html exports
					$export->saveExportFile();
				}
			}
			if ($export->getExportContent() != null ) {
				$this->getOutput()->disable();
				wfResetOutputBuffers();
				$export->sendExportHeader();
				echo $export->getExportContent();
			}
			
		} else {

			$out->addHtml('<ul>');

			if ($user->isAllowed( 'loop-export-xml' )) {
				$xmlExportLink = $linkRenderer->makeLink( new TitleValue( NS_SPECIAL, 'LoopExport/xml' ), new HtmlArmor(wfMessage ( 'export-linktext-xml' )->inContentLanguage ()->text () ));
				$out->addHtml ('<li>'.$xmlExportLink.'</li>');
			}

			if ($user->isAllowed( 'loop-export-pdf' ) && ! empty( $wgXmlfo2PdfServiceUrl ) && ! empty( $wgXmlfo2PdfServiceToken ) ) {
				$pdfExportLink = $linkRenderer->makeLink( new TitleValue( NS_SPECIAL, 'LoopExport/pdf' ), new HtmlArmor(wfMessage ( 'export-linktext-pdf' )->inContentLanguage ()->text () ));
				$out->addHtml ('<li>'.$pdfExportLink.'</li>');
			}

			if ($user->isAllowed( 'loop-export-mp3' ) && $wgText2Speech && ! empty( $wgText2SpeechServiceUrl ) ) { 
				$mp3ExportLink = $linkRenderer->makeLink( new TitleValue( NS_SPECIAL, 'LoopExport/mp3' ), new HtmlArmor(wfMessage ( 'export-linktext-mp3' )->inContentLanguage ()->text () ));
				$out->addHtml ('<li>'.$mp3ExportLink.'</li>');
			}			
			
			if ($user->isAllowed( 'loop-export-html' )) {
				$htmlExportLink = $linkRenderer->makeLink( new TitleValue( NS_SPECIAL, 'LoopExport/html' ), new HtmlArmor(wfMessage ( 'export-linktext-html' )->inContentLanguage ()->text () ));
				$out->addHtml ('<li>'.$htmlExportLink.'</li>');
			}

			$out->addHtml('</ul>');

		}
	}

	protected function getGroupName() {
		return 'loop';
	}
}
