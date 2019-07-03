<?php 

/**
 * Transforms image paths into absolute server paths
 * Called for PDF process
 * @param DomNode $input
 * @return String $return
 */
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


/**
 * Transforms math tag into math text
 * Called for PDF process
 * @param DomNode $input
 * @return String $return
 */
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

/**
 * Transforms math tag into spoken text
 * Called for Audio process
 * @param DomNode $input
 * @return String $return
 */
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

/**
 * Transforms syntaxhighlight XML and processes it into highlighted text
 * Called for PDF process
 * @param DomNode $input
 * @return DomDocument $return
 */
function xsl_transform_syntaxhighlight($input) {
	
	global $wgPygmentizePath, $IP, $wgScriptPath;
	
	$symfonyPath = "$IP/extensions/Loop/vendor/symfony/process/";

	if ( is_file( $symfonyPath . "Process.php" ) ) {
	
		$return = '';
		$input_object=$input[0];
		
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
	
		require_once $symfonyPath . "Process.php";
		require_once $symfonyPath . "ProcessUtils.php";
		require_once $symfonyPath . "Pipes/PipesInterface.php";
		require_once $symfonyPath . "Pipes/AbstractPipes.php";
		require_once $symfonyPath . "Pipes/UnixPipes.php";
		
		
		if ($input_object->hasAttribute('lang')) {
			$lexer = $input_object->getAttribute('lang');
			$lang = $input_object->getAttribute('lang');
		} else {
			$lexer = 'xml';
		}
		# doc for command: http://pygments.org/docs/formatters/#HtmlFormatter
		$command = array( "-l", $lexer ); # defines lexer (language to highlight in)

		# we ignore the 'inline' attribute as we need to have line breaks on paper

		/* 	# as standard, line numbers should be shown! 
			# because line-breaking is mandatory, line numbers indicate actual new lines so users can tell the difference
		if ($input_object->hasAttribute('line')) {
			$line = $input_object->getAttribute('line');
			$command[] = "-O";
			$command[] = "linenos=inline";
		} */
		if ($input_object->hasAttribute('start') ) { # defines the start option of line numbering
			$start = $input_object->getAttribute('start');
			$command[] = "-O";
			$command[] = "linenostart=" . $start;
		}
		if ($input_object->hasAttribute('highlight')) { # highlights given lines
			$highlight = $input_object->getAttribute('highlight');
			$command[] = "-O";
			$command[] = "hl_lines=$highlight";
		}

		$command = array_merge ( [ $wgPygmentizePath, "-f", "html", "-O", "encoding=utf-8", "-O", "cssclass", "-O", "startinline=true" ], $command );

		$process = new Symfony\Component\Process\Process( $command, null, null, $code );
		$process->run();
		
		if ( !$process->isSuccessful() ) {
			$output ='';
		} else {
			$output = $process->getOutput();
		}
		#var_dump($output); dd($output, $xml,$lexer, $code, $process);

		$output = '<pre>'.$output.'</pre>';
		$return = new DOMDocument;
		
		$old_error_handler = set_error_handler("xsl_error_handler");
		libxml_use_internal_errors(true);
		
		try {
			$return->loadXml($output);
		} catch ( Exception $e ) {
		
		}
		restore_error_handler();	

		return $return;
	} else {
		# if symfony/process is not present, just return the input node
		return $input[0];
	}
	
}

/**
 * Adds linebreaks to a Domnode code tag
 * Called for PDF process
 * @param DomNode $input
 * @return DomNode $codeTag
 */
function xsl_transform_code($input) {
	
	$input_object=$input[0];
	
	$dom = new DOMDocument( "1.0", "utf-8" );
	$dom->appendChild($dom->importNode($input_object, true));
	
	
	$xml = $dom->saveXML();
	$xml = str_replace('<space/>', ' ',$xml);
	$xml = preg_replace("/(\s\\t)/","\n\t", $xml);
	
	$dom2 = new DOMDocument( "1.0", "utf-8" );
	$dom2->loadXML($xml);
	$codeTags = $dom2->getElementsByTagNameNS ("http://www.w3.org/1999/xhtml", "code"); # finds <xhtml:code> tags
	$codeTag = $codeTags[0];

	return $codeTag;

}
