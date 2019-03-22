<?php 
class LoopMp3 { 
	public static function getMp3FromRequest( $loopStructure, $articleId ) {

		$lsi = LoopStructureItem::newFromIds($articleId); 

		if ( $lsi ) {
				
			$loopStructureItems = $loopStructure->getStructureItems();		
			
			$loopExportXml = new LoopExportXml($loopStructure);
			$loopExportXml->generateExportContent( );
			$structureXml = $loopExportXml->exportContent;
			$domStructure = new domDocument;
			$domStructure->loadXML($structureXml);

			$articleNodes = $domStructure->getElementsByTagName("article");
			
			foreach ( $articleNodes as $node ) {

				$tmpData = LoopMp3::getArticleXmlFromStructureXml( $node );
				if ( $tmpData["articleId"] == $articleId ) {
					$mp3FilePath = LoopMp3::page2Mp3( $loopStructure, $tmpData["articleXml"], $tmpData["articleId"], $tmpData["lastChanged"] );
					
					return $mp3FilePath;
				}
			}

		} else {

			$wikiPage = WikiPage::factory( Title::newFromId( $articleId ));
			$articleXml = LoopXml::articleFromId2xml( $articleId );
			$lastChanged = $wikiPage->getTouched();

			$mp3FilePath = LoopMp3::page2Mp3( $loopStructure, $articleXml, $articleId, $lastChanged );

			return $mp3FilePath;
		}

	}

	public static function page2mp3( $loopStructure, $loopStructureXml, $articleId, $lastChanged ) { #TODO nicht nur structure items!

		global $wgLanguageCode, $wgUploadDirectory;

		$loopExportMp3 = new LoopExportMp3($loopStructure);
		$loopExportSsml = LoopMp3::transformToSsml( $loopStructureXml );
		$responseData = LoopMp3::requestArticleAsMp3( $loopExportSsml, $wgLanguageCode, "ssml" );
		$lsi = LoopStructureItem::newFromIds($articleId); 

		if ( $lsi ) {
			$structureFolder = $loopStructure->getId();
		} else {
			$structureFolder = "ns";
		}

		$filePath = $wgUploadDirectory . $loopExportMp3->exportDirectory ."/". $structureFolder ."/". $articleId ."/";
		$fileName = $articleId . "_" . $lastChanged . ".mp3";
		$filePathName = $filePath . $fileName;
		
		$fileToUse = LoopMp3::checkForExistingFile( $filePath, $lastChanged );

		if ( $fileToUse == "create" || $fileToUse == "update" ) {
			global $IP, $wgSitename;

			$mp3File = fopen( $filePathName , 'w') or die("can't write mp3 file");
			fwrite($mp3File, $responseData);
			fclose($mp3File);

			require_once ($IP."/extensions/Loop/vendor/james-heinrich/getid3/getid3/getid3.php");
			require_once ($IP."/extensions/Loop/vendor/james-heinrich/getid3/getid3/write.php");

			$getID3 = new getID3;
			$getID3->setOption( array( 'encoding' => 'UTF-8' ) );
			$tagwriter = new getid3_writetags;
			$tagwriter->filename = $filePathName;
			$tagwriter->tagformats = array('id3v1', 'id3v2.3');
			$tagwriter->overwrite_tags = true;
			$tagwriter->tag_encoding = 'UTF-8';
			$tagwriter->remove_other_tags = false;

			$tagData = array(
				'artist'	=> array( 'oncampus' ),
				'album'		=> array( $wgSitename ),
				'year'		=> array( date('Y') ),
				'genre' 	=> array( 'E-Learning' ),
			);

			if ( $lsi ) {

				$loopStructureItems = $loopStructure->getStructureItems();		
				$totalArticles = sizeof($loopStructureItems);
				$pad_length = strlen(strval($totalArticles));
				$id3tag_track = str_pad(($lsi->sequence + 1), $pad_length, "0", STR_PAD_LEFT).'/'.str_pad($totalArticles, $pad_length, "0", STR_PAD_LEFT);
				
				$id3tag_title = wfMessage( "loopexport-audio-chapter" ) . " " . $lsi->tocNumber .' - '. $lsi->tocText;
				
				$tagData['title'] = array( $id3tag_title );
				$tagData['track'] = array( $id3tag_track );
				# Autoren-url mit Link zur Seite?
				# Copyright?
			} else {

				$title = Title::newFromId($articleId);
				$tagData['title'] = array( $title->mTextform );

			}
			
			$tagwriter->tag_data = $tagData;
			$tagwriter->WriteTags();

			return $filePathName;
			
		} elseif ( $fileToUse == "reuse" ) {
			return $filePathName;
		} else {
			return false;
		}
	}

	public static function checkForExistingFile( $filePath, $lastChanged ) {

		if ( !file_exists ( $filePath ) ) { // create directory if non-existent
			mkdir( $filePath, 0775, true );
			return "create";
		} else {

			$fileList = preg_grep('/([\d]{1,}[_]{1}[\d]{1,})(.mp3)/i', scandir($filePath)); // list of all audio files in directory like 2_20190319093656.mp3
			#todo evtl auch für structureid_structurelastchanged.zip benutzen?
			if ( ! empty( $fileList ) ) { 
				$fileName = $fileList[2];
				$fileDate = explode( "_", $fileName );
				$fileLastChangedDate = explode( ".", $fileDate[1] ); 

				if ( $fileLastChangedDate[0] != $lastChanged ) {
					unlink($filePath.$fileName);
					return "update"; # there have been changes made on page
				} else {
					return "reuse";
				}
			} else {
				return "create";
			}
		}
	}


	public static function structure2mp3(LoopStructure $loopStructure) {
		
		$loopStructureItems = $loopStructure->getStructureItems();		

		$loopExportXml = new LoopExportXml($loopStructure);
		$loopExportXml->generateExportContent( );
		$structureXml = $loopExportXml->exportContent;
		$domStructure = new domDocument;
		$domStructure->loadXML($structureXml);

		$articleNodes = $domStructure->getElementsByTagName("article");

		$mp3Files = array();

		//var_dump($tmpDom2->saveHTML());
		foreach ( $articleNodes as $node ) {

			$tmpData = LoopMp3::getArticleXmlFromStructureXml( $node );

			$mp3FilePath = LoopMp3::page2Mp3( $loopStructure, $tmpData["articleXml"], $tmpData["articleId"], $tmpData["lastChanged"] );
			
			$mp3Files[] = $mp3FilePath;

		}
		#todo zip!

		return true;
	
	}
	private static function getArticleXmlFromStructureXml( $node ) {

		$data["articleId"] = str_replace("article", "", $node->getAttribute("id"));

		$tmpDom = new domDocument;
		$tmpNode = $tmpDom->importNode($node, true);
		$tmpDom->appendChild( $tmpNode );

		$data["articleXml"] = $tmpDom->saveXml(); 

		$structureItem = LoopStructureItem::newFromIds($data["articleId"]);
		$data["lastChanged"] = $structureItem->lastChanged();
		
		return $data;

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