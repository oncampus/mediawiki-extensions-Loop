<?php
/**
  * @description Exports LOOP to XML
  * @author Dennis Krohn <dennis.krohn@th-luebeck.de>
  */

if ( !defined( 'MEDIAWIKI' ) ) die ( "This file cannot be run standalone.\n" );

use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\SlotRecord;

class LoopXml {

	/**
	 * @param LoopStructure $loopStructure:
	 * @param Array $modifiers:
	 * 		"mp3" => true; modifies XML Output for MP3 export, adds additional breaks for loop_objects
	 */
	public static function structure2xml(LoopStructure $loopStructure, Array $modifiers = []) {
		global $wgCanonicalServer, $wgLanguageCode;

		set_time_limit(601);
		ini_set('memory_limit', '1024M');

		$loopStructureItems = $loopStructure->getStructureItems();

		$langParts = mb_split("-",$wgLanguageCode);

		$xml = "<?xml version='1.0' encoding='UTF-8' ?>\n";
		$xml.= "<loop ";
		$xml.= "xmlns:xhtml=\"http://www.w3.org/1999/xhtml\" ";
		$xml.= ">";


		$xml .= "<meta>\n";
		$xml .= "\t<title>".htmlspecialchars ($loopStructure->getTitle())."</title>\n";
		$xml .= "\t<url>".$wgCanonicalServer."</url>\n";
		$xml .= "\t<date_generated>".date('Y-m-d H:i:s')."</date_generated>\n";
		$xml .= "\t<date_last_changed>".date('Y-m-d H:i:s',strtotime($loopStructure->lastChanged()))."</date_last_changed>\n";
		$xml .= "\t<lang>".$langParts[0]."</lang>\n";
		$xml .= "</meta>\n";


		$toc = self::structureItemToc($loopStructureItems[0]);
		$xml .= "<toc>".$toc."</toc>\n";

		$articles = '<articles xmlns:xhtml="http://www.w3.org/1999/xhtml">';
		foreach ( $loopStructureItems as $loopStructureItem ) {
		    $articles .= self::structureItem2xml ( $loopStructureItem, $modifiers );
		}
		$articles .= "</articles>\n";

		$xml .= self::handleDublicateIds( $articles ); # double ids of various items are eliminated

		$xml .= "<loop_objects>\n";

		$xml .= self::objectsTable2xml ( $loopStructureItems, $loopStructure );

		$xml .= "</loop_objects>\n";


		$xml .= "<glossary>\n";

		$xml .= self::glossary2xml ();

		$xml .= "</glossary>\n";

		$xml .= self::bibliography2xml ();

		$xml .= self::terminology2xml ();

		$xml .= "</loop>";

		return $xml;
	}

	public static function objectsTable2xml ( $loopStructureItems, $loopStructure ) {

	    $xml = '';

	    $objects = LoopObjectIndex::getAllObjects( $loopStructure );

	    foreach ( $objects as $object ) {

	        $xml .= "<loop_object object_type='".$object["index"]."' articleid='".$object["pageid"]."' n='".$object["nthoftype"]."' refid='".$object["id"]."'>\n";
	        if ( ! empty($object["objectnumber"]) ) {   $xml .= "<object_number>".$object["objectnumber"]."</object_number>\n"; }
	        if ( ! empty($object["type"]) ) {   $xml .= "<object_media_type>".$object["type"]."</object_media_type>\n"; }

	        $xml .= "<object_title>".$object["title"]."</object_title>\n";
	        $xml .= "<object_description>".$object["description"]."</object_description>\n";
	        $xml .= "</loop_object>\n";
	    }
	    return $xml;
	}

	/**
	 * Converts structure items to XML code
	 * @param LoopStructureItem $structureItem:
	 * @param Array $modifiers:
	 * 		"mp3" => true; modifies XML Output for MP3 export, adds additional breaks for loop_objects
	 */
	public static function structureItem2xml(LoopStructureItem $structureItem, Array $modifiers = []) {

		$title = Title::newFromId( $structureItem->getArticle () );
		$fwp = new FlaggableWikiPage ( $title );
		$stableRev = $fwp->getStable();
		if ( $stableRev == 0 ) {
			$stableRev = intval($title->mArticleID);
			$wp = MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle ( $title );
			$content = $wp->getContent( MediaWiki\Revision\RevisionRecord::RAW );
			$contentText = ContentHandler::getContentText( $content );

		} else {
			$revStore = MediaWikiServices::getInstance()->getRevisionStore();
			$revision = $revStore->getRevisionById( $stableRev );

			$pageContent = $revision->getContent( SlotRecord::MAIN );
			$contentText = ContentHandler::getContentText( $pageContent );
		}
		$content = html_entity_decode($contentText);
		$objectTypes = LoopObject::$mObjectTypes;

		# modify content for resolving space issues with syntaxhighlight in pdf
		$content = preg_replace('/(<syntaxhighlight.*)(>)(.*)(<\/syntaxhighlight>)/iU', "$1$2$3\n$4", $content);

		# math tags are not recognized if there is no space before closing
		$content = preg_replace('/(<\/math)/iU', "\\, $1", $content);

		# remove html comments - these cause the whole page to vanish from XML and PDF
		$content = preg_replace('/(<!--.*-->)/msiU', "", $content);

		# remove loop comments - these may cause the whole page to vanish from XML and PDF
		$content = preg_replace('/(<loop_comment.*>)(.*)(<\/loop_comment>)/msiU', "", $content);
		
		# remove table headlines - these make the process crash otherwise!
		$content = substr_replace($content, '', strpos($content, '|+'), (strpos($content, '|-', strpos($content, '|+')) - strpos($content, '|+')));

		# remove score tags - these may cause the whole XML to fail
		$content = preg_replace('/(<score.*>)(.*)(<\/score>)/msiU', "<score></score>", $content);

		# modify content for mp3 export
		if ( array_key_exists( "mp3", $modifiers ) && $modifiers["mp3"] ) {
			foreach( $objectTypes as $type ) {
				$content = preg_replace('/(<'.$type.')/', "\n<".$type, $content);
				$content = preg_replace('/(<\/'.$type.'>)/', "</".$type.">\n", $content);
			}
		}

		$wiki2xml = new wiki2xml ();
		$xml = "<article ";
		$xml .= "id=\"article" . $structureItem->getArticle() . "\" ";
		$xml .= 'toclevel="'.$structureItem->getTocLevel().'" ';
		$xml .= 'tocnumber="'.$structureItem->getTocNumber().'" ';
		$xml .= 'toctext="'.htmlspecialchars($structureItem->getTocText(), ENT_XML1 | ENT_COMPAT, 'UTF-8').'" ';
		$xml .= ">\n";
		$xml .= $wiki2xml->parse ( $content );
		if ($title->getArticleID() == 248) { # debug for specific pages
			#dd( $content, $xml);
		}
		$xml .= "\n</article>\n";

		return $xml;
	}

	/**
	 * - Removes non-unique IDs from elements from their second occurence.
	 * - Removes IDs from content such as H5P which can occur several times in a single LOOP
	 * Elements with dublicate IDs cause severe problems in PDF
	 * @param String $contentText
	 */
	public static function handleDublicateIds( $contentText ) {

		$idCache = array();
		$objectTags = array(  );
		$dom = new DOMDocument( "1.0", "utf-8" );
		$objectTags = array( 'loop_figure', 'loop_formula', 'loop_listing', 'loop_media', 'loop_table', 'loop_task', 'cite', 'loop_index' ); # all tags with ids
		$contentTags = array( 'h5p', 'learningapp', 'padlet','taskcard', 'prezi', 'slideshare', 'quizlet', 'youtube' );
		$xml = $contentText;
		$dom->loadXml($xml);
		$selector = new DOMXPath( $dom );
		$nodes = $selector->query( '//extension' );

		foreach ( $nodes as $node ) {

			if ( in_array( $node->getAttribute("extension_name"), $objectTags ) ) {

				if ( !empty( $node->getAttribute("id") )) {
					if ( ! in_array( $node->getAttribute("id"), $idCache ) )  {
						$idCache[] = $node->getAttribute("id");
					} else {
						$node->removeAttribute("id");
					}
				}

			} elseif ( in_array( $node->getAttribute("extension_name"), $contentTags ) ) {
				# rename id tags for dublicate id reasons in pdf
				$id = "";
				$id = $node->getAttribute("id");
				$node->removeAttribute("id");
				$node->setAttribute("content-id", $id);

			}
		}
		$newContentText = preg_replace("/^(\<\?xml version=\"1.0\"\ encoding=\"utf-8\"\?\>\n)/", "", $dom->saveXML());
		$newContentText = preg_replace("/^(\<\?xml version=\"1.0\"\\?\>\n)/", "", $newContentText);

		if ( empty( $newContentText ) ) {
			echo "<script>console.log('Articles XML Invalid');</script>"; # when the given XML is invalid, no domdocument doesn't load it. this is a hidden error message
			return false;
			$contentText = $newContentText;
		} elseif ( $contentText != $newContentText ) {
			$contentText = $newContentText;
		}

		return $contentText;
	}


	/**
	 * Converts article to XML code
	 * @param Int $articleId:
	 * @param Array $modifiers:
	 * 		"nometa" => true; removes <meta>-tag for pdf
	 * 		"noarticle" => true; removes <article>-tag wrapper for sidebar in pdf
	 */
	public static function articleFromId2xml( $articleId, $modifiers = [] ) {

		global $wgLanguageCode;
		$langParts = mb_split("-", $wgLanguageCode);

		$title = Title::newFromId($articleId);
		$fwp = new FlaggableWikiPage ( $title );
		$stableRev = $fwp->getStable();
		if ( $stableRev == 0 ) {
			$stableRev = intval($articleId);
			$wp = MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle ( $title );
			$pageContent = $wp->getContent( MediaWiki\Revision\RevisionRecord::RAW );
			$contentText = ContentHandler::getContentText( $pageContent );
		} else {
			$revStore = MediaWikiServices::getInstance()->getRevisionStore();
			$revision = $revStore->getRevisionById( $stableRev );

			$pageContent = $revision->getContent( SlotRecord::MAIN );
			$contentText = ContentHandler::getContentText( $pageContent );
		}
		$content = html_entity_decode($contentText);

		$wiki2xml = new wiki2xml ();
		$xml = "";
		if ( isset( $modifiers["noarticle"] ) ) {
			if ( ! $modifiers["noarticle"] ) {
				$xml = "<article ";
				$xml .= "id=\"article" . $articleId . "\" ";
				$xml .= "title=\"" . htmlspecialchars($title->getText(), ENT_XML1 | ENT_COMPAT, 'UTF-8') . "\"";
				$xml .= ">\n";
			}
		}

		if ( isset( $modifiers["nometa"] ) ) {
			if ( ! $modifiers["nometa"] ) {
				$xml .= "<meta>\n";
				$xml .= "\t<lang>".$langParts[0]."</lang>\n";
				$xml .= "</meta>\n";
			}
		}

		$xml .= $wiki2xml->parse ( $content );


		if ( isset( $modifiers["noarticle"] ) ) {
			if ( ! $modifiers["noarticle"] ) {
				$xml .= "\n</article>\n";
			}
		}
		return $xml;
	}


	private static function structureItemToc(LoopStructureItem $structureItem) {

		$toc_xml = "<chapter>";

		$childs = $structureItem->getDirectChildItems();
		foreach ($childs as $child) {

			$toc_xml .= "\n<page ";
			$toc_xml .= 'id="article'.$child->getArticle().'" ';
			$toc_xml .= 'toclevel="'.$child->getTocLevel().'" ';
			$toc_xml .= 'tocnumber="'.$child->getTocNumber().'" ';
			#$toc_xml .= 'toctext="'.htmlspecialchars($child->getTocText()).'" ';

			$toc_xml .= 'toctext="'.htmlspecialchars($child->getTocText(), ENT_XML1 | ENT_COMPAT, 'UTF-8').'" ';
			$toc_xml .= ">";

			if ($subchilds = $child->getDirectChildItems()) {
				$toc_xml .= self::structureItemToc($child);
			}

			$toc_xml .= "</page>";

		}
		$toc_xml .= "</chapter>";

		if ($structureItem->getTocLevel() == 0) {
			$toc_xml = "<chapter>\n<page id=\"article".$structureItem->getArticle()."\" toclevel=\"".$structureItem->getTocLevel()."\" tocnumber=\"".$structureItem->getTocNumber()."\" toctext=\"".htmlspecialchars($structureItem->getTocText(), ENT_XML1 | ENT_COMPAT, 'UTF-8')."\" >".$toc_xml."</page></chapter>";
		}

		return $toc_xml;
	}



	public static function error_handler($errno, $errstr, $errfile, $errline)
	{
		return true;
	}

	public static function transform_link( $input, $id = null ) {

		global $wgLang;

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
				$link_parts[$child_name] = $child_value;
			} else {
				$child_name='text';
				$child_value=$child->textContent;
				$link_parts[$child_name] = $child_value;
			}
			$hookContainer = MediaWikiServices::getInstance()->getHookContainer();
			$mf = new MagicWordFactory( $wgLang, $hookContainer );
			$allowedAligns = [ 'right', 'left', 'center' ];
			$allowedFormats = [ 'thumb', 'framed', 'frameless' ];

			if ($child_name == 'part') {
				$part_childs = $child->childNodes;
				$num_part_childs = $part_childs->length;

				for ($j = 0; $j < $num_part_childs; $j++) {
					$part_child = $part_childs->item( $j );
					if ( isset( $part_child->tagName ) && $part_child->tagName == "extension" ) {
						$part_child->nodeValue = ""; # don't allow math or extension rendering in link names
						$child_value = $child->textContent;
						$link_parts[$child_name] = $child_value;
					}
				}

				if (substr($child_value, -2) == 'px') {
					$child_value_width = substr($child_value,0,-2);
					$link_parts['width'] = $child_value_width;
				}

				foreach( $allowedAligns as $al ) {
					if( $mf->get( $al )->match( $child_value ) ) { // if align
						if( in_array( $mf->get( $al )->getSynonym( 1 ), $allowedAligns ) ) {
							$child_value_align = $mf->get( $al )->getSynonym( 1 );
						} else {
							$child_value_align = 'none';
						}

						$link_parts['align'] = $child_value_align;
					}
				}

				foreach( $allowedFormats as $fo ) {
					if( $mf->get( $fo )->match( $child_value ) ) { // if format
						if( in_array( $mf->get( $fo )->getSynonym( 1 ), $allowedFormats ) ) {
							$child_value_format = $mf->get( $fo )->getSynonym( 1 );
						} else {
							$child_value_format = 'none';
						}

						$link_parts['format'] = $child_value_format;
					}
				}
			}
		}

		if (!array_key_exists('type', $link_parts)) {
			$link_parts['type']='internal';
		}
		if (array_key_exists('text', $link_parts)) {
			$link_parts['text']=htmlspecialchars($link_parts['text']);
		}

		$return_xml = '';

		if ( $link_parts['type'] == 'external' ) {
			if ( array_key_exists( "href", $link_parts ) ) {
				$return_xml = '<php_link_external href="' . $link_parts['href'] . '">';
				$return_xml .= ( array_key_exists( "text", $link_parts ) ) ? $link_parts['text'] : " ";
				$return_xml .= '</php_link_external>';
			}
		} else {
			if ( isset( $link_parts['target'] ) ) {
				$target_title = Title::newFromText( $link_parts['target'] );
				if ( is_object( $target_title ) ) {

					$target_ns = $target_title->getNamespace();

					if ( $target_ns == NS_FILE ) {
						$file = MediaWikiServices::getInstance()->getRepoGroup()->getLocalRepo()->newFile( $target_title );
						if ( is_object( $file ) ) {
							$target_file = $file->getLocalRefPath();
							$target_url = $file->getFullUrl();
							if ( is_file( $target_file ) ) {
								$allowed_extensions = array( 'jpg','jpeg','gif','png','svg','tiff','bmp','eps','wmf','cgm' );
								if ( in_array( $file->getExtension(), $allowed_extensions ) ) {

									$issvg = false;
									if ( $file->getExtension() === 'svg' ) {
										$issvg = true;
										$iconfile = new DOMDocument();
										$iconfile->load( $file->getLocalRefPath() );
										$svg = $iconfile->getElementsByTagName( 'svg' )[0];
										$viewbox = $svg->getAttribute( 'viewBox' );
										if ( $viewbox ) {
											$svgheight = explode( ' ', $viewbox )[3];
											$svgwidth = explode( ' ', $viewbox )[2];
										} else {
											$svgheight = $svg->getAttribute( 'height' );
											$svgwidth = $svg->getAttribute( 'width' );
										}
										if ( $svgwidth && $svgheight && intval( $svgwidth ) > 0 ) {
											$resolutionscale = $svgheight / $svgwidth;
										}
									}

									if ( array_key_exists( 'width', $link_parts ) ) {
										$width = 0.214 * intval( $link_parts['width'] );
									} else {
										$size = getimagesize( $target_file );
										$width = 0.214 * intval( $size[0] );
									}

									if ( $width > 150 ) {
										$width = 150;
										$imagewidth = '150mm';
									} else {
										$imagewidth = round( $width ) . 'mm';
									}

									$return_xml =  '<php_link_image imagepath="' . $target_url . '" imagewidth="' . $imagewidth . '"';
									if ( $issvg && isset( $resolutionscale ) ) {
										$imageheight = round( $width * $resolutionscale ) . 'mm';
										$return_xml .= ' imageheight="' . $imageheight . '"';
									}

									if ( isset( $link_parts['align'] ) ) {
										$return_xml .= ' align="' . $link_parts['align'] . '" ';
									}
									if ( isset( $link_parts['format'] ) ) {
										$return_xml .= ' format="' . $link_parts['format'] . '" ';
									}
									$return_xml .= '></php_link_image>';


								} elseif ( $file->getMediaType() == "VIDEO" ) { #render videos entered as [[File:Video.mp4]] like loop_video
									$return_xml .= '<paragraph>';
									$return_xml .= '<extension extension_name="loop_video" source="'.$link_parts['target'].'"></extension>';
									if ( isset ( $id ) ) {
										$return_xml .= '<extension extension_name="loop_video_link" id="'. $id[0]->value.'"></extension>';
									}
									$return_xml .= '</paragraph>';
								} elseif ( $file->getMediaType() == "AUDIO" ) { #render videos entered as [[File:Video.mp4]] like loop_video
									$return_xml .= '<paragraph>';
									$return_xml .= '<extension extension_name="loop_audio" source="'.$link_parts['target'].'"></extension>';
									if ( isset ( $id ) ) {
										$return_xml .= '<extension extension_name="loop_video_link" id="'. $id[0]->value.'"></extension>';
									}
									$return_xml .= '</paragraph>';
								}
							}
						}
					} elseif ($target_ns == NS_CATEGORY) {
						// Kategorie-Link nicht ausgeben

					} elseif ($target_ns == NS_MEDIA) {
						if (!array_key_exists('href', $link_parts)) {
							$link_parts['href'] = Title::newFromText($link_parts['target']);;
						}
						if (array_key_exists('part',$link_parts)) {
							$link_parts['text'] = $link_parts['part'];
						}

						$file = MediaWikiServices::getInstance()->getRepoGroup()->getLocalRepo()->newFile($link_parts['href']);
						$target_url = '';
						if (is_object($file)) {
							$target_url = $file->getFullUrl();
						}

						$return_xml = '<php_link_media href="'.$target_url.'">';
						$return_xml .= ( array_key_exists( "text", $link_parts ) ) ? $link_parts['text'] : "Datei: Name nicht gefunden";
						$return_xml .= '</php_link_media>' ;
					} else {
						// internal link
						if (!array_key_exists('text', $link_parts)) {
							if(array_key_exists('part',$link_parts)) {
								$link_parts['text']=$link_parts['part'];
							} else {
								$link_parts['text']=$link_parts['target'];
							}
						}
						if (!array_key_exists('href', $link_parts)) {
							$link_parts['href']=$link_parts['target'];
						}

						if ($structureitem = LoopStructureItem::newFromToctext( $link_parts['href'] )) {
							$link_parts['href'] = 'article'.$structureitem->getArticle();
						}
						$return_xml =  '<php_link_internal href="'.$link_parts['href'].'">'.$link_parts['text'].'</php_link_internal>' ;
					}

				}
			}
		}
		$return = new DOMDocument;
		if ( empty( $return_xml ) ) {
			return $return;
		}
		$old_error_handler = set_error_handler("LoopXml::error_handler");
		libxml_use_internal_errors(true);

		try {
			$return->loadXml($return_xml);
		} catch ( Exception $e ) {
		}
		restore_error_handler();

		return $return;
	}

	public static function glossary2xml( ) {

		$articles = LoopGlossary::getGlossaryPages( "idArray" );
		$return = '';

		if ( !empty( $articles ) ) {
			foreach ( $articles as $articleId ) {
				$return .= self::articleFromId2xml( $articleId, array( "nometa" => true, "noarticle" => false ) );
			}
		}

		return $return;
	}

	public static function terminology2xml( ) {

		$terminology = LoopTerminology::getTerminologyPageContentText();
		$items = LoopTerminology::getSortedTerminology( $terminology );

		$xml = "";

		if ( !empty( $items ) ) {
			$xml .= "<terminology>\n";
			$xml .= "<article>\n";
			ksort( $items );
			$xml .= "<list>";
            foreach ( $items as $item => $content ) {
                if ( array_key_exists( "dt", $content ) &&  array_key_exists( "dd", $content ) ) {
                    $xml .= '<listitem><defkey>';
                    $i = 0;
                    foreach ( $content["dt"] as $term ) {
                        $xml .= ( $i == 0 ? "" : ", " );
                        $xml .= htmlspecialchars($term);
                        $i++;
                    }
                    $xml .= "</defkey>\n";
                   # $xml .= "<div class='loopterminology-definition'>";
                    foreach ( $content["dd"] as $def ) {
                        $xml .= "<paragraph>" . htmlspecialchars($def) . "</paragraph>\n";
                    }
                    $xml .= "</listitem>\n";
                }
            }
			$xml .= "</list>";
			$xml .= "</article>\n";
			$xml .= "</terminology>\n";
		}
		return $xml;
	}


	public static function bibliography2xml( ) {
	    global $wgLoopLiteratureCiteType;
		$xml = '<bibliography>'.SpecialLoopLiterature::renderBibliography('xml')."</bibliography>";
		return $xml;
	}

}



/**
 * wiki2xml was released 2005-2006 by Magnus Manske under the GPL.
 * see https://phabricator.wikimedia.org/diffusion/SVN/browse/trunk/parsers/graveyard/wiki2xml/php/wiki2xml.php
 *
 */

class wiki2xml
{
	var $cnt = 0 ; # For debugging purposes
	var $protocols = array ( "http" , "https" , "news" , "ftp" , "irc" , "mailto" ) ;
	var $errormessage = "ERROR!" ;
	var $compensate_markup_errors = true;
	var $auto_fill_templates = 'none' ; # Will try and replace templates right inline, instead of using <template> tags; requires global $content_provider
	var $use_space_tag = true ; # Use <space/> instead of spaces before and after tags
	var $allowed = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890 +-#:;.,%="\'\\' ;
	var $directhtmltags = array (
			"b" => "xhtml:b",
			"i" => "xhtml:i",
			"u" => "xhtml:u",
			"s" => "xhtml:s",
			"p" => "xhtml:p",
			"br" => "xhtml:br",
			"em" => "xhtml:em",
			"div" => "xhtml:div",
			"span" => "xhtml:span",
			"big" => "xhtml:big",
			"small" => "xhtml:small",
			"sub" => "xhtml:sub",
			"sup" => "xhtml:sup",
			"font" => "xhtml:font",
			"center" => "xhtml:center",
			"table" => "xhtml:table",
			"tr" => "xhtml:tr",
			"th" => "xhtml:th",
			"td" => "xhtml:td",
			"pre" => "xhtml:pre",
			"code" => "xhtml:code",
			"caption" => "xhtml:caption",
			"cite" => "xhtml:cite",
			"ul" => "xhtml:ul",
			"ol" => "xhtml:ol",
			"li" => "xhtml:li",
			"tt" => "xhtml:tt",
			"h1" => "xhtml:h1",
			"h2" => "xhtml:h2",
			"h3" => "xhtml:h3",
			"h4" => "xhtml:h4",
			"h5" => "xhtml:h5",
			"h6" => "xhtml:h6",
			"h7" => "xhtml:h7",
			"h8" => "xhtml:h8",
			"h9" => "xhtml:h9"
	) ;

	var $w ; # The wiki text
	var $wl ; # The wiki text length
	var $bold_italics ;
	var $tables = array () ; # List of open tables
	var $profile = array () ;

	# Some often used functions

	/**
	 * Inherit settings from an existing parser
	 */
	function inherit ( &$base )
	{
		$this->protocols = $base->protocols ;
		$this->errormessage = $base->errormessage ;
		$this->compensate_markup_errors = $base->compensate_markup_errors ;
		$this->auto_fill_templates = $base->auto_fill_templates ;
		$this->use_space_tag = $base->use_space_tag ;
		$this->compensate_markup_errors = $base->compensate_markup_errors ;
		$this->allowed = $base->allowed ;
		$this->directhtmltags = $base->directhtmltags ;
	}

	/**
	 * Matches a function to the current text (default:once)
	 */
	function once ( &$a , &$xml , $f , $atleastonce = true , $many = false )
	{
		#echo("<br>w2x_2");
		$f = "p_{$f}" ;
		$cnt = 0 ;
		#		print $f . " : " . htmlentities ( mb_substr ( $this->w , $a , 20 ) ) . "<br/>" ; flush () ;
		#		if ( !isset ( $this->profile[$f] ) ) $this->profile[$f] = 0 ; # PROFILING
		do {
			#			$this->profile[$f]++ ; # PROFILING
			$matched = $this->$f ( $a , $xml ) ;
			if ( $matched && $many ) $again = true ;
			else $again = false ;
			if ( $matched ) $cnt++ ;
		} while ( $again ) ;
		if ( !$atleastonce ) return true ;
		if ( $cnt > 0 ) return true ;
		return false ;
	}

	function onceormore ( &$a , &$xml , $f )
	{
		#echo("<br>w2x_3");
		return $this->once ( $a , $xml , $f , true , true ) ;
	}

	function nextis ( &$a , $t , $movecounter = true )
	{
		#echo("<br>w2x_4");
		#echo "[$a]" . "";
		#echo  "[$t]" . "";
		//#echo mb_substr ( $this->w , $a , strlen ( $t ) ) != $t . "<br>";
		//##echo ",a:" . $a .",t:".$t .",mc:".mb_substr ( $this->w , $a , strlen ( $t ) ));
		#exit;
		if ( $t == "{{" ) {
			return true;
		}
		if ( mb_substr ( $this->w , $a , strlen ( $t ) ) != $t ) return false ;
		if ( $movecounter ) $a += strlen ( $t ) ;
		return true ;
	}

	function nextchar ( &$a , &$x )
	{
		#echo("<br>w2x_5");
		if ( $a >= $this->wl ) return false ;
		$c = mb_substr ( $this->w , $a , 1 , 'UTF-8' );
		$x .= htmlspecialchars ( $c ) ;
		$a++ ;
		return true ;
	}

	function ischaracter ( $c )
	{
		#echo("<br>w2x_6");
		if ( $c >= 'A' && $c <= 'Z' ) return true ;
		if ( $c >= 'a' && $c <= 'z' ) return true ;
		return false ;
	}

	function skipblanks ( &$a , $blank = " " )
	{
		#echo("<br>w2x_7");
		while ( $a < $this->wl )
		{
			$c = mb_substr ( $this->w , $a , 1 , 'UTF-8' );
			if ( $c != $blank ) return ;
			$a++ ;
		}
	}

	##############


	function p_internal_link_target ( &$a , &$xml , $closeit = "]]" )
	{
		#echo("<br>w2x_8");
		return $this->p_internal_link_text ( $a , $xml , true , $closeit ) ;
	}

	function p_internal_link_text2 ( &$a , &$xml , $closeit )
	{
		#echo("<br>w2x_9");
		$bi = $this->bold_italics ;
		$ret = $this->p_internal_link_text ( $a , $xml , false , $closeit , false ) ;
		if ( $closeit == ']]' && '' != $this->bold_italics ) $ret = false ; # Dirty hack to ensure good XML; FIXME!!!
		return $ret ;
	}

	function p_internal_link_text ( &$a , &$xml , $istarget = false , $closeit = "]]" , $mark = true )
	{
		#echo("<br>w2x_10");
		$b = $a ;
		$x = "" ;
		if ( $b >= $this->wl ) return false ;
		$bi = $this->bold_italics ;
		$this->bold_italics = '' ;
		$closeit1 = $closeit[0] ;
		while ( 1 )
		{
			if ( $b >= $this->wl ) {
				$this->bold_italics = $bi ;
				return false ;
			}
			#$c = $this->w[$b] ;
			$c = mb_substr ( $this->w , $b , 1 , 'UTF-8' );
			if ( $closeit != "}}" && $c == "\n" ) {
				$this->bold_italics = $bi ;
				return false ;
			}
			if ( $c == "|" ) break ;
			if ( $c == $closeit1 && $this->nextis ( $b , $closeit , false ) ) break ;
			if ( !$istarget ) {
				if ( $c == "[" && $this->once ( $b , $x , "internal_link" ) ) continue ;
				if ( $c == "[" && $this->once ( $b , $x , "external_link" ) ) continue ;
				if ( $c == "{" && $this->once ( $b , $x , "template_variable" ) ) continue ;
				if ( $c == "{" && $this->once ( $b , $x , "template" ) ) continue ;
				if ( $c == "<" && $this->once ( $b , $x , "html" ) ) continue ;
				if ( $c == "'" && $this->p_bold ( $b , $x , "internal_link_text2" , $closeit ) ) { break ; }
				if ( $c == "'" && $this->p_italics ( $b , $x , "internal_link_text2" , $closeit ) ) { break ; }

				$c5 = mb_substr ( $this->w , $a+5 , 1 , 'UTF-8' );
				$c7 = mb_substr ( $this->w , $a+7 , 1 , 'UTF-8' );

				if ( $b + 10 < $this->wl &&
						( $c5 == '/' && $c7 == '/' ) &&
						$this->once ( $b , $x , "external_freelink" ) ) continue ;
			} else {
				# LOOP CHANGE for endless loop [{{filepath:Prokrastination_Fragebogen_Rist.pdf}} Fragebogen]
				if ( $closeit != "}}" ) {
					if ( $c == "{" && $this->once ( $b , $x , "template" ) ) continue ;
				} else {
					return false;
				}
			}
			$x .= htmlspecialchars ( $c ) ;
			$b++ ;
			/*			if ( $b >= $this->wl ) {
				$this->bold_italics = $bi ;
				return false ;
				}*/
		}

		if ( $closeit == "}}" && !$istarget ) {
			$xml .= mb_substr ( $this->w , $a , $b - $a ) ;
			$a = $b ;
			$this->bold_italics = $bi ;
			return true ;
		}

		$x = trim ( str_replace ( "\n" , "" , $x ) ) ;
		if ( $mark )
		{
			if ( $istarget ) $xml .= "<target>{$x}</target>" ;
			else $xml .= "<part>{$x}</part>" ;

		}
		else $xml .= $x ;
		$a = $b ;
		$this->bold_italics = $bi ;
		return true ;
	}

	function p_internal_link_trail ( &$a , &$xml )
	{
		#echo("<br>w2x_11");
		$b = $a ;
		$x = "" ;
		while ( 1 )
		{
			$c = "" ;

			if ( !$this->nextchar ( $b , $c ) ) break ;

			if ( $this->ischaracter ( $c ) )
			{
				$x .= $c ;
			}
			else
			{
				$b-- ;
				break ;
			}
		}
		if ( $x == "" ) return false ; # No link trail
		$xml .= "<trail>{$x}</trail>" ;
		$a = $b ;
		return true ;
	}

	function p_internal_link ( &$a , &$xml )
	{
		#echo("<br>w2x_12");
		$x = "" ;
		$b = $a ;
		if ( !$this->nextis ( $b , "[[" ) ) return false ;
		if ( !$this->p_internal_link_target ( $b , $x , "]]" ) ) return false ;
		while ( 1 )
		{
			if ( $this->nextis ( $b , "]]" ) ) break ;
			if ( !$this->nextis ( $b , "|" ) ) return false ;
			if ( !$this->p_internal_link_text ( $b , $x , false , "]]" ) ) return false ;
		}
		#$this->p_internal_link_trail ( $b , $x ) ; # LOOP: [[file:bildname.png]]Wort kein Leerzeichen zwischen ]] und Wort hat das Wort aus der PDF einfach entfernt.
		$xml .= "<link>{$x}</link>" ;
		$a = $b ;
		return true ;
	}

	function p_magic_variable ( &$a , &$xml )
	{
		#echo("<br>w2x_13");
		$x = "" ;
		$b = $a ;
		if ( !$this->nextis ( $b , "__" ) ) return false ;
		$varname = "" ;
		for ( $c = $b ; $c < $this->wl && (mb_substr ( $this->w , $c , 1 , 'UTF-8' )) != '_' ; $c++ )
			$varname .= mb_substr ( $this->w , $c , 1 , 'UTF-8' ) ;
			if ( !$this->nextis ( $c , "__" ) ) return false ;
			$xml .= "<magic_variable>{$varname}</magic_variable>" ;
			$a = $c ;
			return true ;
	}

	# Template and template variable, utilizing parts of the internal link methods
	function p_template ( &$a , &$xml )
	{
		#echo("<br>w2x_14");
		global $content_provider , $xmlg ;
		if ( is_array( $xmlg ) &&  array_key_exists("useapi", $xmlg) ) { # 2020 edit
			if ( $xmlg["useapi"] ) return false ; # API already resolved templates
		}

		$x = "" ;
		$b = $a ;
		if ( !$this->nextis ( $b , "{{" ) ) return false ;
		#		if ( $this->nextis ( $b , "{" , false ) ) return false ; # Template names may not start with "{"
		if ( !$this->p_internal_link_target ( $b , $x , "}}" ) ) return false ;
		$target = $x ;
		$variables = array () ;
		$varnames = array () ;
		$vcount = 1 ;
		while ( 1 )
		{
			if ( $this->nextis ( $b , "}}" ) ) break ;
			if ( !$this->nextis ( $b , "|" ) ) return false ;
			$l1 = strlen ( $x ) ;
			if ( !$this->p_internal_link_text ( $b , $x , false , "}}" ) ) return false ;
			$v = mb_substr ( $x , $l1 ) ;
			$v = explode ( "=" , $v, 2 ) ;
			if ( count ( $v ) < 2 ) $vk = $vcount ;
			else {
				$vk = trim ( array_shift ( $v ) ) ;
				$varnames[$vcount] = $vk;
			}
			$vv = array_shift ( $v ) ;
			$variables[$vk] = $vv ;
			if ( !isset ( $variables[$vcount] ) ) $variables[$vcount] = $vv ;
			$vcount++ ;
		}
		$explode_target_bigger = @explode ( ">" , $target , 2 );
		$explode_target_smaller = @explode ( "<" , $target , 2 );
		$target = array_pop ( $explode_target_bigger ) ;
		$target = array_shift ( $explode_target_smaller ) ;
		if ( $this->auto_fill_templates == 'all' ) $replace_template = true ;
		else if ( $this->auto_fill_templates == 'none' ) $replace_template = false ;
		else {
			$found = in_array ( ucfirst ( $target ) , $this->template_list ) ;
			if ( $found AND $this->auto_fill_templates == 'these' ) $replace_template = true ;
			else if ( !$found AND $this->auto_fill_templates == 'notthese' ) $replace_template = true ;
			else $replace_template = false ;
		}

		if ( mb_substr ( $target , 0 , 1 ) == '#' ) { # Try template logic
			$between = $this->process_template_logic ( $target , $variables ) ;
			# Change source (!)
			$w1 = mb_substr ( $this->w , 0 , $a ) ;
			$w2 = mb_substr ( $this->w , $b ) ;
			$this->w = $w1 . $between . $w2 ;
			$this->wl = strlen ( $this->w ) ;
		} else if ( $replace_template ) { # Do not generate <template> sections, but rather replace the template call with the template text

			# Get template text
			#$between = trim ( $content_provider->get_template_text ( $target ) ) ;
			$between = '';
			#add_authors ( $content_provider->authors ) ;



			# Removing <noinclude> stuff
			$between = preg_replace( '?<noinclude>.*</noinclude>?msU', '', $between);
			$between = str_replace ( "<include>" , "" , $between ) ;
			$between = str_replace ( "</include>" , "" , $between ) ;
			$between = str_replace ( "<includeonly>" , "" , $between ) ;
			$between = str_replace ( "</includeonly>" , "" , $between ) ;

			# Remove HTML comments
			$between = str_replace ( "-->\n" , "-->" , $between ) ;
			$between = preg_replace( '?<!--.*-->?msU', '', $between) ;

			# Replacing template variables.
			# ATTENTION: Template variables within <nowiki> sections of templates will be replaced as well!

			if ( $a > 0 && mb_substr ( $between , 0 , 2 ) == '{|' )
				$between = "\n" . $between ;

				$this->replace_template_variables ( $between , $variables ) ;

				# Change source (!)
				$w1 = mb_substr ( $this->w , 0 , $a ) ;
				$w2 = mb_substr ( $this->w , $b ) ;
				$this->w = $w1 . $between . $w2 ;
				$this->wl = strlen ( $this->w ) ;
		} else {
			$xml .= "<template><target>{$target}</target>";
			for ( $i = 1 ; $i < $vcount ; $i++ ) {
				//				$v = htmlentities ( $variables[$i] ) ;
				$v = htmlspecialchars ( $variables[$i] ) ;
				if ( isset( $varnames[$i] ) ) {
					$vn = htmlspecialchars ( $varnames[$i] ) ;
					$xml .= "<arg name=\"{$vn}\">{$v}</arg>";
				}
				else $xml .= "<arg>{$v}</arg>";
			}
			$xml .= "</template>" ;
			$a = $b ;
		}
		return true ;
	}

	function process_template_logic ( $title , $variables ) {

		#echo("<br>w2x_15");
		# : Process title and variables for sub-template-replacements

		if ( mb_substr ( $title , 0 , 4 ) == "#if:" ) {
			$title = trim ( mb_substr ( $title , 4 ) ) ;
			if ( $title == '' ) return array_pop ( $variables ) ; # ELSE
			return array_shift ( $variables ) ; # THEN
		}

		if ( mb_substr ( $title , 0 , 8 ) == "#switch:" ) {
			$title = trim ( array_pop ( explode ( ':' , $title , 2 ) ) ) ;
			foreach ( $variables AS $v ) {
				$v = explode ( '=' , $v , 2 ) ;
				$key = trim ( array_shift ( $v ) ) ;
				if ( $key != $title ) continue ; # Wrong key
				return array_pop ( $v ) ; # Correct key, return value
			}
		}

		# BAD PARSER FUNCTION! Ignoring...
		return $title ;
	}

	function replace_template_variables ( &$text , &$variables ) {

		#echo("<br>w2x_16");
		global $xmlg ;
		if ( is_array( $xmlg ) &&  array_key_exists("useapi", $xmlg) ) { # 2020 edit
			if ( $xmlg["useapi"] ) return false ; # API already resolved templates
		}
		for ( $a = 0 ; $a+3 < strlen ( $text ) ; $a++ ) {
			if ( $text[$a] != '{' ) continue ;
			while ( $this->p_template_replace_single_variable ( $text , $a , $variables ) ) ;
		}
	}

	function p_template_replace_single_variable ( &$text , $a , &$variables ) {

		#echo("<br>w2x_17");
		if ( mb_substr ( $text , $a , 3 ) != '{{{' ) return false ;
		$b = $a + 3 ;

		# Name
		$start = $b ;
		$count = 0 ;
		while ( $b < strlen ( $text ) && ( $text[$b] != '|' || $count > 0 ) && ( mb_substr ( $text , $b , 3 ) != '}}}' || $count > 0 ) ) {
			if ( $this->p_template_replace_single_variable ( $text , $b , $variables ) ) continue ;
			if ( $text[$b] == '{' ) $count++ ;
			if ( $text[$b] == '}' ) $count-- ;
			$b++ ;
		}
		if ( $b >= strlen ( $text ) ) return false ;
		$name = trim ( mb_substr ( $text , $start , $b - $start ) ) ;

		# Default value
		$value = "" ;
		if ( $text[$b] == '|' ) {
			$b++ ;
			$start = $b ;
			$count = 0 ;
			while ( $b < strlen ( $text ) && ( mb_substr ( $text , $b , 3 ) != '}}}' || $count > 0 ) ) {
				if ( $this->p_template_replace_single_variable ( $text , $b , $variables ) ) continue ;
				if ( $text[$b] == '{' ) $count++ ;
				if ( $text[$b] == '}' ) $count-- ;
				$b++ ;
			}
			if ( $b >= strlen ( $text ) ) return false ;
			$value = trim ( mb_substr ( $text , $start , $b - $start ) ) ;#$start - $b - 1 ) ) ;
		}

		// Replace
		$b += 3 ; # }}}
		if ( isset ( $variables[$name] ) ) {
			$value = $variables[$name] ;
		}
		$text = mb_substr ( $text , 0 , $a ) . $value . mb_substr ( $text , $b ) ;

		return true ;
	}

	function p_template_variable ( &$a , &$xml )
	{
		#echo("<br>w2x_18");
		$x = "" ;
		$b = $a ;
		if ( !$this->nextis ( $b , "{{{" ) ) return false ;
		if ( !$this->p_internal_link_text ( $b , $x , false , "}}}" ) ) return false ;
		if ( !$this->nextis ( $b , "}}}" ) ) return false ;
		$xml .= "<templatevar>{$x}</templatevar>" ;
		$a = $b ;
		return true ;
	}

	# Bold / italics
	function p_bold ( &$a , &$xml , $recurse = "restofline" , $end = "" )
	{
		#echo("<br>w2x_19");
		return $this->p_intwined ( $a , $xml , "bold" , "'''" , $recurse , $end ) ;
	}

	function p_italics ( &$a , &$xml , $recurse = "restofline" , $end = "" )
	{
		#echo("<br>w2x_20");
		return $this->p_intwined ( $a , $xml , "italics" , "''" , $recurse , $end ) ;
	}

	function p_intwined ( &$a , &$xml , $tag , $markup , $recurse , $end )
	{
		#echo("<br>w2x_21");
		$b = $a ;
		if ( !$this->nextis ( $b , $markup ) ) return false ;
		$id = mb_substr ( ucfirst ( $tag ) , 0 , 1 ) ;
		$bi = $this->bold_italics ;
		$open = false ;
		if ( mb_substr ( $this->bold_italics , -1 ) == $id )
		{
			$x = "</{$tag}>" ;
			$this->bold_italics = mb_substr ( $this->bold_italics , 0 , -1 ) ;
		}
		else
		{
			$pos = strpos ( $this->bold_italics , $id ) ;
			if ( false !== $pos ) return false ; # Can't close a tag that ain't open
			$open = true ;
			$x = "<{$tag}>" ;
			$this->bold_italics .= $id ;
		}

		if ( $end == "" )
		{
			$res = $this->once ( $b , $x , $recurse ) ;
		}
		else
		{
			$r = "p_{$recurse}" ;
			$res = $this->$r ( $b , $x , $end ) ;
		}

		$this->bold_italics = $bi ;
		if ( !$res )
		{
			return false ;
		}
		$xml .= $x ;
		$a = $b ;
		return true ;
	}

	function scanplaintext ( &$a , &$xml , $goodstop , $badstop )
	{
		#echo("<br>w2x_22");
		$b = $a ;
		$x = "" ;
		while ( $b < $this->wl )
		{
			if ( (mb_substr ( $this->w , $b , 1 , 'UTF-8' )) == "{" && $this->once ( $b , $x , "template" ) ) continue ;
			foreach ( $goodstop AS $s )
				if ( $this->nextis ( $b , $s , false ) ) break 2 ;
				foreach ( $badstop AS $s )
					if ( $this->nextis ( $b , $s , false ) ) return false ;
					$c = mb_substr ( $this->w , $b , 1 , 'UTF-8' ) ;
					$x .= htmlspecialchars ( $c ) ;
					$b++ ;
		}
		if ( count ( $goodstop ) > 0 && $b >= $this->wl ) return false ; # Reached end; not good
		$a = $b ;
		$xml .= $x ;
		return true ;
	}

	# External link
	function p_external_freelink ( &$a , &$xml , $mark = true )
	{
		#echo("<br>w2x_23");
		if ( $this->wl <= $a + 10 ) return false ; # Can't have an URL shorter than that

		$c5 = mb_substr ( $this->w , $a+5 , 1 , 'UTF-8' );
		$c7 = mb_substr ( $this->w , $a+7 , 1 , 'UTF-8' );
		if ( $c5 != '/' && $c7 != '/' ) return false ; # Shortcut for protocols 3-6 chars length
		$protocol = "" ;
		$b = $a ;
		#		while ( $this->w[$b] == "{" && $this->once ( $b , $x , "template" ) ) $b = $a ;
		foreach ( $this->protocols AS $p )
		{
			if ( $this->nextis ( $b , $p . "://" ) )
			{
				$protocol = $p ;
				break ;
			}
		}
		if ( $protocol == "" ) return false ;
		$x = "{$protocol}://" ;
		while ( $b < $this->wl )
		{
			#$c = $this->w[$b] ;
			$c = mb_substr ( $this->w , $b , 1 , 'UTF-8' );
			if ( $c == "{" && $this->once ( $b , $x , "template" ) ) continue ;
			if ( $c == "\n" || $c == " " || $c == '|' ) break ;
			if ( !$mark && $c == "]" ) break ;
			$x .= htmlspecialchars ( $c ) ;
			$b++ ;
		}
		if ( mb_substr ( $x , -1 ) == "." || mb_substr ( $x , -1 ) == "," )
		{
			$x = mb_substr ( $x , 0 , -1 ) ;
			$b-- ;
		}
		$a = $b ;
		$x = htmlspecialchars ( $x , ENT_QUOTES ) ;
		if ( $mark ) $xml .= "<link type='external' href='$x'/>" ;
		else $xml .= $x ;
		return true ;
	}

	function p_external_link ( &$a , &$xml , $mark = true )
	{
		#echo("<br>w2x_24");
		$b = $a ;
		if ( !$this->nextis ( $b , "[" ) ) return false ;
		$url = "" ;
		$c = $b ;
		$x = "" ;
		while ( $c < $this->wl && (mb_substr ( $this->w , $c , 1 , 'UTF-8' )) == "{" && $this->once ( $c , $x , "template" ) ) $c = $b ;
		if ( $c >= $this->wl ) return false ;
		$x = "" ;
		if ( !$this->p_external_freelink ( $b , $url , false ) ) return false ;
		$this->skipblanks ( $b ) ;
		if ( !$this->scanplaintext ( $b , $x , array ( "]" ) , array ( "\n" ) ) ) return false ;
		$a = $b + 1 ;
		$xml .= "<link type='external' href='{$url}'>{$x}</link>" ;
		return true ;
	}

	# Heading
	function p_heading ( &$a , &$xml )
	{
		#echo("<br>w2x_25");
		if ( $a >= $this->wl || (mb_substr ( $this->w , $a , 1 , 'UTF-8' )) != '=' ) return false ;
		$b = $a ;
		$level = 0 ;
		$h = "" ;
		$x = "" ;
		while ( $this->nextis ( $b , "=" ) )
		{
			$level++ ;
			$h .= "=" ;
		}
		$this->skipblanks ( $b ) ;
		if ( !$this->once ( $b , $x , "restofline" ) ) return false ;
		if ( $this->compensate_markup_errors ) $x = trim ( $x ) ;
		else if ( $x != trim ( $x ) ) $xml .= "<error type='heading' reason='trailing blank'/>" ;
		if ( mb_substr ( $x , -$level ) != $h ) return false ; # No match

		$x = trim ( mb_substr ( $x , 0 , -$level ) ) ;
		$level -= 1 ;
		$a = $b ;
		$xml .= "<heading level='" . ($level+1) . "'>{$x}</heading>" ;
		return true ;
	}

	# Line
	# Often used function for parsing the rest of a text line
	function p_restofline ( &$a , &$xml , $closeit = array() )
	{
		#echo("<br>w2x_26");
		$b = $a ;
		$x = "" ;
		$override = false ;
		while ( $b < $this->wl && !$override )
		{
			#$c = $this->w[$b] ;
			$c = mb_substr ( $this->w , $b , 1 , 'UTF-8' );

			if ( $c == "\n" ) { $b++ ; break ; }
			foreach ( $closeit AS $z )
				if ( $this->nextis ( $b , $z , false ) ) break ;
				if ( $c == "_" && $this->once ( $b , $x , "magic_variable" ) ) continue ;
				if ( $c == "[" && $this->once ( $b , $x , "internal_link" ) ) continue ;
				if ( $c == "[" && $this->once ( $b , $x , "external_link" ) ) continue ;
				if ( $c == "{" && $this->once ( $b , $x , "template_variable" ) ) continue ;
				if ( $c == "{" && $this->once ( $b , $x , "template" ) ) continue ;
				if ( $c == "{" && $this->p_table ( $b , $x ) ) continue ;
				if ( $c == "<" && $this->once ( $b , $x , "html" ) ) continue ;
				if ( $c == "'" && $this->once ( $b , $x , "bold" ) ) { $override = true ; break ; }
				if ( $c == "'" && $this->once ( $b , $x , "italics" ) ) { $override = true ; break ; }

				$c5 = mb_substr ( $this->w , $a+5 , 1 , 'UTF-8' );
				$c7 = mb_substr ( $this->w , $a+7 , 1 , 'UTF-8' );
				if ( $b + 10 < $this->wl &&
						( $c5 == '/' && $c7 == '/' ) &&
						$this->once ( $b , $x , "external_freelink" ) ) continue ;

						# Just an ordinary character
						#$x .= htmlspecialchars ( $c ) ;
						$x .= htmlspecialchars ( $c, ENT_COMPAT, 'UTF-8' ) ;
						#$x .=  $c  ;
						$b++ ;
						if ( $b >= $this->wl ) break ;
		}
		if ( !$override && $this->bold_italics != "" )
		{
			return false ;
		}
		$xml .= $x ;
		$a = $b ;
		return true ;
	}

	function p_line ( &$a , &$xml , $force )
	{
		#echo("<br>w2x_27");
		if ( $a >= $this->wl ) return false ; # Already at the end of the text
		#$c = $this->w[$a] ;
		$c = mb_substr ( $this->w , $a , 1 , 'UTF-8' );
		if ( !$force )
		{
			if ( $c == '*' || $c == ':' || $c == '#' || $c == ';' || $c == ' ' || $c == "\n" ) return false ; # Not a suitable beginning
			if ( $this->nextis ( $a , "{|" , false ) ) return false ; # Table
			if ( count ( $this->tables ) > 0 && $this->nextis ( $a , "|" , false ) ) return false ; # Table
			if ( count ( $this->tables ) > 0 && $this->nextis ( $a , "!" , false ) ) return false ; # Table
			if ( $this->nextis ( $a , "=" , false ) ) return false ; # Heading
			if ( $this->nextis ( $a , "----" , false ) ) return false ; # <hr>
		}
		$this->bold_italics = "" ;
		return $this->once ( $a , $xml , "restofline" ) ;
	}

	function p_blankline ( &$a , &$xml )
	{
		#echo("<br>w2x_28");
		if ( $this->nextis ( $a , "\n" ) ) return true ;
		return false ;
	}

	function p_block_lines ( &$a , &$xml , $force = false )
	{
		#echo("<br>w2x_29");
		$x = "" ;
		$b = $a ;
		if ( !$this->p_line ( $b , $x , $force ) ) return false ;
		while ( $this->p_line ( $b , $x , false ) ) ;
		while ( $this->p_blankline ( $b , $x ) ) ; # Skip coming blank lines
		$xml .= "<paragraph>{$x}</paragraph>" ;
		$a = $b ;
		return true ;
	}



	# PRE block
	# Parses a line starting with ' '
	function p_preline ( &$a , &$xml )
	{
		#echo("<br>w2x_30");
		if ( $a >= $this->wl ) return false ; # Already at the end of the text
		if ( (mb_substr ( $this->w , $a , 1 , 'UTF-8' ))!= ' ' ) return false ; # Not a preline

		$a++ ;
		$this->bold_italics = "" ;
		$x = "" ;
		$ret = $this->once ( $a , $x , "restofline" ) ;
		if ( $ret ) {
			$xml .= "<preline>" . $x . "</preline>" ;
		}
		return $ret ;
	}

	# Parses a block of lines each starting with ' '
	function p_block_pre ( &$a , &$xml )
	{
		#echo("<br>w2x_31");
		$x = "" ;
		$b = $a ;
		if ( !$this->once ( $b , $x , "preline" , true , true ) ) return false ;
		$this->once ( $b , $x , "blankline" , false , true ) ;
		$xml .= "<preblock>{$x}</preblock>" ;
		$a = $b ;
		return true ;
	}

	# LIST block
	# Returns a list tag depending on the wiki markup
	function listtag ( $c , $open = true )
	{
		#echo("<br>w2x_32");
		if ( !$open ) return "</list>" ;
		$r = "" ;
		if ( $c == '#' ) $r = "numbered" ;
		if ( $c == '*' ) $r = "bullet" ;
		if ( $c == ':' ) $r = "ident" ;
		if ( $c == ';' ) $r = "def" ;
		if ( $r != "" ) $r = " type='{$r}'" ;
		$r = "<list{$r}>" ;
		return $r ;
	}

	# Opens/closes list tags
	function fixlist ( $last , $cur )
	{
		#echo("<br>w2x_33");
		$r = "" ;
		$olast = $last ;
		$ocur = $cur ;
		$ocommon = "" ;

		# Remove matching parts
		while ( $last != "" && $cur != "" && $last[0] == $cur[0] )
		{
			$ocommon = $cur[0] ;
			$cur = mb_substr ( $cur , 1 ) ;
			$last = mb_substr ( $last , 1 ) ;
		}

		# Close old tags
		$fixitemtag = false ;
		if ( $last != "" && $ocommon != "" ) $fixitemtag = true ;
		while ( $last != "" )
		{
			$r .= "</listitem>" . $this->listtag ( mb_substr ( $last , -1 ) , false ) ;
			$last = mb_substr ( $last , 0 , -1 ) ;
		}
		if ( $fixitemtag ) $r .= "</listitem><listitem>" ;

		# Open new tags
		while ( $cur != "" )
		{
			$r .= $this->listtag ( $cur[0] ) . "<listitem>" ;
			$cur = mb_substr ( $cur , 1 ) ;
		}

		return $r ;
	}

	# Parses a single list line
	function p_list_line ( &$a , &$xml , &$last )
	{
		#echo("<br>w2x_34");
		$cur = "" ;
		do {
			$lcur = $cur ;
			while ( $this->nextis ( $a , "*" ) ) $cur .= "*" ;
			while ( $this->nextis ( $a , "#" ) ) $cur .= "#" ;
			while ( $this->nextis ( $a , ":" ) ) $cur .= ":" ;
			while ( $this->nextis ( $a , ";" ) ) $cur .= ";" ;
		} while ( $cur != $lcur ) ;

		$unchanged = false ;
		#		if ( mb_substr ( $cur , 0 , strlen ( $last ) ) == $last ) $unchanged = true ;
		if ( $last == $cur ) $unchanged = true ;
		$xml .= $this->fixlist ( $last , $cur ) ;

		if ( $cur == "" ) return false ; # Not a list line
		$last = $cur ;
		$this->skipblanks ( $a ) ;

		if ( $unchanged ) $xml .= "</listitem><listitem>" ;
		if ( $cur == ";" ) # Definition
		{
			$b = $a ;
			while ( $b < $this->wl && (mb_substr ( $this->w , $b , 1 , 'UTF-8' )) != "\n" && (mb_substr ( $this->w , $b , 1 , 'UTF-8' )) != ':' ) $b++ ;
			if ( $b >= $this->wl || (mb_substr ( $this->w , $b , 1 , 'UTF-8' )) == "\n" )
			{
				$xml .= "<defkey>" ;
				$this->p_restofline ( $a , $xml ) ;
				$xml .= "</defkey>" ;
			}
			else
			{
				$xml .= "<defkey>" ;
				$this->w[$b] = "\n" ; //
				$this->p_restofline ( $a , $xml ) ;
				$xml .= "</defkey>" ;
				$xml .= "<defval>" ;
				$this->p_restofline ( $a , $xml ) ;
				$xml .= "</defval>" ;
			}
		}
		else $this->p_restofline ( $a , $xml ) ;
		return true ;
	}

	# Checks for a list block ( those nasty things starting with '*', '#', or the like...
	function p_block_list ( &$a , &$xml )
	{
		#echo("<br>w2x_35");
		$last = "" ;
		$found = false ;
		while ( $this->p_list_line ( $a , $xml , $last ) ) $found = true ;
		return $found ;
	}

	# HTML
	# This function detects a HTML tag, finds the matching close tag,
	# parses everything in between, and returns everything as an extension.
	# Returns false otherwise.
	function p_html ( &$a , &$xml )
	{
		#echo("<br>w2x_36");
		if ( !$this->nextis ( $a , "<" , false ) ) return false ;

		$b = $a ;
		$x = "" ;
		$tag = "" ;
		$closing = false ;
		$selfclosing = false ;

		if ( !$this->p_html_tag ( $b , $x , $tag , $closing , $selfclosing ) ) return false ;

		if ( isset ( $this->directhtmltags[$tag] ) )
		{
			$tag_open = "<" . $this->directhtmltags[$tag] ;
			$tag_close = "</" . $this->directhtmltags[$tag] . ">" ;
		}
		else
		{
			$tag_open = "<extension extension_name='{$tag}'" ;
			$tag_close = "</extension>" ;
		}

		# Is this tag self-closing?
		if ( $selfclosing )
		{
			$a = $b ;
			$xml .= $tag_open . $x . ">" . $tag_close ;
			return true ;
		}

		# Find the matching close tag
		#  : The simple open/close counter should be replaced with a
		#        stack to allow for tolerating half-broken HTML,
		#        such as unclosed <li> tags
		$begin = $b ;
		$cnt = 1 ;
		$tag2 = "" ;
		while ( $cnt > 0 && $b < $this->wl )
		{
			$x2 = "" ;
			$last = $b ;
			if ( !$this->p_html_tag ( $b , $x2 , $tag2 , $closing , $selfclosing ) )
			{
				$dummy = "";
				if ( $tag != "nowiki" && (mb_substr ( $this->w , $b , 1 , 'UTF-8' )) == '{' && $this->p_template ( $b , $dummy ) )
					continue ;
					$b++ ;
					continue ;
			}
			if ( $tag != $tag2 ) continue ;
			if ( $selfclosing ) continue ;
			if ( $closing ) $cnt-- ;
			else $cnt++ ;
		}

		if ( $cnt > 0 ) return false ; # Tag was never closed

		# What happens in between?
		$between = mb_substr ( $this->w , $begin , $last - $begin ) ;

		if ( $tag != "nowiki" && $tag != "math" && $tag != "source" && $tag != "syntaxhighlight")
		{
			if ( $tag == 'gallery' ) {
				$this->gallery2wiki ( $between ) ;
				$tag_open = "" ;
				$tag_close = "" ;
			}

			# Parse the part in between the tags
			$subparser = new wiki2xml ;
			$subparser->inherit ( $this ) ;
			$between2 = $subparser->parse ( $between ) ;

			# Was the parsing correct?
			if ( $between2 != $this->errormessage )
				$between = $this->strip_single_paragraph ( $between2 ) ; # No <paragraph> for inline HTML tags
				else
					$between = htmlspecialchars ( $between ) ; # Incorrect markup, use safe wiki source instead
		}
		else {
			$between = htmlspecialchars ( $between ) ; # No wiki parsing in here

			if ($tag == "syntaxhighlight") {
				$between = str_replace(' ','<space/>',$between);
				#$between = '<![CDATA['.$between.']]>';

			}
		}

		$a = $b ;
		if ( $tag_open != "" ) $xml .= $tag_open . $x . ">" ;
		$xml .= $between ;
		if ( $tag_close != "" ) $xml .= $tag_close ;
		return true ;
	}

	/**
	 * Converts the lines within a <gallery> to wiki tables
	 */
	function gallery2wiki ( &$text ) {
		$lines = explode ( "\n" , trim ( $text ) ) ;
		$text = "{| style='border-collapse: collapse; border: 1px solid grey;'\n" ;
		$cnt = 0 ;
		foreach ( $lines AS $line ) {
			if ( $cnt >= 4 ) {
				$cnt = 0 ;
				$text .= "|--\n" ;
			}
			$a = explode ( "|" , $line , 2 ) ;
			if ( count ( $a ) == 1 ) { # Generate caption from file name
				$b = $a[0] ;
				$b = explode ( ":" , $b , 2 ) ;
				$b = array_pop ( $b ) ;
				$b = explode ( "." , $b ) ;
				array_pop ( $b ) ;
				$a[] = implode ( "." , $b ) ;
			}
			$link = array_shift ( $a ) ;
			$caption = array_pop ( $a ) ;
			$text .= "|valign=top align=left|[[{$link}|thumb|center|]]<br/>{$caption}\n" ;
			$cnt++ ;
		}
		$text .= "|}\n" ;
	}

	function strip_single_paragraph ( $s )
	{
		#echo("<br>w2x_37");
		if ( mb_substr_count ( $s , "paragraph>" ) == 2 &&
			 mb_substr ( $s , 0 , 11 ) == "<paragraph>" &&
			 mb_substr ( $s , -12 ) == "</paragraph>" )
			$s = mb_substr ( $s , 11 , -12 ) ;
			return $s ;
	}

	# This function checks for and parses a HTML tag
	# Only to be called from p_html, as it returns only a partial extension tag!
	function p_html_tag ( &$a , &$xml , &$tag , &$closing , &$selfclosing )
	{
		#echo("<br>w2x_38");
		if ( (mb_substr ( $this->w , $a , 1 , 'UTF-8' )) != '<' ) return false ;
		$b = $a + 1 ;
		$this->skipblanks ( $b ) ;
		$tag = "" ;
		$attrs = array () ;
		if ( !$this->scanplaintext ( $b , $tag , array ( " " , ">" ) , array ( "\n" ) ) ) return false ;

		$this->skipblanks ( $b ) ;
		if ( $b >= $this->wl ) return false ;

		$tag = trim ( strtolower ( $tag ) ) ;
		$closing = false ;
		$selfclosing = false ;

		# Is closing tag?
		if ( mb_substr ( $tag , 0 , 1 ) == "/" )
		{
			$tag = mb_substr ( $tag , 1 ) ;
			$closing = true ;
			$this->skipblanks ( $b ) ;
			if ( $b >= $this->wl ) return false ;
		}

		if ( mb_substr ( $tag , -1 ) == "/" )
		{
			$tag = mb_substr ( $tag , 0 , -1 ) ;
			$selfclosing = true ;
		}

		# Parsing attributes
		$ob = $b ;
		$q = "" ;
		while ( $b < $this->wl && ( $q != "" || ( (mb_substr ( $this->w , $b , 1 , 'UTF-8' )) != '>' && (mb_substr ( $this->w , $b , 1 , 'UTF-8' )) != '/' ) ) ) {
			if ( (mb_substr ( $this->w , $b , 1 , 'UTF-8' )) == '"' || (mb_substr ( $this->w , $b , 1 , 'UTF-8' )) == "'" ) {
				if ( $q == "" ) $q = (mb_substr ( $this->w , $b , 1 , 'UTF-8' )) ;
				else if ( (mb_substr ( $this->w , $b , 1 , 'UTF-8' )) == $q ) $q = "" ;
			}
			$b++ ;
		}
		if ( $b >= $this->wl ) return false ;
		$attrs = $this->preparse_attributes ( mb_substr ( $this->w , $ob , $b - $ob + 1 ) ) ;

		# Is self closing?
		if ( $tag == 'br' ) $selfclosing = true ; # Always regard <br> as <br/>
		if ( (mb_substr ( $this->w , $b , 1 , 'UTF-8' )) == '/' )
		{
			$b++ ;
			$this->skipblanks ( $b ) ;
			$selfclosing = true ;
		}

		$this->skipblanks ( $b ) ;
		if ( $b >= $this->wl ) return false ;
		if ( (mb_substr ( $this->w , $b , 1 , 'UTF-8' )) != '>' ) return false ;

		$a = $b + 1 ;
		if ( count ( $attrs ) > 0 )
		{
			$xml = " " . implode ( " " , $attrs ) ;
		}
		return true ;
	}

	# This function replaces templates and separates HTML attributes.
	# It is used for both HTML tags and wiki tables
	function preparse_attributes ( $x )
	{
		#echo("<br>w2x_39");
		# Creating a temporary new parser to run the attribute list in
		$np = new wiki2xml ;
		$np->inherit ( $this ) ;
		$np->w = $x ;
		$np->wl = strlen ( $x ) ;

		# Replacing templates, and '<' and '>' in parameters
		$c = 0 ;
		$q = "" ;
		while ( $q != "" || ( $c < $np->wl && $np->w[$c] != '>' && $np->w[$c] != '/' ) )
		{
			$y = $np->w[$c] ;
			if ( $np->nextis ( $c , "{{" , false ) ) {
				$xx = "" ;
				if ( $np->p_template ( $c , $xx ) ) continue ;
				else $c++ ;
			} else if ( $y == "'" || $y == '"' ) {
				if ( $q == "" ) $q = $y ;
				else if ( $y == $q ) $q = "" ;
				$c++ ;
			} else if ( $q != "" && ( $y == '<' || $y == '>' ) ) {
				$y = htmlentities ( $y ) ;
				$np->w = mb_substr ( $np->w , 0 , $c ) . $y . mb_substr ( $np->w , $c + 1 ) ;
				$np->wl += strlen ( $y ) - 1 ;
			} else $c++ ;
			if ( $c >= $np->wl ) return array () ;
		}

		$attrs = array () ;
		$c = 0 ;

		# Seeking attributes
		while ( $np->w[$c] != '>' && $np->w[$c] != '/' )
		{
			$attr = "" ;
			if ( !$np->p_html_attr ( $c , $attr ) ) break ;
			if ( $attr != "" ) {
				$exploded_attr = explode ( "=" , $attr , 2 );
				$key = array_shift ( $exploded_attr ) ;
				if ( !isset ( $attrs[$key] ) && mb_substr ( $attr , -3 , 3 ) != '=""' ) {
					$attrs[$key] = $attr ;
				}
			}
			$np->skipblanks ( $c ) ;
			if ( $c >= $np->wl ) return array () ;
		}
		if ( mb_substr ( $np->w , $c ) != ">" AND mb_substr ( $np->w , $c ) != "/" ) return array() ;

		return $attrs ;
	}


	# This function scans a single HTML tag attribute and returns it as <attr name='key'>value</attr>
	function p_html_attr ( &$a , &$xml )
	{
		#echo("<br>w2x_40");
		$b = $a ;
		$this->skipblanks ( $b ) ;
		if ( $b >= $this->wl ) return false ;
		$name = "" ;
		if ( !$this->scanplaintext ( $b , $name , array ( " " , "=" , ">" , "/" ) , array ( "\n" ) ) ) return false ;

		$this->skipblanks ( $b ) ;
		if ( $b >= $this->wl ) return false ;
		$name = trim ( strtolower ( $name ) ) ;

		# Trying to catch illegal names; should be replaced with regexp
		$n2 = "" ;
		for ( $q = 0 ; $q < strlen ( $name ) ; $q++ ) {
			if ( $name[$q] == '_' OR ( $name[$q] >= 'a' AND $name[$q] <= 'z' ) )
				$n2 .= $name[$q] ;
		}
		$name = trim ( $n2 ) ;
		if ( $name == 'extension_name' ) return false ; # Not allowed, because used internally
		if ( $name == '' ) return false ;

		# Determining value
		$value = "" ;
		if ( (mb_substr ( $this->w , $b , 1 , 'UTF-8' )) == "=" )
		{
			$b++ ;
			$this->skipblanks ( $b ) ;
			if ( $b >= $this->wl ) return false ;
			$q = "" ;
			$is_q = false ;
			if ( (mb_substr ( $this->w , $b , 1 , 'UTF-8' )) == '"' || (mb_substr ( $this->w , $b , 1 , 'UTF-8' )) == "'" )
			{
				$q = (mb_substr ( $this->w , $b , 1 , 'UTF-8' )) ;
				$b++ ;
				if ( $b >= $this->wl ) return false ;
				$is_q = true ;
			}
			while ( $b < $this->wl )
			{
				$c = (mb_substr ( $this->w , $b , 1 , 'UTF-8' )) ;
				if ( $c == $q )
				{
					$b++ ;
					if ( $is_q ) break ;
					return false ; # Broken attribute value
				}
				if ( $this->nextis ( $b , "\\{$q}" ) ) # Ignore escaped quotes
				{
					$value .= "\\{$q}" ;
					continue ;
				}
				if ( $c == "\n" ) return false ; # Line break before value end
				if ( !$is_q && ( $c == ' ' || $c == '>' || $c == '/' ) ) break ;
				$value .= htmlspecialchars ( $c ) ;
				$b++ ;
			}
		}
		if ( $name == "" ) return true ;

		$a = $b ;
		if ( $q == "'" ) $q = "'" ;
		else $q = '"' ;
		$xml = "{$name}={$q}{$value}{$q}" ;
		#$xml .= "<attr name='{$name}'>{$value}</attr>" ;
		return true ;
	}

	# Horizontal ruler (<hr> / ----)
	function p_hr ( &$a , &$xml )
	{
		#echo("<br>w2x_41");
		if ( !$this->nextis ( $a , "----" ) ) return false ;
		$this->skipblanks ( $a , "-" ) ;
		$this->skipblanks ( $a ) ;
		$xml .= "<hr/>" ;
		return true ;
	}

	# TABLE
	# Scans the rest of the line as HTML attributes and returns the usual <attrs><attr> string
	function scanattributes ( &$a )
	{
		#echo("<br>w2x_42");
		$x = "" ;
		while ( $a < $this->wl )
		{
			if ( (mb_substr ( $this->w , $a , 1 , 'UTF-8' )) == "\n" ) break ;
			$x .= (mb_substr ( $this->w , $a , 1 , 'UTF-8' )) ;
			$a++ ;
		}
		$x .= ">" ;

		$attrs = $this->preparse_attributes ( $x ) ;

		$ret = "" ;
		if ( count ( $attrs ) > 0 )
		{
			#$ret .= "<attrs>" ;
			$ret .= " " . implode ( " " , $attrs ) ;
			#$ret .= "</attrs>" ;
		}
		return $ret ;
	}

	# Finds the first of the given items; does *not* alter $a
	function scanahead ( $a , $matches )
	{
		#echo("<br>w2x_43");
		while ( $a < $this->wl )
		{
			foreach ( $matches AS $x )
			{
				if ( $this->nextis ( $a , $x , false ) )
				{
					return $a ;
				}
			}
			$a++ ;
		}
		return -1 ; # Not found
	}


	# The main table parsing function
	function p_table ( &$a , &$xml )
	{
		#echo("<br>w2x_44");
		if ( $a >= $this->wl ) return false ;
		$c = (mb_substr ( $this->w , $a , 1 , 'UTF-8' )) ;
		if ( $c == "{" && $this->nextis ( $a , "{|" , false ) )
			return $this->p_table_open ( $a , $xml ) ;

			#		print "p_table for " . htmlentities ( mb_substr ( $this->w , $a ) ) . "<br/><br/>" ; flush () ;

			if ( count ( $this->tables ) == 0 ) return false ; # No tables open, nothing to do

			# Compatability for table cell lines starting with blanks; *evil MediaWiki parser!*
			$b = $a ;
			$this->skipblanks ( $b ) ;
			if ( $b >= $this->wl ) return false ;
			$c =(mb_substr ( $this->w , $b , 1 , 'UTF-8' )) ;

			if ( $c != "|" && $c != "!" ) return false ; # No possible table markup

			if ( $c == "|" && $this->nextis ( $b , "|}" , false ) ) return $this->p_table_close ( $b , $xml ) ;

			#if ( $this->nextis ( $a , "|" , false ) || $this->nextis ( $a , "!" , false ) )
			return $this->p_table_element ( $b , $xml , true ) ;
	}

	function lasttable ()
	{
		#echo("<br>w2x_45");
		return $this->tables[count($this->tables)-1] ;
	}

	# Returns the attributes for table cells
	function tryfindparams ( &$a )
	{
		#echo("<br>w2x_46");
		$n = strspn ( $this->w , $this->allowed , $a ) ; # PHP 4.3.0 and above
		#		$n = strspn ( mb_substr ( $this->w , $a ) , $this->allowed ) ; # PHP < 4.3.0
		if ( $n == 0 ) return "" ; # None found

		$b = $a + $n ;
		if ( $b >= $this->wl ) return "" ;
		if ( (mb_substr ( $this->w , $b , 1 , 'UTF-8' )) != "|" && (mb_substr ( $this->w , $b , 1 , 'UTF-8' )) != "!" ) return "" ;
		if ( $this->nextis ( $b , "||" , false ) ) return "" ; # Reached a ||, so return blank string
		if ( $this->nextis ( $b , "!!" , false ) ) return "" ; # Reached a ||, so return blank string
		$this->w[$b] = "\n" ; //
		$ret = $this->scanattributes ( $a ) ;
		$this->w[$b] = "|" ; //
		$a = $b + 1 ;
		return $ret ;
	}

	function p_table_element ( &$a , &$xml , $newline = false )
	{
		#echo("<br>w2x_47");
		#		print "p_table_element for " . htmlentities ( mb_substr ( $this->w , $a ) ) . "<br/><br/>" ; flush () ;
		$b = $a ;
		$this->skipblanks ( $b ) ; # Compatability for table cells starting with blanks; *evil MediaWiki parser!*
		if ( $b >= $this->wl ) return false ; # End of the game
		$x = "" ;
		if ( $newline && $this->nextis ( $b , "|-" ) ) # Table row
		{
			$this->skipblanks ( $b , "-" ) ;
			$this->skipblanks ( $b ) ;

			$attrs = $this->scanattributes ( $b ) ;
			if ( $this->tables[count($this->tables)-1]->is_row_open ) $x .= "</tablerow>" ;
			else $this->tables[count($this->tables)-1]->is_row_open = true ;
			$this->tables[count($this->tables)-1]->had_row = true ;
			$x .= "<tablerow{$attrs}>" ;
			$y = "" ;
			$this->p_restofcell ( $b , $y ) ;
		}
		else if ( $newline && $this->nextis ( $b , "|+" ) ) # Table caption
		{
			$this->skipblanks ( $b ) ;
			$attrs = $this->tryfindparams ( $b ) ;
			$this->skipblanks ( $b ) ;
			if ( $this->tables[count($this->tables)-1]->is_row_open ) $x .= "</tablerow>" ;
			$this->tables[count($this->tables)-1]->is_row_open = false ;

			$y = "" ;
			if ( !$this->p_restofcell ( $b , $y ) ) return false ;
			$x .= "<tablecaption{$attrs}>{$y}</tablecaption>" ;
		}
		else # TD or TH
		{
			$c = (mb_substr ( $this->w , $b , 1 , 'UTF-8' )) ;
			$b++ ;
			$tag = "error" ;
			if ( $c == '|' ) $tag = "tablecell" ;
			else if ( $c == '!' ) $tag = "tablehead" ;
			$attrs = $this->tryfindparams ( $b ) ;
			$this->skipblanks ( $b ) ;
			if ( !$this->p_restofcell ( $b , $x ) ) return false ;

			if ( mb_substr ( $x , 0 , 1 ) == "|" ) # Crude fix to compensate for MediaWiki "tolerant" parsing
				$x = mb_substr ( $x , 1 ) ;
				$x = "<{$tag}{$attrs}>{$x}</{$tag}>" ;
				$this->tables[count($this->tables)-1]->had_cell = true ;
				if ( !$this->tables[count($this->tables)-1]->is_row_open )
				{
					$this->tables[count($this->tables)-1]->is_row_open = true ;
					$this->tables[count($this->tables)-1]->had_row = true ;
					$x = "<tablerow>{$x}" ;
				}
		}

		$a = $b ;
		$xml .= $x ;
		return true ;
	}

	# Finds the mb_substring that composes the table cell,
	# then runs a new parser on it
	function p_restofcell ( &$a , &$xml )
	{
		#echo("<br>w2x_48");
		# Get mb_substring for cell
		$b = $a ;
		$sameline = true ;
		$x = "" ;
		$itables = 0 ;
		while ( $b < $this->wl )
		{
			$c = (mb_substr ( $this->w , $b , 1 , 'UTF-8' )) ;
			if ( $c == "<" && $this->once ( $b , $x , "html" ) ) continue ; # Up front to catch pre and nowiki
			if ( $c == "\n" ) { $sameline = false ; }
			if ( $c == "\n" && $this->nextis ( $b , "\n{|" ) ) { $itables++ ; continue ; }
			if ( $c == "\n" && $itables > 0 && $this->nextis ( $b , "\n|}" ) ) { $itables-- ; continue ; }

			if ( ( $c == "\n" && $this->nextis ( $b , "\n|" , false ) ) OR
				 ( $c == "\n" && $this->nextis ( $b , "\n!" , false ) ) OR
				 ( $c == "\n" && $this->nextis ( $b , "\n |" , false ) ) OR # MediaWiki parser madness compensator
				 ( $c == "\n" && $this->nextis ( $b , "\n !" , false ) ) OR # MediaWiki parser madness compensator
				 ( $c == "|" && $sameline && $this->nextis ( $b , "||" , false ) ) OR
				 ( $c == "!" && $sameline && $this->nextis ( $b , "!!" , false ) ) )
			{
				if ( $itables == 0 ) break ;
				$b += 2 ;
			}

			if ( $c == "[" && $this->once ( $b , $x , "internal_link" ) ) continue ;
			if ( $c == "{" && $this->once ( $b , $x , "template_variable" ) ) continue ;
			if ( $c == "{" && $this->once ( $b , $x , "template" ) ) continue ;
			$b++ ;
		}

		#		if ( $itables > 0 ) return false ;

		# Parse cell mb_substring
		$s = mb_substr ( $this->w , $a , $b - $a ) ;
		$p = new wiki2xml ;
		$p->inherit ( $this ) ;
		$x = $p->parse ( $s ) ;
		if ( $x == $this->errormessage ) return false ;

		$a = $b + 1 ;
		$xml .= $this->strip_single_paragraph ( $x ) ;
		return true ;
	}

	function p_table_close ( &$a , &$xml )
	{
		#echo("<br>w2x_49");
		if ( count ( $this->tables ) == 0 ) return false ;
		$b = $a ;
		if ( !$this->nextis ( $b , "|}" ) ) return false ;
		if ( !$this->tables[count($this->tables)-1]->had_row ) return false ; # Table but no row was used
		if ( !$this->tables[count($this->tables)-1]->had_cell ) return false ; # Table but no cell was used
		$x = "" ;
		if ( $this->tables[count($this->tables)-1]->is_row_open ) $x .= "</tablerow>" ;
		unset ( $this->tables[count($this->tables)-1] ) ;
		$x .= "</table>" ;
		$xml .= $x ;
		$a = $b ;
		while ( $this->nextis ( $a , "\n" ) ) ;
		return true ;
	}

	function p_table_open ( &$a , &$xml )
	{
		#echo("<br>w2x_50");
		$b = $a ;
		if ( !$this->nextis ( $b , "{|" ) ) return false ;

		$this->is_row_open = false ;

		# Add table to stack
		$nt = new stdClass();
		$nt->is_row_open = false ;
		$nt->had_row = false ;
		$nt->had_cell = false ;
		$this->tables[count($this->tables)] = $nt ;

		$x = "<table" ;
		$x .= $this->scanattributes ( $b ) . ">" ;
		while ( $this->nextis ( $b , "\n" ) ) ;

		while ( !$this->p_table_close ( $b , $x ) )
		{
			if ( $b >= $this->wl )
			{
				unset ( $this->tables[count($this->tables)-1] ) ;
				return false ;
			}
			if ( $this->p_table_open ( $b , $x ) ) continue ;
			if ( !$this->p_table_element ( $b , $x , true ) ) # No |} and no table element
			{
				unset ( $this->tables[count($this->tables)-1] ) ;
				return false ;
			}
		}
		$a = $b ;
		$xml .= $x ;
		return true ;
	}

	#-----------------------------------
	# Parse the article
	function p_article ( &$a , &$xml )
	{
		#echo("<br>w2x_51");
		$x = "" ;
		$b = $a ;
		while ( $b < $this->wl )
		{
			if ( $this->onceormore ( $b , $x , "heading" ) ) continue ;
			if ( $this->onceormore ( $b , $x , "block_lines" ) ) continue ;
			if ( $this->onceormore ( $b , $x , "block_pre" ) ) continue ;
			if ( $this->onceormore ( $b , $x , "block_list" ) ) continue ;
			if ( $this->onceormore ( $b , $x , "hr" ) ) continue ;
			if ( $this->onceormore ( $b , $x , "table" ) ) continue ;
			if ( $this->onceormore ( $b , $x , "blankline" ) ) continue ;
			if ( $this->p_block_lines ( $b , $x , true ) ) continue ;
			# The last resort! It should never come to this!
			if ( !$this->compensate_markup_errors ) $xml .= "<error type='general' reason='no matching markup'/>" ;
			$xml .= htmlspecialchars ( (mb_substr ( $this->w , $b , 1 , 'UTF-8' ))) ;
			$b++ ;
		}
		$a = $b ;
		$xml .= $x ;

		#		asort ( $this->profile ) ;
		#		$p = "" ;
		#		foreach ( $this->profile AS $k => $v ) $p .= "<p>{$k} : {$v}</p>" ;
		#		$xml = "<debug>{$this->cnt}{$p}</debug>" . $xml ;
		return true ;
	}

	# The only function to be called directly from outside the class
	function parse ( &$wiki )
	{

		set_time_limit(603);
		#echo("<br>w2x_52");
		global $IP;

		$this->w = rtrim ( $wiki ) ;

		/*
		 # replace old index terms
		 $this->w = preg_replace ('/(\{\{#index:)(.+)(\}\})/i','',$this->w);

		 # replace math tags
		 $matches=array();
		 $pattern = '@(<math)(.*?)(>)(.*?)(</math>)@s';
		 preg_match_all ($pattern, $this->w, $matches);
		 $mathtags=$matches[4];
		 $i=0;
		 foreach($mathtags as $mathtag) {
			wfDebug( __METHOD__ . ': mathtag :'.print_r($mathtag,true).":\n");
			$math = new MathTexvc('\pagecolor{White}'.$mathtag);
			$math->render('rgb 1.0 1.0 1.0');
			$mathpath=$math->getMathImageUrl();
			wfDebug( __METHOD__ . ': mathpath :'.print_r($mathpath,true).":\n");
			$imagepath=$IP.str_replace('mediawiki/','',$mathpath);
			wfDebug( __METHOD__ . ': imagepath :'.print_r($imagepath,true).":\n");
			if (file_exists ( $imagepath)) {
			$return='<mathimage path="'.$imagepath.'"></mathimage>';
			} else {
			$return= 'Error parsing math';
			}
			$this->w = str_replace ( $matches[0][$i] , $return , $this->w ) ;
			$i++;
			}
			*/
		# Fix line endings
		$cc = count_chars ( $wiki , 0 ) ;
		if ( $cc[10] > 0 && $cc[13] == 0 )
			$this->w = str_replace ( "\r" , "\n" , $this->w ) ;
			$this->w = str_replace ( "\r" , "" , $this->w ) ;
			$this->w = str_replace ( "\n" , " \n" , $this->w ) ;

			# Remove HTML comments
			#    $this->w = str_replace ( "\n<!--" , "<!--" , $this->w ) ;
			$this->w= preg_replace('/\n<!--(.|\s)*?-->\n/', "<!-- --> ", $this->w);
			$this->w= preg_replace('/<!--(.|\s)*?-->/', '', $this->w);
			$this->w= preg_replace('/<!--(.|\s)*$/', '', $this->w);

			# deal with extension EmbedVideo
			$this->w= preg_replace('/(\{\{)(#ev:)(dailymotion|divshare|edutopia|funnyordie|googlevideo|interiavideo|interia|revver|sevenload|teachertube|youtube|youtubehd|vimeo)(\|)((.|\s)*?)(\}\})/', '<embed_video service=$3 videoid=$5></embed_video>', $this->w);



			# Run the thing!
			#		$this->tables = array () ;
			$this->wl = strlen ( $this->w ) ;
			$xml = "" ;
			$a = 0 ;
			if ( !$this->p_article ( $a , $xml ) ) return $this->errormessage ;


			# XML cleanup
			$ol = -1 ;
			while ( $ol != strlen ( $xml ) ) {
				$ol = strlen ( $xml ) ;
				$xml = str_replace ( "<preline> " , "<preline><space/>" , $xml ) ;
				$xml = str_replace ( "<space/> " , "<space/><space/>" , $xml ) ;
			}
			$ol = -1 ;
			while ( $ol != strlen ( $xml ) ) {
				$ol = strlen ( $xml ) ;
				$xml = str_replace ( "  " , " " , $xml ) ;
			}
			$ol = -1 ;
			while ( $this->use_space_tag && $ol != strlen ( $xml ) ) {
				$ol = strlen ( $xml ) ;
				$xml = str_replace ( "> " , "><space/>" , $xml ) ;
				$xml = str_replace ( " <" , "<space/><" , $xml ) ;
			}
			$xml = str_replace ( '<tablerow></tablerow>' , '' , $xml ) ;

			return $xml ;
	}

}
