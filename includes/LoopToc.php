<?php
/**
 * @description Adds TOC
 * @ingroup Extensions
 * @author Dustin Neß <dustin.ness@th-luebeck.de>, Dennis Krohn @krohnden <dennis.krohn@th-luebeck.de>
 */

if ( !defined( 'MEDIAWIKI' ) ) die ( "This file cannot be run standalone.\n" );

use MediaWiki\MediaWikiServices;

class LoopToc extends LoopStructure {

    static function onParserSetup( Parser $parser ) {
        $parser->setHook( 'loop_toc', 'LoopToc::renderLoopToc' );
		return true;
    }

	static function renderLoopToc( $input, array $args, Parser $parser, PPFrame $frame ) {
		
        global $wgOut;
        
		$result = self::outputLoopToc( $wgOut->getTitle()->mArticleID, "html" );

        $return  = '<div class="looptoc">';
        $return .= $result;
        $return .= '</div>';
        return $return;
    }
    
    public static function outputLoopToc( $rootArticleId, $output = "html" ) {

		$html = '';
		$xml = '';
		$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
		$linkRenderer->setForceArticlePath(true);

		$lsi = LoopStructureItem::newFromIds( $rootArticleId );
		if ( $lsi ) {
			$level = $lsi->getTocLevel();
			$tocText = $lsi->getTocText();
			$next = $lsi->getNextItem();
			$tocNumber =  $lsi->getTocNumber();
			
			$headLink = $linkRenderer->makeLink(
				Title::newFromID( $lsi->article ),
				new HtmlArmor( '<span class="loopstructure-number">' . $tocNumber .'</span> ' . $tocText )
			);
            $html .= '<div class="loopstructure-listitem loopstructure-level-' . $level . '">' . $headLink . '</div>';
            $xml .= '<loop_toc_list><php_link_internal text-decoration="no-underline" href="article'.$rootArticleId.'"><bold>'. $tocNumber .'</bold>  '. $tocText . '</php_link_internal></loop_toc_list>';
			
			while ( !empty ( $next ) ) {
				$tmp_lsi = $next;
				if ( $tmp_lsi->getTocLevel() == $level + 1 ) { # if next item toclevel is one higher than current level, add to output
					if ( empty( $tocNumber ) || strpos ( $tmp_lsi->tocNumber, $tocNumber ) === 0 ) { # the root page's toc number must be inside the displayed toc number
						$next = $tmp_lsi->getNextItem();
						
						$link = $linkRenderer->makeLink(
							Title::newFromID( $tmp_lsi->article ),
							new HtmlArmor( '<span class="loopstructure-number">' . $tmp_lsi->tocNumber .'</span> ' . $tmp_lsi->tocText )
						);
						$html .= '<div class="loopstructure-listitem loopstructure-level-' . $tmp_lsi->tocLevel . '">' . $link . '</div>';
                        $xml .= '<loop_toc_list><php_link_internal text-decoration="no-underline" href="article'.$tmp_lsi->article.'"><bold>'. $tmp_lsi->tocNumber .'</bold>  '. $tmp_lsi->tocText . '</php_link_internal></loop_toc_list>';

					} else {
						break;
					}
				} 
				$next = $tmp_lsi->getNextItem();
			}
		}

        if ( $output == "html" ) {
            $return = $html;
        } elseif ( $output == "xml" ) {
            $return = $xml;
        }

		return $return;
	}


}