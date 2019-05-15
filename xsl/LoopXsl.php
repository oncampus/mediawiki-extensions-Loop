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
	}
}

?>