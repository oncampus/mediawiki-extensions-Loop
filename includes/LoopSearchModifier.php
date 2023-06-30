<?php

if (!defined('MEDIAWIKI')) {
	die();
}

$wgHooks['SearchGetNearMatch'][] = 'wfAddWildcardToSearchTerm';

function wfAddWildcardToSearchTerm($term, &$termRegex) {
	$termRegex = '.*' . $termRegex . '.*';
	return true;
}
