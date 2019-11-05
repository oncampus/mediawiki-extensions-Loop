<?php 

/**
 * @description Transforms XML to XSLT-compatible content
 * @author Dennis Krohn <dennis.krohn@th-luebeck.de>, Dustin Neß <dustin.ness@th-luebeck.de>
 */

if ( !defined( 'MEDIAWIKI' ) ) {
    die( "This file cannot be run standalone.\n" );
}

class LoopXsl {

	/**
	 * Transforms image paths into absolute server paths
	 * Called for PDF process
	 * @param DomNode $input
	 * @return String $return
	 */
    public static function xsl_transform_imagepath($input) {
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
	public static function xsl_transform_math($input) {
		global $IP;
		$input_object = $input[0];
		$mathcontent = $input_object->textContent;
		
		try {
		    $math = new MathMathML($mathcontent);
		    $math->render();
		    $return = $math->getHtmlOutput();
		} catch (Exception $e) {
		    $return = '<math></math>';
		}
		
		$dom = new DOMDocument;
		$dom->loadXML( $return );
		$mathnode = $dom->getElementsByTagName('math')->item(0);
		
		$doc = new DOMDocument;
		
		$old_error_handler = set_error_handler( "LoopXsl::xsl_error_handler" );
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
	public static function xsl_transform_math_ssml($input) {
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

	public static function xsl_error_handler($errno, $errstr, $errfile, $errline) {
		return true;
	}

	/**
	 * Transforms syntaxhighlight XML and processes it into highlighted text
	 * Called for PDF process
	 * @param DomNode $input
	 * @return DomDocument $return
	 */
	public static function xsl_transform_syntaxhighlight($input) {
		
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
			$command = array( "-l", $lexer, "-O", "linenos=inline" ); # defines lexer (language to highlight in)

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
			
			$old_error_handler = set_error_handler("LoopXsl::xsl_error_handler");
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
	public static function xsl_transform_code($input) {
		
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
	
	public static function xsl_transform_cite_ssml( $input ) {
	    global $wgLoopLiteratureCiteType;
	    
	    $input_object=$input[0];
	    $return = '';
	    $citeContent = $input_object->textContent;
	    if ( $wgLoopLiteratureCiteType == "vancouver" ) {
	        $loopStructure = new LoopStructure();
	        $loopStructure->loadStructureItems();
	        $allReferences = LoopLiteratureReference::getAllItems( $loopStructure );
	        $itemData = LoopLiteratureReference::getItemData( $input_object->getAttribute( "id" ) );
	        $id = $input_object->getAttribute( "id" );
	        $objectNumber = $allReferences[$itemData["articleId"]][$id]["objectnumber"];
	        $return .= $objectNumber;
	    } elseif ( $wgLoopLiteratureCiteType == "harvard" ) {
	        $return .= str_replace("+", " ", $citeContent);
	        if ( !empty ( $input_object->getAttribute( "page" ) ) ) {
	            $return .= ", " . wfMessage("loopliterature-text-pages-speech", 1)->text() ." ". $input_object->getAttribute( "page" ) . " ";
	        } elseif ( !empty ( $input_object->getAttribute( "pages" ) ) ) {
	            $pages =  $input_object->getAttribute( "pages" );
	            $pages = str_replace("-", " ".wfMessage("loopliterature-text-pages-to-speech")->text()." ", $pages );
	            $pages = str_replace(",", " ".wfMessage("loopliterature-text-pages-and-speech")->text()." ", $pages );
	            $return .= ", " . wfMessage("loopliterature-text-pages-speech", 2)->text() ." ". $pages . " ";
	        }
	    } else {
	        return false;
	    }
	    
	    return $return;
	}

	public static function xsl_transform_cite( $input ) {
	    global $wgLoopLiteratureCiteType;
	    
	    $input_object=$input[0];
	    $return = '';
	    $citeContent = $input_object->textContent;
	    if ( $wgLoopLiteratureCiteType == "vancouver" ) {
	        $loopStructure = new LoopStructure();
	        $loopStructure->loadStructureItems();
	        $allReferences = LoopLiteratureReference::getAllItems( $loopStructure );
	        $itemData = LoopLiteratureReference::getItemData( $input_object->getAttribute( "id" ) );
	        $id = $input_object->getAttribute( "id" );
	        $objectNumber = $allReferences[$itemData["articleId"]][$id]["objectnumber"];
	        $return .= $objectNumber;
	    }
	    return $return;
	}
	
	public static function xsl_get_bibliography( $input ) {
	    global $wgLoopLiteratureCiteType;
	    #dd($input[0]);
	    $input_object = $input[0];
		$dom = new DOMDocument( "1.0", "utf-8" );
	    
	    if ( empty ( $input_object ) ) {
	        $xml = '<bibliography>'.SpecialLoopLiterature::renderBibliography('xml')."</bibliography>";
	        $dom->loadXML($xml);
	    } else {
	        $dom->appendChild($dom->importNode($input_object, true));
	        $tags = $dom->getElementsByTagName ("extension"); 
	        $input = $tags[0]->nodeValue;
	        $xml = '<bibliography>'.LoopLiterature::renderLoopLiterature($input)."</bibliography>";
	        $dom = new DOMDocument( "1.0", "utf-8" );
	        $dom->loadXML($xml);
	    }
	    return $dom;
	}

	public static function get_page_link( $input ) {
		if ( is_string( $input ) && !empty ($input) ) {
			$articleId = str_replace( "article", "", $input );
			if ( is_numeric ( $articleId ) ) {
				global $wgCanonicalServer, $wgArticlePath;
				$title = Title::newFromId( $articleId );
				if ( isset( $title ) ) {
					$url = $wgCanonicalServer . str_replace( "$1", $title->mUrlform, $wgArticlePath );
					return $url;
				}
			}
		} 
	}

	public static function xsl_getIndex ( $input ) {

		$structure = new LoopStructure();
		$structure->loadStructureItems();
		$indexItems = LoopIndex::getAllItems( $structure, true );
		$dom = new DOMDocument( "1.0", "utf-8" );

		$root = $dom->createElement('loop_index_list');
		$root = $dom->appendChild($root);

		foreach ($indexItems as $letter => $group) {
			$loop_index_group = $dom->createElement('loop_index_group');
			$letterAttribute = $dom->createAttribute('letter');
			$letterAttribute->value = $letter;
			$loop_index_group->appendChild($letterAttribute);
			$loop_index_group = $root->appendChild($loop_index_group);
	
			foreach ($group as $indexname => $pages) {
				$loop_index_item = $dom->createElement('loop_index_item');
				$loop_index_item = $loop_index_group->appendChild($loop_index_item);
	
				$loop_index_title_value = str_replace('_', ' ', $indexname);
				$loop_index_title = $dom->createElement('loop_index_title', $loop_index_title_value);
				$loop_index_title = $loop_index_item->appendChild($loop_index_title);
	
				$loop_index_pages = $dom->createElement('loop_index_pages');
				$loop_index_pages = $loop_index_item->appendChild($loop_index_pages);
	
				$furthervalue = '0';
				foreach ($pages as $page => $refIds) {
					$loop_index_page = $dom->createElement('loop_index_page');
	
					$furtherAttribute = $dom->createAttribute('further');
					$furtherAttribute->value = $furthervalue;
					$loop_index_page->appendChild($furtherAttribute);
					
					$pagetitleAttribute = $dom->createAttribute('pagetitle');
					$pagetitleAttribute->value = "article".$page;
					$loop_index_page->appendChild($pagetitleAttribute);
	
					$loop_index_pages->appendChild($loop_index_page);
	
					$furthervalue = '1';
				}
			}
		}
		return $dom;
	}
	
	/**
	 * Returns image url of musical notes to embed in pdf
	 */
	public static function xsl_score( $input, $lang ) {
		global $wgCanonicalServer;
		
		if( count( $lang ) != 0 ) {
			$language = $lang[0]->value;
		} else {
			$language = 'lilypond';
		}
		
		$parser = new Parser();
		$html = Score::renderScore( $input[0]->textContent, ['lang' => $language], $parser );
		preg_match_all( '~<img.*?src=["\']+(.*?)["\']+~', $html, $url );
		$return = $wgCanonicalServer . $url[1][0];
		
		return $return;
	}

	public static function xsl_getSidebarPage( $input ) {

		$dom = new DOMDocument( "1.0", "utf-8" );

		if ( !empty ( $input[0]->value ) ) {
			$title = Title::newFromText( $input[0]->value );
			if ( $title->getArticleID() != 0 && $title->getNamespace() == NS_MAIN ) {
				$page = "<paragraph>".LoopXml::articleFromId2xml( $title->getArticleID(), array( "nometa" => true,  "noarticle" => true ) )."</paragraph>";
				$page = str_replace("extension_name='loop_sidebar'", "extension_name='loop_sidebar_dummy'", $page); # don't render sidebars of sidebar pages
				$dom->loadXML($page);
			}
		} 
		return $dom;
	}

	public static function xsl_toc( $article_id ) {

		$xml = '';
		
		$dom = new DOMDocument( "1.0", "utf-8" );
		$xml .= '<paragraph>';
		$xml .= LoopToc::outputLoopToc( $article_id[0], "xml" );
		$xml .= '</paragraph>';
	
		$dom->loadXML($xml);
		
		return $dom;
	}

}