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
		#return true;
		$loopStructure = new LoopStructure();
		$loopStructure->loadStructureItems();
        $script = self::addLiteratureScript($loopStructure);
		#dd($script);
		$outputPage->addHTML("<script>$script</script>");
	}

    public static function addLiteratureScript( $loopStructure ) {

        $literature = LoopLiterature::getAllItems( $loopStructure );
        $objects = LoopObjectIndex::getAllObjects ( $loopStructure );


        foreach ( $objects as $refid => $data) {
            $output[$data["index"]][$refid] = wfMessage($data["index"]."-name-short")->text() . " " . $data["objectnumber"];
            $output[$data["index"]][$refid] .= ( isset( $data["title"] ) ) ? ": " . $data["title"] : "";
            #dd( $data);
        }

        $script = "";
       
        foreach ( $output as $index => $data ) {
            $script .= "var $index = {\n";
                
                foreach ( $data as $refid => $text ) {
                    $script .= "'$refid' : '$text',\n";
                }
                
            $script .= "}\n";
        }
        $script .= "var loop_literature = {\n";
        if ( !empty ( $literature ) ) {
            foreach ( $literature as $key => $val ) {
                $text = LoopLiterature::renderLiteratureElement( $val, null, "wikieditor" );
                $script .= "'$key' : '$text',\n";
            }
        }
        $script .= "}";

        #dd($script, $objects, $output);
        return $script;
    }

}