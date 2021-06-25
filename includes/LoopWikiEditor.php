<?php

/**
 * @description Handles customizing of the WikiEditor
 * @author Dennis Krohn <dennis.krohn@th-luebeck.de>
 */

if ( !defined( 'MEDIAWIKI' ) ) die ( "This file cannot be run standalone.\n" );

class LoopWikiEditor {

	public static function onEditPageShowEditFormInitial ( EditPage $editPage, OutputPage $outputPage ) {

		$outputPage->addModules("loop.wikiEditor.js");
		$loopStructure = new LoopStructure();
		$loopStructure->loadStructureItems();
        $script = self::addLiteratureScript($loopStructure);
		$outputPage->addHTML("<script>$script</script>");
	}

    public static function addLiteratureScript( $loopStructure ) {
		global $wgLoopEditorHideReferences;

		if ($wgLoopEditorHideReferences) {
			return "var loop_elements = {};";
		}
        $literature = LoopLiterature::getAllItems( $loopStructure );
        $objects = LoopObjectIndex::getAllObjects ( $loopStructure );
        $output = array();
        foreach ( $objects as $refid => $data) {
            $output[$data["index"]][( empty( $data["objectnumber"] ) ? "999999" : $data["objectnumber"] ) ."::". $data["id"] ] = wfMessage($data["index"]."-name-short")->text() . ( !empty( $data["objectnumber"] ) ? " " . $data["objectnumber"] : "" );
            $output[$data["index"]][( empty( $data["objectnumber"] ) ? "999999" : $data["objectnumber"] ) ."::". $data["id"] ] .= ( isset( $data["title"] ) ) ? ": " . html_entity_decode ( $data["title"] ) : "";
            ksort( $output[$data["index"]], SORT_NUMERIC ); # put items without object number to the end of the list
        }
        $script = "var loop_elements = {\n";

        foreach ( $output as $index => $data ) {
            $script .= "$index : {\n";

                foreach ( $data as $refid => $text ) {
                    $text = str_replace("\\", " ", $text);
                    $text = str_replace("'", "\'", $text);
                    $text = str_replace("\n", " ", $text);
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
				$text = str_replace("\n", " ", $text);
                $key = str_replace("\\", " ", $key);
                $key = json_encode($key);
                $script .= "$key : '$text',\n";
            }
            $script .= "}";
        }
        $script .= "}";

        return $script;
    }

}
