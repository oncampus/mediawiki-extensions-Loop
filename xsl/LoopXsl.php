<?php 

function xsl_transform_imagepath($input) {
	$imagepath='';
	if (is_array($input)) {
		if (isset($input[0])) {
			$input_object=$input[0];
			$input_value=$input_object->textContent;
			$input_array=explode(':',$input_value);
			if (count($input_array)==2) {
				$target_uri=trim($input_array[1]);
				$filetitle=Title::newFromText( $target_uri, NS_FILE );
				$file = wfLocalFile($filetitle);

				if (is_object($file)) {
					$imagepath=$file->getFullUrl();
					#dd($file->getLocalRefPath());
					if ( file_exists($file->getLocalRefPath()) ) {
						return $imagepath;
					} else {
						return '';
					}
				} else {
					return '';
				}

			}
		}
	} else {
		#$target_uri=trim($input_array[1]);
			$filetitle=Title::newFromText( $input, NS_FILE );
			$file = wfLocalFile($filetitle);
			if (is_object($file)) {
				$imagepath=$file->getFullUrl();
				if ( file_exists($file->getLocalRefPath()) ) {
					
					return $imagepath;
				} else {
					
					return '';
				}
			} else {
				
				return '';
			}
	}
}


function xsl_transform_math($input) {
	global $IP;
	$input_object = $input[0];
	$mathcontent = $input_object->textContent;
	
	$math = new MathMathML($mathcontent);
	$math->render();
	$return = $math->getHtmlOutput();
	
	$dom = new DOMDocument;
	$dom->loadXML( $return );
	$mathnode = $dom->getElementsByTagName('math')->item(0);
	
	$doc = new DOMDocument;
	
	$old_error_handler = set_error_handler( "xsl_error_handler" );
	libxml_use_internal_errors( true );
	
	try {
		$doc->loadXML($mathnode->C14N());
		$return = $doc->documentElement;
	} catch ( Exception $e ) {
	
	}
	restore_error_handler();
	#dd($input,$doc->saveXML(),$math);
	return $return;	
	
}

function xsl_transform_math_ssml($input) {
	global $wgMathMathMLUrl;
	$input_object = $input[0];
	$mathcontent = $input_object->textContent;
	
	$math = new MathMathML($mathcontent);
	$math->render();
	$host = $wgMathMathMLUrl."speech/";
	$post = 'q=' . rawurlencode( $mathcontent );
	$math->makeRequest($host, $post, $return, $er);

	if (empty($er)) {
		return $return;	
	} else {
		return '';
	}
}

function xsl_error_handler($errno, $errstr, $errfile, $errline) {
	return true;
}


function xsl_transform_syntaxhighlight($input) {
	
	global $wgPygmentizePath, $IP, $wgScriptPath;
	
	$return = '';
	
	$input_object=$input[0];
	
	if ($input_object->hasAttribute('lang')) {
		$lexer = $input_object->getAttribute('lang');
	} else {
		$lexer = 'xml';
	}
	
	if ($input_object->hasAttribute('lang')) {
		$lang = $input_object->getAttribute('lang');
	}
	if ($input_object->hasAttribute('line')) {
		$line = $input_object->getAttribute('line');
	}
	if ($input_object->hasAttribute('start')) {
		$start = $input_object->getAttribute('start');
	}
	if ($input_object->hasAttribute('highlight')) {
		$highlight = $input_object->getAttribute('highlight');
	}
	if ($input_object->hasAttribute('inline')) {
		$inline = $input_object->getAttribute('inline');
	}	

	$dom = new DOMDocument( "1.0", "utf-8" );
	$dom->appendChild($dom->importNode($input_object, true));
	
	
	$xml = $dom->saveXML();

	$xml = str_replace('<space/>',' ',$xml);
	$xml = preg_replace("/^(\<\?xml version=\"1.0\"\ encoding=\"utf-8\"\?\>\n)/", "", $xml); 
	$xml = preg_replace("/^(<extension)(.*)(>)/", "", $xml);
	$xml = preg_replace("/(<\/extension>)$/", "", $xml);
	
	$xml = trim ($xml, " \n\r");
	$xml = htmlspecialchars_decode ($xml);
	
	$code = $xml;
	
	$symfonyPath = "$IP/extensions/Loop/vendor/symfony/process/";
	require_once $symfonyPath . "Process.php";
	require_once $symfonyPath . "ProcessUtils.php";
	require_once $symfonyPath . "Pipes/PipesInterface.php";
	require_once $symfonyPath . "Pipes/AbstractPipes.php";
	require_once $symfonyPath . "Pipes/UnixPipes.php";
	
	/*
	$code = '<syntaxhighlight lang="python" line="1" >
def quickSort(arr):
    less = []
    pivotList = []
    more = []
    if len(arr) <= 1:
        return arr
    else:
       pass
</syntaxhighlight>';
	$lexer = 'python';
*/
	$process = new Symfony\Component\Process\Process( [ $wgPygmentizePath, "-l", $lexer, "-f", "html", "-O", "encoding=utf-8", "-O", "linenos=inline", "-O", "startinline=true", "-O", "cssclass"], null, null, $code );
	$process->run();
	
	if ( !$process->isSuccessful() ) {
		dd("not successful, ".$process->getExitCode(), $process);
		$output ='';
	} else {
		$output = $process->getOutput();
	}
	#var_dump($output);
	#dd($output, $xml,$lexer, $code, $process);

	$output = '<pre>'.$output.'</pre>';
	$return = new DOMDocument;
	
	$old_error_handler = set_error_handler("xsl_error_handler");
	libxml_use_internal_errors(true);
	
	try {
		$return->loadXml($output);
	} catch ( Exception $e ) {
	
	}
	restore_error_handler();	
	

#	$return = $doc->documentElement;
	#dd($output);
	return $return;
	
}
