<?php
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This file cannot be run standalone.\n" );
}


class LoopNoprint {
	var $input='';
	var $args=array();	
    
    public static function onParserSetup( Parser $parser) {
        $parser->setHook( 'loop_noprint', 'LoopNoprint::renderLoopNoprint' );

        /*global $wgParser, $wgTitle, $wgParserConf, $wgUser;
		
		$this->input=$input;
		$this->args=$args;		*/
		
		return true;
    }
    
    /*
	function LoopNoprint($input,$args) {
		global $wgParser, $wgTitle, $wgParserConf, $wgUser;
		
		$this->input=$input;
		$this->args=$args;		
		
		return true;
	}
	*/

	static function renderLoopNoprint( $input, array $args, Parser $parser, PPFrame $frame ) {
		//global $wgStylePath, $wgParser;
		
		$return='';
		$noprint_id=uniqid();
		$return.='<div class="noprintarea" id="'.$noprint_id.'">';
		$output = $parser->recursiveTagParse( $input);
		$return.= $output;
		$return.= '</div>';
		
		return $return;
	}	

}
