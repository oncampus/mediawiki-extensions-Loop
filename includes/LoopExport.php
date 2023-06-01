<?php
/**
 * @description Exports LOOP to various formats.
 * @ingroup Extensions
 * @author Marc Vorreiter @vorreiter <marc.vorreiter@th-luebeck.de>
 * @author Dennis Krohn @krohnden <dennis.krohn@th-luebeck.de>
 */
if ( !defined( 'MEDIAWIKI' ) ) die ( "This file cannot be run standalone.\n" );

use MediaWiki\MediaWikiServices;

abstract class LoopExport {

	public $structure;
	public $exportContent;
	public $exportDirectory;
	public $fileExtension;

	abstract protected function generateExportContent();


	public function getExistingExportFile() {

		global $wgRequest, $wgLoopExportDebug;
		$debug = $wgRequest->getText("debug");
		if ( $debug == "true" || $wgLoopExportDebug ) {
			return false;
		}

		global $wgUploadDirectory;

		$export_dir = $wgUploadDirectory.$this->exportDirectory.'/'.$this->structure->getId();
		if (!is_dir($export_dir)) {
			@mkdir($export_dir, 0777, true);
		}

		$export_file = $export_dir.'/'.$this->structure->lastChanged().'.'.$this->fileExtension;
		if (is_file($export_file)) {

			$fh = fopen($export_file, 'r');
			if ( filesize($export_file) > 0 )	{
				$content = fread($fh, filesize($export_file));
				$this->exportContent = $content;
				fclose($fh);
				return $export_file;
			} else {
				fclose($fh);
				return false;
			}

		} else {
			return false;
		}
	}

	public function saveExportFile() {
		global $wgUploadDirectory;

		$export_dir = $wgUploadDirectory.$this->exportDirectory.'/'.$this->structure->getId();
		if (  isset($this->lsi) && $this->lsi != null ) {
			$export_dir .= '/'. $this->lsi->article;

		}
		//var_dump( $export_dir );
		if (!is_dir($export_dir)) {
			@mkdir($export_dir, 0777, true);
		}
		if ( isset($this->lsi) && $this->lsi != null ) {
			if (!is_dir($export_dir)) {
				@mkdir($export_dir, 0777, true);
			}
			$export_file = $export_dir.'/'.$this->lsi->article.'_'.$this->lsi->lastChanged().'.'.$this->fileExtension; #todo on article delete from structure
		} else {
			$export_file = $export_dir.'/'.$this->structure->lastChanged().'.'.$this->fileExtension;
		}

		$fh = fopen($export_file, 'w');
		fwrite($fh, $this->exportContent);
		fclose($fh);

		// delete old export file
		if ( is_dir($export_dir) ) {
			$handle = opendir($export_dir);
			while (false !== ($entry = readdir($handle))) {
				if ($entry != "." && $entry != "..") {
					if (is_file($export_dir.'/'.$entry)) {
						if ($entry != basename($export_file)) {
							unlink($export_dir.'/'.$entry);
						}
					}

				}
			}
		}
	}

	public function getExportContent() {
		return $this->exportContent;
	}

	public function setExportContent($content) {
		$this->exportContent = $content;
	}

	public function getExportFilename() {

		global $wgCanonicalServer;

		$urlparts = mb_split("\.", $wgCanonicalServer);
		if (isset($urlparts[0])) {
			$hashtag = preg_replace("/(http[s]{0,1}:\/\/)/i", "", $urlparts[0]);
		} else {
			$hashtag = preg_replace("/(http[s]{0,1}:\/\/)/i", "", $wgCanonicalServer);;
		}

		$zipFileAddendum = "";
		if ( $this->exportDirectory == "/export/mp3" ) {
			$zipFileAddendum = "_" . wfMessage("loopexport-audio-filename")->text();
		} elseif ( $this->exportDirectory == "/export/html" ) {
			$zipFileAddendum = "_" . wfMessage("loopexport-offline-filename")->text();
		}

		return strtoupper($hashtag) . $zipFileAddendum .'.'. $this->fileExtension;
	}

}



class LoopExportXml extends LoopExport {

	public function __construct($structure, $request = null) {
		$this->structure = $structure;
		$this->exportDirectory = '/export/xml';
		$this->fileExtension = 'xml';
		$this->request = $request;
	}

	public static function isAvailable( $loopSettings = null ) {
		global $wgXmlExport;
		if ( $loopSettings === null ) {
			$loopSettings = new LoopSettings();
			$loopSettings->loadSettings();
		}
		if ( $wgXmlExport && $loopSettings->exportXml ) {
			return true;
		}
		return false;
	}

	/**
	 * Add indexable item to the database
	 * @param Array $modifiers:
	 * 		"mp3" => true; modifies XML Output for MP3 export, adds additional breaks for loop_objects
	 */
	public function generateExportContent( Array $modifiers = [] ) {
		$query = array();
		if ( isset( $this->request ) ) {
			$query = $this->request->getQueryValues();
		}
		if ( isset( $query['articleId'] ) ) {
			$this->exportContent = LoopXml::articleFromId2xml( $query['articleId'] );
			var_dump($this->exportContent); exit; //debug output of page
		} else {
			$this->exportContent = LoopXml::structure2xml($this->structure, $modifiers);
		}
	}

	public function generatePageExportContent( $article_id ) {
		$this->exportContent = LoopXml::articleFromId2xml( $article_id );
	}

	public function sendExportHeader() {

		$filename = $this->getExportFilename();

		header("Last-Modified: " . date("D, d M Y H:i:s T", strtotime($this->structure->lastChanged())));
		header("Content-Type: application/xml; charset=utf-8");
		header('Content-Disposition: attachment; filename="' . $filename . '";' );
		header("Cache-Control: max-age=0, no-cache, no-store, must-revalidate");
		header("Expires: " . date("D, d M Y H:i:s T"));

	}

}


class LoopExportPdf extends LoopExport {

	public function __construct($structure) {
		$this->structure = $structure;
		$this->exportDirectory = '/export/pdf';
		$this->fileExtension = 'pdf';
	}

	public static function isAvailable( $loopSettings = null ) {
		global $wgXmlfo2PdfServiceUrl, $wgXmlfo2PdfServiceToken, $wgPdfExport;
		if ( $loopSettings === null ) {
			$loopSettings = new LoopSettings();
			$loopSettings->loadSettings();
		}
		if ( $wgPdfExport && ! empty( $wgXmlfo2PdfServiceUrl ) && ! empty( $wgXmlfo2PdfServiceToken ) && $loopSettings->exportPdf ) {
			return true;
		}
		return false;
	}

	public function generateExportContent() {
		$this->exportContent = LoopPdf::structure2pdf($this->structure);
	}

	public function sendExportHeader() {

		$filename = $this->getExportFilename();

		header("Last-Modified: " . date("D, d M Y H:i:s T", strtotime($this->structure->lastChanged())));
		header("Content-Type: application/pdf");
		header('Content-Disposition: attachment; filename="' . $filename . '";' );
		header("Content-Length: ". strlen($this->exportContent));
		header("Cache-Control: max-age=0, no-cache, no-store, must-revalidate");
		header("Expires: " . date("D, d M Y H:i:s T"));

	}

}


class LoopExportMp3 extends LoopExport {

	public function __construct($structure, $request = null) {
		$this->structure = $structure;
		$this->request = $request;
		$this->exportDirectory = '/export/mp3';
		$this->fileExtension = 'zip';
		$this->lsi = null;

	}

	public static function isAvailable( $loopSettings = null ) {
		global $wgText2SpeechServiceUrl, $wgAudioExport;
		if ( $loopSettings === null ) {
			$loopSettings = new LoopSettings();
			$loopSettings->loadSettings();
		}
		if ( $wgAudioExport && ! empty( $wgText2SpeechServiceUrl ) && $loopSettings->exportAudio ) {
			return true;
		}
		return false;
	}

	public function generateExportContent() {
		$this->exportContent = LoopMp3::structure2mp3($this->structure);
	}

	public function sendExportHeader() {

		$filename = $this->getExportFilename();

		header("Last-Modified: " . date("D, d M Y H:i:s T", strtotime($this->structure->lastChanged())));
		header("Content-Type: application/zip");
		header('Content-Disposition: attachment; filename="' . $filename . '";' );
		header("Content-Length: ". strlen($this->exportContent));
		header("Cache-Control: max-age=0, no-cache, no-store, must-revalidate");
		header("Expires: " . date("D, d M Y H:i:s T"));

	}

}
class LoopExportPageMp3 extends LoopExport {

	public function __construct($structure, $request) {
		$this->structure = $structure;
		$this->request = $request;
		$this->exportDirectory = '/export/mp3';
		$this->fileExtension = 'mp3';
		$this->lsi = null;

	}

	public static function isAvailable( $loopSettings = null ) {
		global $wgText2Speech, $wgText2SpeechServiceUrl;
		if ( $loopSettings === null ) {
			$loopSettings = new LoopSettings();
			$loopSettings->loadSettings();
		}
		if ( ! empty( $wgText2Speech ) && ! empty( $wgText2SpeechServiceUrl ) && $loopSettings->exportT2s ) {
			return true;
		}
		return false;
	}

	public function generateExportContent() {
		$query = $this->request->getQueryValues();
		set_time_limit(300);
		if ( isset( $query['articleId'] ) ) {
			if ( isset( $query['debug'] ) ) {
				$this->exportContent = LoopMp3::getMp3FromRequest($this->structure, $query['articleId'], $query['debug'] );
			} else {
				$this->exportContent = LoopMp3::getMp3FromRequest($this->structure, $query['articleId'], false );
			}
		} else {
			$this->exportContent = null;
		}
	}
	public function sendExportHeader() {

		header("Last-Modified: " . date("D, d M Y H:i:s T", strtotime($this->structure->lastChanged())));
		header("Content-Type: text/html");
		header("Content-Length: ". strlen($this->exportContent));
		header("Cache-Control: max-age=0, no-cache, no-store, must-revalidate");
		header("Expires: " . date("D, d M Y H:i:s T"));

	}

}

class LoopExportEpub extends LoopExport {

	public function __construct($structure) {
		$this->structure = $structure;
		$this->exportDirectory = '/export/epub';
		$this->fileExtension = 'epub';
	}

	public static function isAvailable( $loopSettings = null ) {
		global $wgEpubExport;
		if ( $loopSettings === null ) {
			$loopSettings = new LoopSettings();
			$loopSettings->loadSettings();
		}
		if ( $loopSettings->exportEpub && $wgEpubExport ) {
			return true;
		}
		return false;
	}

	public function generateExportContent() {
		$this->exportContent = '';
	}

	public function sendExportHeader() {
		$filename = $this->getExportFilename();

		header("Last-Modified: " . date("D, d M Y H:i:s T", strtotime($this->structure->lastChanged())));
		header("Content-Type: application/epub+zip");
		header('Content-Disposition: attachment; filename="' . $filename . '";' );
		header("Content-Length: ". strlen($this->exportContent));
		header("Cache-Control: max-age=0, no-cache, no-store, must-revalidate");
		header("Expires: " . date("D, d M Y H:i:s T"));

	}
}


class LoopExportHtml extends LoopExport {

	private $context;

	public function __construct($structure, $context) {
		$this->structure = $structure;
		$this->exportDirectory = '/export/html';
		$this->fileExtension = 'zip';
		$this->context = $context;
	}

	public static function isAvailable( $loopSettings = null ) {
		global $wgHtmlExport;
		if ( $loopSettings === null ) {
			$loopSettings = new LoopSettings();
			$loopSettings->loadSettings();
		}
		if ( $loopSettings->exportHtml && $wgHtmlExport ) {
			return true;
		}
		return false;
	}

	public function generateExportContent() {
		$this->exportContent = LoopHtml::structure2html($this->structure, $this->context, $this->exportDirectory);
	}

	public function sendExportHeader() {
		$filename = $this->getExportFilename();

		header("Last-Modified: " . date("D, d M Y H:i:s T", strtotime($this->structure->lastChanged())));
		header("Content-Type: application/zip");
		header('Content-Disposition: attachment; filename="' . $filename . '";' );
		header("Content-Length: ". strlen($this->exportContent));
		header("Cache-Control: max-age=0, no-cache, no-store, must-revalidate");
		header("Expires: " . date("D, d M Y H:i:s T"));

	}

}

class LoopExportScorm extends LoopExport {

	public function __construct($structure) {
		$this->structure = $structure;
		$this->exportDirectory = '/export/Scorm';
		$this->fileExtension = 'zip';
	}

	public static function isAvailable( $loopSettings = null ) {
		global $wgScormExport;
		if ( $loopSettings === null ) {
			$loopSettings = new LoopSettings();
			$loopSettings->loadSettings();
		}
		if ( $loopSettings->exportScorm && $wgScormExport ) {
			return true;
		}
		return false;
	}

	public function generateExportContent() {
		$this->exportContent = '';
	}

	public function sendExportHeader() {
		$filename = $this->getExportFilename();

		header("Last-Modified: " . date("D, d M Y H:i:s T", strtotime($this->structure->lastChanged())));
		header("Content-Type: application/zip");
		header('Content-Disposition: attachment; filename="' . $filename . '";' );
		header("Content-Length: ". strlen($this->exportContent));
		header("Cache-Control: max-age=0, no-cache, no-store, must-revalidate");
		header("Expires: " . date("D, d M Y H:i:s T"));

	}

}

class SpecialLoopExport extends SpecialPage {
	public function __construct() {
		parent::__construct( 'LoopExport' );
	}

	public function execute( $sub ) {

		$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
		$linkRenderer->setForceArticlePath(true);

		$userGroupManager = MediaWikiServices::getInstance()->getUserGroupManager();
		$permissionManager = MediaWikiServices::getInstance()->getPermissionManager();

		$out = $this->getOutput();
		$request = $this->getRequest();
		$user = $this->getUser();
		Loop::handleLoopRequest( $out, $request, $user ); #handle editmode

		$config = $this->getConfig();
		$context = $this->getContext();

		$out->setPageTitle( $this->msg( 'loopexport-specialpage-title' ) );

		$out->addHtml ('<h1>');
		$out->addWikiMsg( 'loopexport-specialpage-title' );
		$out->addHtml ('</h1>');

		$out->addHtml ($sub);
		$structure = new LoopStructure();
		$structure->loadStructureItems();
		$sub = mb_strtolower($sub);

		$export = false;
		switch ($sub) {
			case 'xml':
				if ($permissionManager->userHasRight( $user,  'loop-export-xml' ) && LoopExportXml::isAvailable() ) {
					$export = new LoopExportXml($structure, $request);
					$logEntry = new ManualLogEntry( 'loopexport', "xml");
				}
				break;
			case 'pdf':
				if ($permissionManager->userHasRight( $user,  'loop-export-pdf' ) && LoopExportPdf::isAvailable() ) {
					$export = new LoopExportPdf($structure);
					$logEntry = new ManualLogEntry( 'loopexport', "pdf");
				}
				break;
			case 'mp3':
				if ($permissionManager->userHasRight( $user,  'loop-export-mp3' ) && LoopExportMp3::isAvailable() ) {
					$export = new LoopExportMp3($structure);
					$logEntry = new ManualLogEntry( 'loopexport', "mp3");
				}
				break;
			case 'html':
				if ($permissionManager->userHasRight( $user,  'loop-export-html' ) && LoopExportHtml::isAvailable() ) {
					$export = new LoopExportHtml($structure, $context);
					$logEntry = new ManualLogEntry( 'loopexport', "html");
				}
			break;
			case 'epub':
				if ($permissionManager->userHasRight( $user,  'loop-export-epub' ) && LoopExportEpub::isAvailable() ) {
					$export = new LoopExportEpub($structure);
					$logEntry = new ManualLogEntry( 'loopexport', "epub");
				}
			break;
			case 'pageaudio':
				if ($permissionManager->userHasRight( $user,  'loop-pageaudio' ) && LoopExportPageMp3::isAvailable() ) {
					$export = new LoopExportPageMp3($structure, $request);
					# page audio logging is moved to LoopMp3.php
				}
				break;
		}

		if ( $export != false ) {
			$logMsg = "";
			if ( ! in_array( "sysop", $userGroupManager->getUserGroups( $user ) ) ) {
				error_reporting(E_ERROR | E_PARSE); # no error reports for non-admins
			}
//			if ( $export->getExistingExportFile() && $export->fileExtension != "mp3" ) {
//					$export->getExistingExportFile();
//					$logMsg = wfMessage("log-export-reused")->text();
//
//			} else {
				$export->generateExportContent();

				if ( $export->exportDirectory != "/export/html" && $export->fileExtension != "mp3" && !is_array( $export->getExportContent() ) ) { # don't cache html exports
					$export->saveExportFile();
					$logMsg = wfMessage("log-export-generated")->text();
				}
//			}
			if ( is_array( $export->getExportContent() ) ) {
				$msg = $this->msg( "loopexport-pdf-error" );

				if ( $permissionManager->userHasRight( $user, 'loop-pdf-test') ) {
					$msg .= $linkRenderer->makeLink( new TitleValue( NS_SPECIAL, 'LoopExportPdfTest' ), $this->msg( "loopexportpdftest" )->parse() );
				} elseif ( LoopBugReport::isAvailable() != false ) {
					$msg .= $linkRenderer->makeLink( new TitleValue( NS_SPECIAL, 'LoopBugReport' ), $this->msg( "loopbugreport" )->parse(), array(), array( "url" => "PDF Export", "page" => "Special:LoopExport/pdf" ) );
				}
				$html = Html::rawElement( 'div', array( 'class' => 'errorbox' ), $msg );
				if ( in_array( "sysop", $userGroupManager->getUserGroups( $user ) ) ) {
					$errors = $export->getExportContent();
					$html .= '<pre class=""><b>Error:</b> <br>'.implode("\n", array_slice(explode("\n", $errors[0]), 1)) .'</pre>';
					$html .= '<pre class="d-none"><b>XML</b>: <br>'.htmlspecialchars($errors[2]).'</pre>';
					$html .= '<pre class="d-none"><b>XMLFO:</b> <br>'.htmlspecialchars($errors[1]["xmlfo"]) .'</pre><br>';
				}
				$this->getOutput()->addHTML( $html );

			} elseif ($export->getExportContent() != null ) {

				if ( isset($logEntry) ) {
					$logEntry->setTarget( Title::newFromId($structure->mainPage) );
					$logEntry->setPerformer( User::newFromId(0) );
					$logEntry->setParameters( [ '4::paramname' => $logMsg ] );
					$logid = $logEntry->insert();
				}

				$this->getOutput()->disable();
				wfResetOutputBuffers();
				$export->sendExportHeader();
				echo $export->getExportContent();
			}

		} else {

			$out->addHtml('<ul>');

			if ($permissionManager->userHasRight( $user,  'loop-export-xml' ) && LoopExportXml::isAvailable() ) {
				$xmlExportLink = $linkRenderer->makeLink( new TitleValue( NS_SPECIAL, 'LoopExport/xml' ), new HtmlArmor(wfMessage ( 'export-linktext-xml' )->inContentLanguage ()->text () ));
				$out->addHtml ('<li>'.$xmlExportLink.'</li>');
			}

			if ($permissionManager->userHasRight( $user,  'loop-export-pdf' ) && LoopExportPdf::isAvailable() ) {
				$pdfExportLink = $linkRenderer->makeLink( new TitleValue( NS_SPECIAL, 'LoopExport/pdf' ), new HtmlArmor(wfMessage ( 'export-linktext-pdf' )->inContentLanguage ()->text () ));
				$out->addHtml ('<li>'.$pdfExportLink.'</li>');
			}

			if ($permissionManager->userHasRight( $user,  'loop-export-mp3' ) && LoopExportMp3::isAvailable() ) {
				$mp3ExportLink = $linkRenderer->makeLink( new TitleValue( NS_SPECIAL, 'LoopExport/mp3' ), new HtmlArmor(wfMessage ( 'export-linktext-mp3' )->inContentLanguage ()->text () ));
				$out->addHtml ('<li>'.$mp3ExportLink.'</li>');
			}

			if ($permissionManager->userHasRight( $user,  'loop-export-html' ) && LoopExportHtml::isAvailable() ) {
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
