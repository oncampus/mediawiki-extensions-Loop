<?php

/**
 * @description Handles customizing of the WikiEditor
 * @author Dennis Krohn <dennis.krohn@th-luebeck.de>
 */

if ( !defined( 'MEDIAWIKI' ) ) {
    die( "This file cannot be run standalone.\n" );
}

class LoopWikiEditor {

	public static function onEditPageShowEditFormInitial ( EditPage $editPage, OutputPage $outputPage ) {

		$outputPage->addModules("loop.wikiEditor.js");
		$loopStructure = new LoopStructure();
		$loopStructure->loadStructureItems();
        $script = self::addLiteratureScript($loopStructure);
		$outputPage->addHTML("<script>$script</script>");
	}

    public static function addLiteratureScript( $loopStructure ) {

        $literature = LoopLiterature::getAllItems( $loopStructure );
        $objects = LoopObjectIndex::getAllObjects ( $loopStructure );
        $output = array();
        foreach ( $objects as $refid => $data) {
            $output[$data["index"]][$data["objectnumber"] ."::". $data["id"] ] = wfMessage($data["index"]."-name-short")->text() . " " . $data["objectnumber"];
            $output[$data["index"]][$data["objectnumber"] ."::". $data["id"] ] .= ( isset( $data["title"] ) ) ? ": " . $data["title"] : "";
            ksort( $output[$data["index"]], SORT_NUMERIC );
        }

        $script = "var loop_elements = {\n";
        
        foreach ( $output as $index => $data ) {
            $script .= "$index : {\n";
                
                foreach ( $data as $refid => $text ) {
                    $text = str_replace("\\", " ", $text);
                    $text = str_replace("'", "\'", $text);
                    $script .= "'$refid' : '$text',\n";
                }
                
            $script .= "},\n";
        }
        if ( !empty ( $literature ) ) {
            $script .= "loop_literature : {\n";
            foreach ( $literature as $key => $val ) {
                $text = LoopLiterature::renderLiteratureElement( $val, null, "wikieditor" );
                $text = str_replace("\\", " ", $text);
                $text = str_replace("'", "\'", $text);
                $script .= "'$key' : '$text',\n";
            }
            $script .= "}";
        }
        $script .= "}";
        
        return $script;
    }

}