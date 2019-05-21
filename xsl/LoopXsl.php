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
	$dom->loadHTML( $return );
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
	#dd($input);
	return $return;	
	

}

function xsl_error_handler($errno, $errstr, $errfile, $errline) {
	return true;
}
