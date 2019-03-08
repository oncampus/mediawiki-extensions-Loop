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

		$out = $this->getOutput();

		$out->setPageTitle( $this->msg( 'loopexport-specialpage-title' ) );

		$out->addHtml ('<h1>');
		$out->addWikiMsg( 'loopexport-specialpage-title' );
		$out->addHtml ('</h1>');

		$out->addHtml ($sub);


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
					$export = new LoopExportMp3($structure);
				}
				break;
			case 'html':
				if ($user->isAllowed( 'loop-export-html' )) {
					$export = new LoopExportHtml($structure, $this->getContext());
				}
				break;
			case 'epub':
				if ($user->isAllowed( 'loop-export-epub' )) {
					$export = new LoopExportEpub($structure);
				}
				break;
		}
		
		if ( $export != false ) {

			$export->generateExportContent();
			if ( $export->exportDirectory != "/export/html" ) { # don't cache html exports
				$export->saveExportFile();
			}

			$this->getOutput()->disable();
			wfResetOutputBuffers();
			$export->sendExportHeader();
			echo $export->getExportContent();
		} else {

			$out->addHtml('<ul>');

			if ($user->isAllowed( 'loop-export-xml' )) {
				$xmlExportLink = $linkRenderer->makeLink( new TitleValue( NS_SPECIAL, 'LoopExport/xml' ), new HtmlArmor(wfMessage ( 'export-linktext-xml' )->inContentLanguage ()->text () ));
				$out->addHtml ('<li>'.$xmlExportLink.'</li>');
			}

			if ($user->isAllowed( 'loop-export-pdf' )) {
				$pdfExportLink = $linkRenderer->makeLink( new TitleValue( NS_SPECIAL, 'LoopExport/pdf' ), new HtmlArmor(wfMessage ( 'export-linktext-pdf' )->inContentLanguage ()->text () ));
				$out->addHtml ('<li>'.$pdfExportLink.'</li>');
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
