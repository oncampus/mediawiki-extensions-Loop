<?php 
$server = $_REQUEST[ 'server' ];
$articleId = $_REQUEST[ 'articleid' ];
$revId = $_REQUEST[ 'revid' ];

require_once( "../../../LocalSettings/LoopSettings.php" );

$cookie = '/tmp/'.uniqid().'cookies.tmp';
$apiurl = $server . "/mediawiki/api.php";
$filepath = "../../../images/$server/audio/$articleId/";
$returnpath = "/mediawiki/images/$server/audio/$articleId/";

function httpRequest( $url, $params ) {
	
	global $cookie;
	$ch = curl_init();
	curl_setopt ( $ch, CURLOPT_USERAGENT, 'LOOP2 API');
	curl_setopt ( $ch, CURLOPT_POST, true );
	curl_setopt ( $ch, CURLOPT_URL, ( $url ) );
	curl_setopt ( $ch, CURLOPT_ENCODING, "UTF-8" );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt ( $ch, CURLOPT_COOKIEFILE, $cookie );
	curl_setopt ( $ch, CURLOPT_COOKIEJAR, $cookie );
	if ( ! empty( $params ) ) curl_setopt( $ch, CURLOPT_POSTFIELDS, $params );
	$return = curl_exec( $ch );
	if ( empty( $return ) ) {
		throw new Exception( "Error getting data from server ($url): " . curl_error( $ch ) );
	}
	curl_close( $ch );
	return $return;
}

function login ( $user, $pass ) {
	
	global $apiurl;
	$params_token = "action=query&meta=tokens&type=login&format=json";
	$data_token = json_decode( httpRequest( $apiurl, $params_token ), true );
	$token = urlencode($data_token['query']['tokens']['logintoken']);
	
	if ( empty ( $token )) {
		throw new Exception("Could not fetch login token");
	}
	
	$params_login = "action=login&lgtoken=$token&lgname=$user&lgpassword=$pass&format=json";
	$data_login = json_decode( httpRequest( $apiurl, $params_login ), true );
	
	if ( empty( $data_login ) ) {
		throw new Exception("No data received from server. Check that API is enabled.");
	} elseif ( $data_login["login"]["result"] != "Success"  ) {
		if ( $data_login["login"]["result"] == "Failed" ) {
			throw new Exception("Could not login to API: " . $data_login["login"]["reason"]);
		} elseif ( $data_login["login"]["result"] == "NeedToken" ) {
			throw new Exception("Could not login to API: No valid token provided. Check token and cookies.");
		} elseif ( isset( $data_login["login"]["reason"] ) ) {
			throw new Exception("Could not login to API. " . $data_login["login"]["reason"] );
		} elseif ( isset( $data_login["login"]["result"] ) ) {
			throw new Exception("Could not login to API. " . $data_login["login"]["result"] );
		} else {
			throw new Exception("Could not login to API." );
		}
	} 
}

function getAudio ( $articleData ) {
	
	global $audiourl, $filepath, $returnpath;
	
	$articleId = $articleData["articleId"];
	$text = $articleData["text"];
	$language = $articleData["language"];
	$revId = $articleData["revId"];
	
	$voiceId = rand(1,3);
	$text = urlencode(strip_tags($text) );
	
	$params_audio = "srctext=$text&language=$language&voiceid=$voiceId";
	$data_audio = httprequest( $audiourl, $params_audio );

	
	if ( !file_exists ( $filepath ) ) { // create directory if non-existent
		mkdir( $filepath, 0775, true );
		$status = "initial";
	} else {
		$fileList = preg_grep("/([\d]{1,}.mp3)/", scandir($filepath)); // list of all audio files in directory
	
		if ( ! empty( $fileList ) ){ // check contents of directory
			$oldId = substr($fileList[2], 0, -4); // removes .mp3
			if ( intval( $oldId, 10 ) < $revId ) {
				unlink($filepath.$fileList[2]);
				$status = "update";
			} else {
				$status = "reuse";
				$returnId = $oldId;
			}
		}
	}
	if ( $status == "update" || $status == "initial" ) {
		$file = fopen($filepath.$revId.'.mp3', 'w') or die("can't write mp3 file");
		fwrite($file, $data_audio);
		fclose($file);
		$returnId = $revId;
	} 
	
	$logPath = "../../../log/audiolog.txt"; // todo: if no folder
	
	$log = "$articleId, $revId, $status";
	$logfile = fopen( $logPath, 'a' ) or die( "can't write logfile" );
	fwrite( $logfile, date("Y-m-d H:i:s" )." ". $log ."\n" );
	fclose( $logfile);
	
	return $returnpath.$returnId.".mp3";
}
function fetchSsml ( $server ) {
	$url = "mediawiki/";
}
function fetchArticleContent ( $articleId ) {
	
	global $apiurl;
	
	$params_revid = "action=query&pageids=$articleId&prop=info&format=json";
	$data_revid = json_decode( httpRequest( $apiurl, $params_revid ), true );
	
	$stable_revid = $data_revid["query"]["pages"][$articleId]["lastrevid"]; 
	if ( isset (  $data_revid["query"]["pages"][$articleId]["flagged"]["stable_revid"] ) ) {
		// last stable rev id is more important than last rev id
		$stable_revid = $data_revid["query"]["pages"][$articleId]["flagged"]["stable_revid"]; # Maybe check pristine level?
	}
	
	$params_parse = "action=parse&format=json&oldid=$stable_revid&prop=text";
	$data_parse = json_decode( httpRequest( $apiurl, $params_parse ), true );
	
	$articleData = array(
		"articleId" => $articleId,
		"text" => $data_parse["parse"]["text"]["*"],
		"language" => $data_revid["query"]["pages"][$articleId]["pagelanguage"],
		"revId" => $stable_revid,
		);
	
	//var_dump ($articleData); 
	return $articleData;
	
}

login( $audiologinuser, $audiologinpw );
$articleData = fetchArticleContent ( $articleId );
$filePath = getAudio( $articleData );

echo $filePath;