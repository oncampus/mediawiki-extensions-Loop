<?php 
class LoopMp3 { 

	public static function page2mp3( $loopStructure, $lsi ) { #TODO nicht nur structure items!

		global $wgLanguageCode, $wgUploadDirectory;
		$loopExportMp3 = new LoopExportMp3($loopStructure, $lsi);

		$loopExportXml = new LoopExportXml($loopStructure, $lsi);
		$loopExportXml->generatePageExportContent( );

		$loopExportXmlPage = "<?xml version='1.0' encoding='UTF-8' ?>\n";
		$loopExportXmlPage.= "<loop ";
		$loopExportXmlPage.= "xmlns:xhtml=\"http://www.w3.org/1999/xhtml\" ";
		$loopExportXmlPage.= ">";

		$loopExportXmlPage .= $loopExportXml->exportContent;
		$loopExportXmlPage.= "</loop>";
		//dd( $loopExportXmlPage);
		$loopExportSsml = LoopMp3::transformToSsml( $loopExportXmlPage );
		
		

		$responseData = LoopMp3::requestArticleAsMp3( $loopExportSsml, $wgLanguageCode, "ssml" );

		
$mp3file = fopen($wgUploadDirectory . "/" . $loopExportMp3->exportDirectory . ".mp3" , 'w') or die("can't write mp3 file");
fwrite($mp3file, $responseData);
fclose($mp3file);
dd();

		return true;
	}



	public static function structure2mp3(LoopStructure $loopStructure) {
		
		
		/*
		
		$structureItems  = $loopStructure->getStructureItems();
				
		$structureItemDir = $wgUploadDirectory.'/export/mp3/structureitems/';
		
		foreach ($structureItems as $structureItem) {
			
			wfDebug("\n".__METHOD__.':structureItem:'.print_r($structureItem,true));
			
			$last_changed = $structureItem->lastChanged();
			$last_changed_ts = wfTimestamp(TS_UNIX,$last_changed);
			
			
			wfDebug("\n".__METHOD__.':last changed:'.print_r($last_changed,true));
			wfDebug("\n".__METHOD__.':last changed ts:'.print_r($last_changed_ts,true));
			
			$generate = false;
			$structureItemFilename = $structureItemDir.strval($structureItem->getId());
			unset ($filemtime);
			if (is_file($structureItemFilename)) {
				$filemtime = filemtime($structureItemFilename);
				
				if ($filemtime == $last_changed_ts) {
					// keine Änderung seit der letzten MP3 Erzeugung
					$generate = false;
				} else {
					// Es liegt eine Änderung seit der letzten MP3 Erzeugung vor
					$generate = true;
				}
				
				
			} else {
				// Noch keine MP3 Datei für StructureItem vorhanden -> neu erzeugen
				$generate = true;
			}
			
			if ($generate == true) {
				
			}
			
			
		}
		*/
		
		$loopStructureItems = $loopStructure->getStructureItems();		
		

		foreach ( $loopStructureItems as $lsi ) {

			LoopMp3::page2Mp3( $loopStructure, $lsi );
			//$loopExportXml->saveExportFile();
			//var_dump($loopExportSsml);
		}

		//$loopExportXml = new LoopExportXml($loopStructure);
		//$wiki_xml = $loopExportXml->generateExportContent();
		
	//var_dump($wiki_xml); exit;
		

		

		//
		
		/*
		 * ToDo
		 * Wiki XML holen
		 * Wiki XML in SSML transformieren
		 * SSML aufspalten auf Seitentexte
		 * 
		 * Temp Verzeichnis anlegen
		 * Jeden Teil SSML an den Toolsserver / Polly schicken
		 * Ergebnis als MP3 speichern
		--------------------------------
		
		Komplettes Audiobook als Zip
		
		
		alle Seiten der Structure in export/mp3 ablegen
		---[articleid]_[lasttouched].mp3
		[structureitemid]_[lasttouched].mp3
		
		xml erstellen
		xsl -> audiotext
		
		für alle structureitems
		überprüfen ob aktuelle mp3 vorliegt
		falls nicht mp3 neu erstellen
		
		zip erstellen
		
		
		Einzelne MP3
		
		LoopPageAudio
		
		
		beim Ändern der Structure
		exports löschen: pdf, epub, offline, mp3
		beim structureitem löschen mp3 löschen
		
		
		 */
		return true;
		
		
	}

	public static function transformToSsml ( $wiki_xml ) {
		global $IP, $wgUploadDirectory;
		//dd($wiki_xml );
		try {
			
			$xml = new DOMDocument();
			$xml->loadXML($wiki_xml);
		} catch (Exception $e) {
			echo "exeption 1";
			return $e;
		}
		
		try {
			$xsl = new DOMDocument;
			$xsl->load($IP.'/extensions/Loop/xsl/ssml.xsl');
		} catch (Exception $e) {
			echo "exeption 2";
			return $e;
		}
		
		try {
			$proc = new XSLTProcessor;
			$proc->registerPHPFunctions();
			$proc->importStyleSheet($xsl);
			$ssml = $proc->transformToXML($xml);
		} catch (Exception $e) {
			echo "exeption 3";
			return $e;
		}
		
		if ( isset( $ssml ) ) {
			return $ssml;
		} else {
			return false;
		}
	}

	private static function requestArticleAsMp3( $content, $language, $type ) {

		global $wgText2SpeechServiceUrl;
		//dd($content);
		$params = "srctext=".urlencode ($content)."&language=".$language."&type=".$type;
//dd($params);
		$mp3Response = LoopMp3::httpRequest( $wgText2SpeechServiceUrl, $params );

		//echo ":)";
		
		return $mp3Response ;

	}

	public static function httpRequest( $url, $params ) {
		
		//$cookie = '/tmp/'.uniqid().'cookies.tmp';
		//global $cookie;
		$ch = curl_init();
		curl_setopt ( $ch, CURLOPT_USERAGENT, 'LOOP2');
		curl_setopt ( $ch, CURLOPT_POST, true );
		curl_setopt ( $ch, CURLOPT_URL, ( $url ) );
		curl_setopt ( $ch, CURLOPT_ENCODING, "UTF-8" );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
		//curl_setopt ( $ch, CURLOPT_COOKIEFILE, $cookie );
		//curl_setopt ( $ch, CURLOPT_COOKIEJAR, $cookie );
		if ( ! empty( $params ) ) curl_setopt( $ch, CURLOPT_POSTFIELDS, $params );
		$return = curl_exec( $ch );
		if ( empty( $return ) ) {
			return "error";
			//throw new Exception( "Error getting data from server ($url): " . curl_error( $ch ) );
		}
		curl_close( $ch );
		return $return;

	}
	
}