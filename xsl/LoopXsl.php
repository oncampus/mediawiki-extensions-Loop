<?php 
# TODO aufräumen

function xsl_transform_link($input) {
	
	libxml_use_internal_errors(true);
	
	$input_object=$input[0];
	
	if ($input_object->hasAttribute('type')) {
		$link_parts['type']=$input_object->getAttribute('type');
	}
	if ($input_object->hasAttribute('href')) {
		$link_parts['href']=$input_object->getAttribute('href');
	}
	
	$link_childs=$input_object->childNodes;
	$num_childs=$link_childs->length;
	
	for ($i = 0; $i < $num_childs; $i++) {
		$child=$link_childs->item($i);
		if (isset($child->tagName)) {
			$child_name=$child->tagName;
			if ($child_name=='') {$child_name='text';}
			$child_value=$child->textContent;
		} else {
			$child_name='text';
			$child_value=$child->textContent;
		}
	
		if ($child_name == 'part') {
			if (substr($child_value, -2) == 'px') {
				$child_name = 'width';
				$child_value = substr($child_value,0,-2);
			} elseif (($child_value == 'right') || ($child_value == 'left') || ($child_value == 'center')) {
				$child_name = 'align';
	
			}
		}
		$link_parts[$child_name]=$child_value;
	}	
	if (!array_key_exists('type', $link_parts)) {
		$link_parts['type']='internal';
	}
	if (array_key_exists('text', $link_parts)) {
		$link_parts['text']=htmlspecialchars($link_parts['text']);
	}	
	
	$return_xml = '';
	
	if ($link_parts['type']=='external') {
		$return_xml =  '<php_link_external href="'.$link_parts['href'].'">'.$link_parts['text'].'</php_link_external>' ;
	} else {
		if (isset($link_parts['target'])) {
			$target_title = Title::newFromText($link_parts['target']);
			$target_ns = $target_title->getNamespace();
		
			if ($target_ns == NS_FILE) {
				$file = wfLocalFile($target_title);
				if (is_object($file)) {
					$target_file=$file->getLocalRefPath();
					$target_url=$file->getFullUrl();
					if (is_file($target_file)) {
						$allowed_extensions = array('jpg','jpeg','gif','png','svg','tiff','bmp','eps','wmf','cgm');
						if (in_array($file->getExtension(), $allowed_extensions)) {
							
							if (array_key_exists('width', $link_parts)) {
								$width=0.214*intval($link_parts['width']);
								if ($width>150) {
									$imagewidth='150mm';
								} else {
									$imagewidth=round($width,0).'mm';
								}
							} else {
								$size=getimagesize($target_file);
								$width=0.214*intval($size[0]);
								if ($width>150) {
									$imagewidth='150mm';
								} else {
									$imagewidth=round($width,0).'mm';
								}
							}							
							
							$return_xml =  '<php_link_image imagepath="'.$target_url.'" imagewidth="'.$imagewidth.'" ';
							if (isset($link_parts['align'])) {
								$return_xml .= ' align="'.$link_parts['align'].'" ';
							}
							$return_xml .=  '></php_link_image>';							
							
							
						}
					}
				} 
			} elseif ($target_ns == NS_CATEGORY) {
				// Kategorie-Link nicht ausgeben
				
			} else {
				// internal link
				if (!array_key_exists('text', $link_parts)) {
					if(array_key_exists('part',$link_parts)) {
						$link_parts['text']=$link_parts['part'];
					} else {
						$link_parts['text']=$target;
					}
				}
				if (!array_key_exists('href', $link_parts)) {
					$link_parts['href']=$target;
				}
				$return_xml =  '<php_link_internal href="'.$link_parts['href'].'">'.$link_parts['text'].'</php_link_internal>' ;
			}
		}	
	}
	$return = new DOMDocument;

	$old_error_handler = set_error_handler("xsl_error_handler");
	libxml_use_internal_errors(true);
	
	try {
		$return->loadXml($return_xml);
	} catch ( Exception $e ) {
	
	}
	restore_error_handler();
	
	
	return $return;	
	
	
}

function xsl_error_handler($errno, $errstr, $errfile, $errline)
{
	return true;
}

function xsl_transform_math($input) {
	global $IP;
	$input_object=$input[0];
	$mathcontent=$input_object->textContent;
	
	$math = new MathMathML($mathcontent);
	$math->render();
	$return = $math->getHtmlOutput();
	
	$dom = new DOMDocument;
	$dom->loadHTML($return);
	$mathnode = $dom->getElementsByTagName('math')->item(0);
	
	#return $mathnode->documentElement;
	
	$doc = new DOMDocument;
	
	$old_error_handler = set_error_handler("xsl_error_handler");
	libxml_use_internal_errors(true);
	
	try {
		$doc->loadXML($mathnode->C14N());
		#$doc->appendChild($mathnode);
		$return = $doc->documentElement;
	} catch ( Exception $e ) {
	
	}
	restore_error_handler();
	
	return $return;	
	

}

function xsl_transform_screenshot($input) {
	global $wgServer, $wgUploadPath, $wgUploadDirectory;
	
	$input_object=$input[0];
	$screenshot_id = $input_object->getAttribute('id');
	
	$target_file = $wgUploadDirectory.'/screenshots/'.$screenshot_id.'.png';
	$target_url = $wgServer.$wgUploadPath.'/screenshots/'.$screenshot_id.'.png';
	
	$size=getimagesize($target_file);
	$width=0.214*intval($size[0]);
	if ($width>150) {
		$imagewidth='150mm';
	} else {
		$imagewidth=round($width,0).'mm';
	}	
	
	
	$return_xml =  '<php_link_image imagepath="'.$target_url.'" imagewidth="'.$imagewidth.'" ';
	$return_xml .=  '></php_link_image>';
	
	$return = new DOMDocument;
	
	$old_error_handler = set_error_handler("xsl_error_handler");
	libxml_use_internal_errors(true);
	
	try {
		$return->loadXml($return_xml);
	} catch ( Exception $e ) {
	
	}
	restore_error_handler();
	
	
	return $return;	
}


function xsl_transform_loop_figure($input) {
	$input_object=$input[0];
	#wfDebug("\n\nyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyy".__METHOD__."input_object:".print_r($input_object,true)."\n\nyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyy\n");
	
	
	$loop_figure_title = '';
	$loop_figure_description = '';
	
	$figure = new LoopFigure;
	
	$dom = new DOMDocument;
	$dom->appendChild($dom->importNode($input_object, true));
	
	$loop_figure_title = $dom->documentElement->getAttribute('title');

	$xpath = new DOMXPath( $dom );
	$loop_title_extension_list = $xpath->query( "//extension[@extension_name='loop_title']" );
	if ($loop_title_extension_list) {
		$loop_title_extension = $loop_title_extension_list->item(0);
		$loop_figure_title2 = $loop_title_extension->textContent;
	}
	
	
	
	#$ad->getAttribute('href');
	
	#$dom->loadXML("<loop_figure></loop_figure>");
	#$node = $dom->importNode($input_object, true);
	#$dom->documentElement->appendChild($node);
	
	#$xpath = new DOMXPath( $dom );
	#$extension_loop_figure = $xpath->query( "extension[@extension_name='loop_figure']" );
	
	
	#$loop_figure_title = $extension_loop_figure->item(0)->attributes;
	#$loop_figure_title = $dom->saveXML();
	/*
	if ($input_object->hasAttribute('title')) {
		$figure->setTitle($input_object->getAttribute('title'));
	}
	*/
	#$loop_figure_title = $dom->saveXML();
	
	#wfDebug("\n\nyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyy".__METHOD__."loop_figure_title:".print_r($loop_figure_title,true)."\n\nyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyy\n");
	#wfDebug("\n\nyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyy".__METHOD__."loop_figure_title2:".print_r($loop_figure_title2,true)."\n\nyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyy\n");
	
	
	
	$xml = '<test></test>';
	$return = new DOMDocument;
	
	$old_error_handler = set_error_handler("xsl_error_handler");
	libxml_use_internal_errors(true);
	
	try {
		$return->loadXml($return_xml);
	} catch ( Exception $e ) {
	
	}
	restore_error_handler();
	
	
	return $return;
}


function xsl_transform_imagepath($input) {
	$imagepath='';
	if (is_array($input)) {
		if (isset($input[0])) {
			$input_object=$input[0];
			$input_value=$input_object->textContent; # TODO errorlog
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

function xsl_transform_syntaxhighlight($input) {
	
	
	global $wgPygmentizePath;
	
	$return = '';
	
	$input_object=$input[0];
	
	if ($input_object->hasAttribute('lang')) {
		$lexer = $input_object->getAttribute('lang');
	} else {
		$lexer = 'xml';
	}
	
#	var_dump($lexer);
	
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

	
	
	#$return=$lexer;
	
	#	$xml = $input_object->ownerDocument->saveXML();
	#$xml = $input_object->C14N();
	
	$dom = new DOMDocument;
	$dom->appendChild($dom->importNode($input_object, true));
	
	
	
	
	$xml = $dom->saveXML();
	
	#var_dump($xml);exit;
	
	$xml = str_replace('<space/>',' ',$xml);
	
	/*
	$xml = str_replace('<paragraph>','',$xml);
	$xml = str_replace('</paragraph>',"\n",$xml);
	$xml = str_replace('<preblock>','',$xml);
	$xml = str_replace('</preblock>','',$xml);
	$xml = str_replace('<preline>','',$xml);
	$xml = str_replace('</preline>',"\n",$xml);
	*/
	
	$xml = preg_replace("/^(\<\?xml version=\"1.0\"\?\>\n)/", "", $xml); 
	$xml = preg_replace("/^(<extension)(.*)(>)/", "", $xml);
	$xml = preg_replace("/(<\/extension>)$/", "", $xml);
	
	#$xml = html_entity_decode ($xml);
	#$xml = htmlentities($xml);
	
	$xml = trim ($xml, " \n\r");
	
	$xml = htmlspecialchars_decode ($xml);
	
	$code= $xml;
	
#	if (stristr($xml, 'php')) {
#		var_dump($xml);exit;
#	}
	
	#$code=$input_object->textContent;
	#$return = $code;
	
	#var_dump($code);exit;
	
	/*
	$builder = new ProcessBuilder();
	$builder->setPrefix( $wgPygmentizePath );
	$process = $builder
	->add( '-l' )->add( $lexer )
	->add( '-f' )->add( 'svg' )
	->getProcess();
	
	$process->setInput( $code );
	$process->run();
	
	if ( !$process->isSuccessful() ) {
		$output ='';
	} else {
		$output = $process->getOutput();
	}
	
	$return=$output;
	*/
	
	/*
	libxml_use_internal_errors(true);
	
	$input_object=$input[0];
	
	if ($input_object->hasAttribute('lang')) {
		$lexer = $input_object->getAttribute('lang');
	} else {
		$lexer = 'xml';
	}

	
	$code=$input_object->textContent;
	
	
	$builder = new ProcessBuilder();
	$builder->setPrefix( $wgPygmentizePath );
	$process = $builder
	->add( '-l' )->add( $lexer )
	->add( '-f' )->add( 'svg' )
	->getProcess();
	
	$process->setInput( $code );
	$process->run();
	
	if ( !$process->isSuccessful() ) {
		$output ='';
	} else {
		$output = $process->getOutput();	
	}
	
	
	$dom = new DOMDocument;
	$dom->loadXML($output);
	#$mathnode = $dom->getElementsByTagName('math')->item(0);
	
	#return $mathnode->documentElement;
	
	$doc = new DOMDocument;
	$return = $doc->documentElement;
	
	$old_error_handler = set_error_handler("xsl_error_handler");
	libxml_use_internal_errors(true);
	
	restore_error_handler();
	*/
	#global $wgPygmentizePath;
	
	require_once 'extensions/SyntaxHighlight_GeSHi/vendor/symfony/process/ProcessBuilder.php';
	
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

	$options = array(
			#'nowrap' => 1,
			'encoding' => 'utf-8',
			'linenos' => 'inline'
	);	
	
	
	$optionPairs = array();
	foreach ( $options as $k => $v ) {
		$optionPairs[] = "{$k}={$v}";
	}
	
	$builder = new Symfony\Component\Process\ProcessBuilder();
	$builder->setPrefix( $wgPygmentizePath );
	$process = $builder
	->add( '-l' )->add( $lexer )
	->add( '-f' )->add( 'html' )
	->add( '-O' )->add( implode( ',', $optionPairs ) )
	->getProcess();
	
	#->add( '-O' )->add( 'nowrap, linenos=inline' )	
	
	
	$process->setInput( $code );
	$process->run();
	
	if ( !$process->isSuccessful() ) {
		$output ='';
	} else {
		$output = $process->getOutput();
	}
	


	#$output = preg_replace("/(?<=span>)(\s)+(?=<span)/", '<span class="nbsp">$1</span>', $output);
	$output = '<pre>'.$output.'</pre>';
	
/*	
	if (stristr($xml, 'php')) {
	
		var_dump($xml);
		var_dump($output);
		exit;
	}	
*/	
	
	#var_dump($output);exit;
	 
	$return = new DOMDocument;
	
	$old_error_handler = set_error_handler("xsl_error_handler");
	libxml_use_internal_errors(true);
	
	try {
		$return->loadXml($output);
	} catch ( Exception $e ) {
	
	}
	restore_error_handler();	
	
	
	#$return = $output;
	
	#var_dump($output);exit;
	/*
	$dom = new DOMDocument;
	$dom->loadXML($output);
	$svgnode = $dom->getElementsByTagName('svg')->item(0);
	
	$doc = new DOMDocument;
	
	$old_error_handler = set_error_handler("xsl_error_handler");
	libxml_use_internal_errors(true);
	
	try {
		$doc->loadXML($svgnode->C14N());
		
		$return = $doc->documentElement;
	} catch ( Exception $e ) {
	
	}
	restore_error_handler();	
	*/
	
	
	
	#$return = $svgnode->C14N();
/*	
	$dom = new DOMDocument;
	$dom->loadXML($output);
	$svgnode = $dom->getElementsByTagName('svg')->item(0);
	
	$return=$svgnode;
	*/
	/*
	$doc = new DOMDocument;
	
	$old_error_handler = set_error_handler("xsl_error_handler");
	libxml_use_internal_errors(true);
	
	try {
		$doc->loadXML($svgnode->C14N());
		#$doc->appendChild($mathnode);
		$return = $doc->documentElement;
	} catch ( Exception $e ) {
	
	}
	restore_error_handler();
	*/
	
#	$dom = new DOMDocument;
#	$dom->loadXML($output);
#	$return = $doc->documentElement;
	
	return $return;
	
}


function xsl_transform_reference($input) {

	$input_object=$input[0];

	
	$args = array();
	if ($input_object->hasAttribute('reference')) {
		$args['reference'] = $input_object->getAttribute('reference');
	} 
	if ($input_object->hasAttribute('page')) {
		$args['page'] = $input_object->getAttribute('page');
	}
	if ($input_object->hasAttribute('render')) {
		$args['render'] = $input_object->getAttribute('render');
	}	
	
	try {
		$reference = LoopReference::newFromTag('', $args);
		$reference->parse();
	
		if ($reference->mRefernceId) {
			$href='object'.$reference->mRefernceId;
		} else {
			$href='article'.$reference->mStructureItemId;
		}
			
		
		$return_xml =  '<php_link_internal href="'.$href.'">'.$reference->mLinkText.'</php_link_internal>' ;
		
	} catch ( LoopException $e ) {
		
		$return_xml = '';
	}
		
	
	
	
	$return = new DOMDocument;
	
	$old_error_handler = set_error_handler("xsl_error_handler");
	libxml_use_internal_errors(true);
	
	try {
		$return->loadXml($return_xml);
	} catch ( Exception $e ) {
	
	}
	restore_error_handler();
	
	
	return $return;
	
	
}



?>