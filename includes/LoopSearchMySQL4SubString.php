<?php
/**
 * Search engine hook for MySQL 4+
 * @package MediaWiki
 * @subpackage Search
 */

require_once( '/../../includes/SearchEngine.php' );
require_once( 'SearchMySQL.php' );

/**
 * @package MediaWiki
 * @subpackage Search
 */
class SearchMySQL4SubString extends SearchMySQL {
	var $strictMatching = true;

	/** @todo document */
	function SearchMySQL4SubString( &$db ) {
		$this->db =& $db;
	}

	function legalSearchChars() {
		return "A-Za-z_'0-9\\x80-\\xFF\\-*?+";
	}

	/** @todo document */
	function parseQuery( $filteredText, $fulltext ) {
		global $wgContLang;
		$lc = SearchEngine::legalSearchChars();
		$searchon = '';
		$this->searchTerms = array();

		wfDebug( "parseQuery filteredText is: '$filteredText'\n" );
		wfDebug( "parseQuery fulltext is: '$fulltext'\n" );

		# FIXME: This doesn't handle parenthetical expressions.
		if( preg_match_all( '/([-+<>~]?)(([' . $lc .
			']+)(\*?)|"[^"]*")/',
			$filteredText, $m, PREG_SET_ORDER ) ) {
			foreach( $m as $terms ) {
				if( $searchon !== '' ) $searchon .= ' ';
				$searchon .= $terms[1] .
					$wgContLang->stripForSearch( $terms[2] );
				if( !empty( $terms[3] ) ) {
					$regexp = preg_quote( $terms[3], '/' );
					if( $terms[4] ) $regexp .= "[0-9A-Za-z_]+";
				} else {
					$regexp = preg_quote( str_replace( '"', '',
						$terms[2] ), '/' );
				}
				$this->searchTerms[] = $regexp;
			}
			wfDebug( "Would search with '$searchon'\n" );
			wfDebug( "Match with /\b" . implode( '\b|\b',
					$this->searchTerms ) . "\b/\n" );
		} else {
			wfDebug( "Can't understand search query
'{$this->filteredText}'\n" );
		}

		$searchon = $this->db->strencode( $searchon );
		$field = $this->getIndexField( $fulltext );
		return " MATCH($field) AGAINST('$searchon' IN BOOLEAN MODE) ";
	}
}
?>
