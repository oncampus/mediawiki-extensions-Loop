<?php
/**
  * @description Makes loop_objects referencable
  * @ingroup Extensions
  * @author Dennis Krohn <dennis.krohn@th-luebeck.de>
  */

if ( !defined( 'MEDIAWIKI' ) ) die ( "This file cannot be run standalone.\n" );

use MediaWiki\MediaWikiServices;

class LoopReference {
    public static function onParserSetup(Parser $parser) {
		$parser->setHook ( 'loop_reference', 'LoopReference::renderLoopReference' );
		return true;
    }

	/**
	 *
	 * @param string $input
	 * @param array $args
	 * @param Parser $parser
	 * @param Frame $frame
	 * @return string
	 */
	public static function renderLoopReference($input, array $args, $parser, $frame) {

		global $wgLoopObjectNumbering;

		$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
		$linkRenderer->setForceArticlePath(true);

		$loopStructure = new LoopStructure();
		$loopStructure->loadStructureItems();
		$html = '';
		$linkTitle = '';
		$linkTitleAttr = '';
		try {
			if ( isset($args["id"]) ) {
				$refId = $args["id"];
				$objectData = LoopObjectIndex::getObjectData( $refId );
			} else {
				throw new LoopException( wfMessage( 'loopreference-error-no-refid' )->text());
			}

			if ( ! $objectData ) {
				throw new LoopException( wfMessage( 'loopreference-error-unknown-refid', $refId )->text());
			}
			# with title = true args or no numbering, the title is shown in link.
			if ( (isset($args["title"]) && strtolower($args["title"]) == "true") || !$wgLoopObjectNumbering ) {
				$showTitle = true;
			} elseif( !isset($args["title"]) || strtolower($args["title"]) == "false" ) {
				$showTitle = false;
			} else {
				$showTitle = false;
				throw new LoopException( wfMessage ( "loop-error-unknown-param", "<loop_reference>", "title", $args["title"], "true, false", 'false' )->text() );
			}
		} catch ( LoopException $e ) {
			$parser->addTrackingCategory( 'loop-tracking-category-error' );
			$html = $e . $html;
		}
		if ( isset($objectData) && $objectData ) {

			$lsi = LoopStructureItem::newFromIds ( $objectData["articleId"] );
			if ( !empty( $input ) ) {
				$linkTitle .= $parser->recursiveTagParse ( $input, $frame );
				if ( $wgLoopObjectNumbering ) {
					if ( $lsi ) {
						$pageData = array( "structure", $lsi, $loopStructure );
						$linkTitleAttr = wfMessage ( $objectData["index"].'-name-short' )->inContentLanguage ()->text () . " ";
						$linkTitleAttr .= LoopObject::getObjectNumberingOutput($refId, $pageData, null, $objectData );
						$linkTitleAttr .= " ".$objectData["title"];
					}
				}
			} else {
				$linkTitle .= wfMessage ( $objectData["index"].'-name-short' )->inContentLanguage ()->text () . " ";
				if ( $wgLoopObjectNumbering ) {
					if ( $lsi ) {

						$pageData = array( "structure", $lsi, $loopStructure );
						$linkTitle .= LoopObject::getObjectNumberingOutput($refId, $pageData, null, $objectData );
					} else { # glossary
						$title = Title::newFromId($objectData["articleId"]);
						if ( $title->getNamespace() === NS_GLOSSARY ) {
							$pageData = array( "glossary", $objectData["articleId"] );
							$previousObjects = LoopObjectIndex::getObjectNumberingsForGlossaryPage ( $objectData["articleId"] );
							$linkTitle .= LoopObject::getObjectNumberingOutput( $refId, $pageData, $previousObjects, $objectData);
						}
					}
				}

				if ( $showTitle ) {
					$linkTitle .= " ".$objectData["title"];
				}
				$linkTitleAttr = htmlspecialchars($linkTitle);
			}
			$html .= $linkRenderer->makelink(
				Title::newFromId($objectData["articleId"]),
				new HtmlArmor( $linkTitle ),
				array(
					"class" => "loop-reference",
					"title" => $linkTitleAttr,
					"alt" => $linkTitleAttr,
					"data-target" => $refId # target id will be added in hook
					)
			);
		} else {
			$html .= $parser->recursiveTagParse ( $input, $frame );
		}

		return  $html;
	}
}
