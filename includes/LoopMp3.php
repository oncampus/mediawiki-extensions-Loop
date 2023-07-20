<?php

if ( !defined( 'MEDIAWIKI' ) ) die ( "This file cannot be run standalone.\n" );

use MediaWiki\MediaWikiServices;

class LoopMp3 {

	/**
	 * Get page by id downloaded as mp3
	 *
	 * @param LoopStructure $loopStructure
	 * @param int $articleId
	 */
	public static function getMp3FromRequest( $loopStructure, $articleId, $debug = false ) {

		global $wgUploadPath;

		$lsi = LoopStructureItem::newFromIds($articleId);

		if ( $lsi ) {

			$loopStructureItems = $loopStructure->getStructureItems();

			$loopExportXml = new LoopExportXml($loopStructure);
			$modifiers = array( "mp3" => true );
			$loopExportXml->generateExportContent( $modifiers );
			$structureXml = $loopExportXml->exportContent;
			$domStructure = new domDocument('1.0', 'utf-8');
			$domStructure->loadXML($structureXml);

			$loopObjectsNodes = $domStructure->getElementsByTagName("loop_objects");
			$articleNodes = $domStructure->getElementsByTagName("article");

			foreach ( $articleNodes as $node ) {
				$node->appendChild( $loopObjectsNodes[0] );
				$tmpData = LoopMp3::getArticleXmlFromStructureXml( $node );
				if ( $tmpData["articleId"] == $articleId ) {
					$mp3File = LoopMp3::page2Mp3( $loopStructure, $tmpData["articleXml"], $tmpData["articleId"], $tmpData["lastChanged"], $debug );
					$mp3FilePath = $wgUploadPath."/export/mp3/".$loopStructure->getId()."/$articleId/$articleId"."_".$tmpData["lastChanged"].".mp3";

					return $mp3FilePath;
				}
			}

		} else {

			$wikiPage = MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle( Title::newFromId( $articleId ));
			$articleXml = LoopXml::articleFromId2xml( $articleId );
			$lastChanged = $wikiPage->getTouched();

			$mp3File = LoopMp3::page2Mp3( $loopStructure, $articleXml, $articleId, $lastChanged, $debug );
			$mp3FilePath = $wgUploadPath."/export/mp3/ns/$articleId/$articleId"."_"."$lastChanged.mp3";

			return $mp3FilePath;
		}

	}

	/**
	 * Checks for nested tags created in ssml and replaces them with speak tags
	 *
	 * @param string $ssml
	 */
	public static function checkNestedTags( $ssml ) {

		$returnSsml1 = preg_replace('/(<replace_speak_next)( voice="\d")(\/>)/', '<speak$2>', $ssml);
		$returnSsml2 = preg_replace('/(<replace_speak)/', '</speak><speak', $returnSsml1);
		$returnSsml = preg_replace('/(<\/replace_speak>)/', '</speak>', $returnSsml2);
		#dd($ssml, $returnSsml1, $returnSsml2, $returnSsml);
		return $returnSsml;
	}

	/**
	 * Downloads given XML or SSML as mp3 from service
	 *
	 * @param LoopStructure $loopStructure
	 * @param string $articleXml
	 * @param int $articleId
	 * @param string $lastChanged
	 */
	public static function page2mp3( $loopStructure, $articleXml, $articleId, $lastChanged, $debug = false ) {

		global $wgLanguageCode, $wgUploadDirectory;

		$loopExportMp3 = new LoopExportMp3($loopStructure);
		$lsi = LoopStructureItem::newFromIds($articleId);

		if ( $lsi || $articleId == "intro" ) {
			$structureFolder = $loopStructure->getId();
		} else {
			$structureFolder = "ns";
		}

		$filePath = $wgUploadDirectory . $loopExportMp3->exportDirectory ."/". $structureFolder ."/". $articleId ."/";
		$fileName = $articleId . "_" . $lastChanged . ".mp3";
		$filePathName = $filePath . $fileName;

		$fileUpToDate = LoopMp3::existingFileUpToDate( $filePath, $lastChanged, "mp3" );
		if ( $debug ) {
			dd(LoopMp3::transformToSsml( $articleXml ),$articleXml); # exit at first article ssml and xml
		}
		if ( ! $fileUpToDate ) {
			global $IP, $wgSitename;

			if ( $articleId == "intro" ) {
				$loopExportSsml = $articleXml;
				$id3tag_track = "0";
			} else {
				$loopExportSsml = LoopMp3::transformToSsml( $articleXml );
				#dd($loopExportSsml,$articleXml); # exit at first article ssml and xml
			}
			$loopExportSsml = self::checkNestedTags($loopExportSsml);
			if ( $debug ) {
				dd($loopExportSsml,$articleXml); # exit at first article ssml and xml
			}
			$responseData = LoopMp3::requestArticleAsMp3( $loopExportSsml, $wgLanguageCode, "ssml" );

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

				$id3tag_title = wfMessage( "loopexport-audio-chapter" )->text() . " " . $lsi->tocNumber .' - '. $lsi->tocText;

				$tagData['title'] = array( $id3tag_title );
				$tagData['track'] = array( $id3tag_track );
				# Autoren-url mit Link zur Seite?
				# Copyright?
			} elseif ( $articleId == "intro" ) {
				$tagData['title'] = array( wfMessage("loopexport-audio-intro")->text() ." ". $wgSitename );
				$tagData['track'] = array( $id3tag_track );
			} else {
				$title = Title::newFromId($articleId);
				$tagData['title'] = array( $title->getText() );
			}

			$tagwriter->tag_data = $tagData;
			$tagwriter->WriteTags();

			LoopMp3::writeLog( wfMessage("log-export-generated")->text(), $articleId );

			return $filePathName;

		} elseif ( $fileUpToDate ) {
			LoopMp3::writeLog( wfMessage("log-export-reused")->text(), $articleId );
			return $filePathName;
		} else {
			return false;
		}
	}
	/**
	 * Adds a log entry for page audio export.
	 *
	 * @param string $msg
	 * @param string $articleId
	 */
	private static function writeLog( $msg, $articleId ) {
		if ($articleId != "intro" ) {
			$logEntry = new ManualLogEntry( 'loopexport', 'pageaudio');
			$logEntry->setTarget( Title::newFromId($articleId) );
			$logEntry->setPerformer( User::newFromId(0) );
			$logEntry->setParameters( [ '4::paramname' => $msg ] );
			$logid = $logEntry->insert();
		}
		return true;

	}
	/**
	 * Checks if a given existing file is up to date compared with given lastChanged date
	 *
	 * @param LoopStructure $loopStructure
	 * @param string $lastChanged
	 * @param string $fileExtension
	 */
	private static function existingFileUpToDate( $filePath, $lastChanged, $fileExtension ) {

		if ( !file_exists ( $filePath ) ) { // create directory if non-existent
			mkdir( $filePath, 0775, true );
			return false; # file not created yet
		} else {

			$fileList = preg_grep('/([\d]{1,}[_]{1}[\d]{1,})(.'.$fileExtension.')/i', scandir($filePath)); // list of all audio files in directory like 2_20190319093656.mp3

			if ( ! empty( $fileList ) ) {
				$fileName = $fileList[2];
				$fileDate = explode( "_", $fileName );
				$fileLastChangedDate = explode( ".", $fileDate[1] );

				if ( $fileLastChangedDate[0] != $lastChanged ) {
					unlink($filePath.$fileName);
					return false; # there have been changes made on page
				} else {
					return true; # file still up to date
				}
			} else {
				return false;
			}
		}
	}


	/**
	 * Downloads a whole LoopStructure as mp3 and delivers it as ZIP.
	 *
	 * @param LoopStructure $loopStructure
	 */
	public static function structure2mp3( LoopStructure $loopStructure ) {

		global $wgUploadDirectory, $wgCanonicalServer;

		$loopStructureItems = $loopStructure->getStructureItems();

		$loopExportXml = new LoopExportXml($loopStructure);
		$modifiers = array( "mp3" => true );
		$loopExportXml->generateExportContent( $modifiers );
		$structureXml = $loopExportXml->exportContent;
		$domStructure = new domDocument('1.0', 'utf-8');
		$domStructure->loadXML($structureXml);
		$articleNodes = $domStructure->getElementsByTagName("article");
		$loopObjectsNodes = $domStructure->getElementsByTagName("loop_objects");

		$introSsml = LoopMp3::createIntroductionSsml ( $loopStructure );
		//dd($introSsml); # exit at intro ssml
		$introMp3FilePath = LoopMp3::page2Mp3( $loopStructure, $introSsml, "intro", $loopStructure->lastChanged() );

		$urlparts = mb_split("\.", $wgCanonicalServer);
		if (isset($urlparts[0])) {
			$hashtag = preg_replace("/(http[s]{0,1}:\/\/)/i", "", $urlparts[0]);
		} else {
			$hashtag = "";
		}

		$getID3 = new getID3;

		$fileName = "000";
		if (!empty($hashtag)) {
			$fileName .= "_" . $hashtag;
		}

		$mp3Files = array(
			array(
				"path" => $introMp3FilePath,
				"fileName" => $fileName
			)
		);

		foreach ( $articleNodes as $node ) {

			$node->appendChild( $loopObjectsNodes[0] );
			$tmpData = LoopMp3::getArticleXmlFromStructureXml( $node );
			//dd($introSsml); # exit at first article xml
			if ( !empty( $tmpData ) ) {
				$mp3FilePath = LoopMp3::page2Mp3( $loopStructure, $tmpData["articleXml"], $tmpData["articleId"], $tmpData["lastChanged"] );
				$tmpTrack = $getID3->analyze($mp3FilePath)["id3v2"]["TRCK"][0]["data"];
				$tmpTrack = sprintf('%03d',$tmpTrack);

				$fileName = $tmpTrack;
				if (!empty($hashtag)) {
					$fileName .= "_" . $hashtag;
				}
				$mp3Files[] = array(
					"path" => $mp3FilePath,
					"fileName" => $fileName
				);
			}
		}
		#dd($mp3Files);

		$loopExportMp3 = new LoopExportMp3($loopStructure);
		$exportDirectory = $wgUploadDirectory . $loopExportMp3->exportDirectory;

		$tmpZipPath = $exportDirectory.'/'.$loopStructure->getId().'/tmp/tmpfile.zip';
		$tmpDirectoryToZip = $exportDirectory.'/'.$loopStructure->getId().'/tmp';
		if ( !file_exists ( $tmpDirectoryToZip ) ) {
			mkdir( $tmpDirectoryToZip, 0775, true );
		}

		foreach ( $mp3Files as $file ) {
			$oldPath = $file["path"];
			$storedFile = fopen($oldPath, 'r');
			$storedFileContent = fread($storedFile, filesize($oldPath));
			fclose($storedFile);

			$newPath = $tmpDirectoryToZip ."/". $file["fileName"].".mp3";
			$tmpfile = fopen($newPath, 'w') or die("can't write mp3file");
			fwrite($tmpfile, $storedFileContent);
			fclose($tmpfile);
		}

		$zip = new ZipArchive();
		$zip->open( $tmpZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

		$files = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $tmpDirectoryToZip ),
			RecursiveIteratorIterator::LEAVES_ONLY
		);

		foreach ( $files as $name => $file ) {
			if ( ! $file->isDir() ) {
				$tmpFilePath = $file->getRealPath();
				$tmpRelativePath = substr($tmpFilePath, strlen($tmpDirectoryToZip) + 1);
				$zip->addFile( $tmpFilePath, $tmpRelativePath );
				$filesToDelete[] = $tmpFilePath;
			}
		}

		$zip->close();
		$zip = file_get_contents( $tmpZipPath );

		foreach ($filesToDelete as $file) {
			unlink($file);
		}

		unlink( $tmpZipPath );
		rmdir ( $tmpDirectoryToZip );

		return $zip;

	}

	/**
	 * Outputs XML of a single article from a whole structure xml
	 *
	 * @param DomNode $node
	 */
	private static function getArticleXmlFromStructureXml( $node ) {

		global $wgLanguageCode;
		$langParts = mb_split("-", $wgLanguageCode);

		$data["articleId"] = str_replace("article", "", $node->getAttribute("id"));

		if ( !empty( $data["articleId"] ) ) {
			$tmpDom = new domDocument('1.0', 'utf-8');
			$tmpNode = $tmpDom->importNode($node, true);

			# create meta tag for languages
			$metaNode = $tmpDom->createElement("meta");
			$langContent = $tmpDom->createTextNode($langParts[0]);
			$langNode = $tmpDom->createElement("lang");
			$langNode->appendChild($langContent);
			$metaNode->appendChild($langNode);
			$tmpNode->appendChild( $metaNode );

			$tmpDom->appendChild( $tmpNode );

			$data["toctext"] = $tmpNode->getAttribute("toctext");
			$data["tocnumber"] = $tmpNode->getAttribute("tocnumber");
			$data["articleXml"] = $tmpDom->saveXml();

			$structureItem = LoopStructureItem::newFromIds($data["articleId"]);
			$data["lastChanged"] = "";
			if ( $structureItem != false ) {
				$data["lastChanged"] = $structureItem->lastChanged();
			}

			return $data;
		}
		return null;
	}

	/**
	 * Transforms XML to SSML
	 *
	 * @param string $wiki_xml
	 */
	private static function transformToSsml ( $wiki_xml ) {
		global $IP, $wgUploadDirectory;

		#require_once ($IP."/extensions/Loop/xsl/LoopXsl.php");

		try {

			$xml = new DOMDocument('1.0', 'utf-8');
			$xml->loadXML($wiki_xml);
		} catch (Exception $e) {
			return $e;
		}

		try {
			$xsl = new DOMDocument('1.0', 'utf-8');
			$xsl->load($IP.'/extensions/Loop/xsl/ssml.xsl');
		} catch (Exception $e) {
			return $e;
		}

		try {
			$proc = new XSLTProcessor;
			$proc->registerPHPFunctions();
			$proc->importStyleSheet($xsl);
			$ssml = $proc->transformToXML($xml);
		} catch (Exception $e) {
			return $e;
		}

		if ( isset( $ssml ) ) {
			return $ssml;
		} else {
			return false;
		}
	}

	/**
	 * Requests the mp3 data from service
	 *
	 * @param string $content
	 * @param string $language
	 * @param string $type
	 */
	private static function requestArticleAsMp3( $content, $language, $type ) {

		global $wgText2SpeechServiceUrl;
		#dd($content);
		$params = "srctext=".urlencode ($content)."&language=".$language."&type=".$type;
		print_r($params);
		$mp3Response = LoopMp3::httpRequest( $wgText2SpeechServiceUrl, $params );

		return $mp3Response ;

	}

	/**
	 * Makes http request
	 *
	 * @param string $url
	 * @param string $params
	 */
	private static function httpRequest( $url, $params ) {

		$ch = curl_init();
		curl_setopt ( $ch, CURLOPT_USERAGENT, 'LOOP2');
		curl_setopt ( $ch, CURLOPT_POST, true );
		curl_setopt ( $ch, CURLOPT_URL, ( $url ) );
		curl_setopt ( $ch, CURLOPT_ENCODING, "UTF-8" );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
		if ( ! empty( $params ) ) curl_setopt( $ch, CURLOPT_POSTFIELDS, $params );
		$return = curl_exec( $ch );

		if ( empty( $return ) ) {
			throw new Exception( "Error getting data from server ($url): " . curl_error( $ch ) );
		}
		curl_close( $ch );
		return $return;

	}

	/**
	 * Generates introduction as SSML
	 *
	 * @param LoopStructure $loopStructure
	 */
	private static function createIntroductionSsml ( $loopStructure ) {
		global $wgLanguageCode, $wgCanonicalServer;

		$langParts = mb_split("-", $wgLanguageCode);

		$intro = '<?xml version="1.0" encoding="UTF-8"?>';
		$intro .= '<article id="intro">';
		$intro .= '<speak voice="1">';
		$intro .= '<p>'. wfMessage("loopexport-audio-intro-title", '<break strength="strong"/>'.$loopStructure->getTitle() )->text() .'.</p>';
		$intro .= '</speak>';

		$dateFormat = array("dmy", "d-m-Y");
		if ( $langParts[0] == "en" ) {
			$dateFormat = array("mdy", "m-d-Y");
		}
		$spokenUrl = str_replace("http://", "", $wgCanonicalServer);
		$spokenUrl = str_replace("https://", "", $spokenUrl);

		$intro .= '<speak voice="2">';
		$intro .= '<p>'. wfMessage("loopexport-audio-intro-url", $spokenUrl )->text() .'</p>';

		$date = '<say-as interpret-as="date" format="'.$dateFormat[0].'">'.date($dateFormat[1],strtotime($loopStructure->lastChanged())).'</say-as>';
		$intro .= '<p>'. wfMessage("loopexport-audio-intro-date", $date )->text() .'</p>';
		$intro .= '</speak>';
		$intro .= '</article>';

		return $intro;
	}

}
