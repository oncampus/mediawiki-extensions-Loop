<?php
/**
  * @description Hide <loop_noprint> content from PDF.
  * @ingroup Extensions
  * @author Dustin Neß <dustin.ness@th-luebeck.de>
  */

if ( !defined( 'MEDIAWIKI' ) ) die ( "This file cannot be run standalone.\n" );

class LoopNoprint {
	var string $input='';
	var array $args=array();

    public static function onParserSetup( Parser $parser): bool
	{
        $parser->setHook( 'loop_noprint', 'LoopNoprint::renderLoopNoprint' );

		return true;
    }

	static function renderLoopNoprint( $input, array $args, Parser $parser, PPFrame $frame ): string
	{
		$return='';
		$noprint_id=uniqid();
		$return.='<div class="noprintarea" id="'.$noprint_id.'">';
		$output = $parser->recursiveTagParse( $input);
		$return.= $output;
		$return.= '</div>';

		return $return;
	}

}
