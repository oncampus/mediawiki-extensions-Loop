<?php


abstract class LoopExport {

	public $structure;
	public $exportContent;
	public $exportDirectory;
	public $fileExtension;

	abstract protected function generateExportContent();


	public function getExistingExportFile() {
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
		if ( $this->lsi != null ) {
			$export_dir .= '/'. $this->lsi->article;
		
		}
		//var_dump( $export_dir );
		if (!is_dir($export_dir)) {
			@mkdir($export_dir, 0777, true);
		}
		if ( $this->lsi != null ) {
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
		if ($handle = opendir($export_dir)) {
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

	public function generateExportContent() {
		$query = array();
		if ( isset( $this->request ) ) {
			$query = $this->request->getQueryValues();
		}
		if ( isset( $query['articleId'] ) ) {
			$this->exportContent = LoopXml::articleFromId2xml( $query['articleId'] );
			var_dump($this->exportContent); exit; //debug output of page
		} else {
			$this->exportContent = LoopXml::structure2xml($this->structure);
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

	}

	// for Development
	public function getExistingExportFile() {
		return false;
	}
}


class LoopExportPdf extends LoopExport {

	public function __construct($structure) {
		$this->structure = $structure;
		$this->exportDirectory = '/export/pdf';
		$this->fileExtension = 'pdf';
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

	}

	// for Development
	public function getExistingExportFile() {
		return false;
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
	public function generateExportContent() {
		
		$this->exportContent = LoopMp3::structure2mp3($this->structure);
		
	}
	public function sendExportHeader() {

		$filename = $this->getExportFilename();
			
		header("Last-Modified: " . date("D, d M Y H:i:s T", strtotime($this->structure->lastChanged())));
		header("Content-Type: application/zip");
		header('Content-Disposition: attachment; filename="' . $filename . '";' );
		header("Content-Length: ". strlen($this->exportContent));

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
	public function generateExportContent() {
		$query = $this->request->getQueryValues();
		if ( isset( $query['articleId'] ) ) {
			$this->exportContent = LoopMp3::getMp3FromRequest($this->structure, $query['articleId'] );
		} else {
			$this->exportContent = null;
		}
	}
	public function sendExportHeader() {
			
		header("Last-Modified: " . date("D, d M Y H:i:s T", strtotime($this->structure->lastChanged())));
		header("Content-Type: text/html");
		header("Content-Length: ". strlen($this->exportContent));

	}
	
}

class LoopExportEpub extends LoopExport {

	public function __construct($structure) {
		$this->structure = $structure;
		$this->exportDirectory = '/export/epub';
		$this->fileExtension = 'epub';
	}

	public function generateExportContent() {
		$this->exportContent = ''; // ToDo: LoopEpub
	}

	public function sendExportHeader() {
		$filename = $this->getExportFilename();

		header("Last-Modified: " . date("D, d M Y H:i:s T", strtotime($this->structure->lastChanged())));
		header("Content-Type: application/epub+zip");
		header('Content-Disposition: attachment; filename="' . $filename . '";' );
		header("Content-Length: ". strlen($this->exportContent));

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

	public function generateExportContent() {
		$this->exportContent = LoopHtml::structure2html($this->structure, $this->context, $this->exportDirectory);
	}

	public function sendExportHeader() {
		$filename = $this->getExportFilename();

		header("Last-Modified: " . date("D, d M Y H:i:s T", strtotime($this->structure->lastChanged())));
		header("Content-Type: application/zip");
		header('Content-Disposition: attachment; filename="' . $filename . '";' );
		header("Content-Length: ". strlen($this->exportContent));

	}

}

class LoopExportScorm extends LoopExport {

	public function __construct($structure) {
		$this->structure = $structure;
		$this->exportDirectory = '/export/Scorm';
		$this->fileExtension = 'zip';
	}

	public function generateExportContent() {
		$this->exportContent = ''; // ToDo: LoopScorm
	}

	public function sendExportHeader() {
		$filename = $this->getExportFilename();

		header("Last-Modified: " . date("D, d M Y H:i:s T", strtotime($this->structure->lastChanged())));
		header("Content-Type: application/zip");
		header('Content-Disposition: attachment; filename="' . $filename . '";' );
		header("Content-Length: ". strlen($this->exportContent));

	}

}
