<?php

use MediaWiki\MediaWikiServices;

class SpecialLoopExport extends SpecialPage {
	public function __construct() {
		parent::__construct( 'LoopExport' );
	}

	public function execute( $sub ) {

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
					$export = new LoopExportXml($structure);
				}
				break;
			case 'pdf':
				if ($user->isAllowed( 'loop-export-pdf' )) {
					$export = new LoopExportPdf($structure);
				}
				break;
			case 'mp3':
				if ($user->isAllowed( 'loop-export-mp3' )) {
					$export = new LoopExportMp3($structure, $request);
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
		}
		
		if ( $export != false ) {

			$query = $request->getQueryValues();
			if ( isset( $query['articleId'] ) ) {
				$export->generateExportContent();
				$this->getOutput()->disable();
				wfResetOutputBuffers();
				$export->sendExportHeader();
				#echo $export->getExportContent();
			} else {
				#dd($export->getExistingExportFile() );
				if ( $export->getExistingExportFile() ) {
					$export->getExistingExportFile();
					#$export->exportContent;
				} else {
					$export->generateExportContent();
					
					if ( $export->exportDirectory != "/export/html" ) { # don't cache html exports
						$export->saveExportFile();
					}
				}
				
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

			if ($user->isAllowed( 'loop-export-mp3' )) { #todo service token mp3
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
