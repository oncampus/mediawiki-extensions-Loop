<?php

class LoopHtml{

    public static function structureToHtml(LoopStructure $loopStructure) {

        $loopStructureItems = $loopStructure->getStructureItems();

        if($loopStructureItems) {
            foreach($loopStructureItems as $loopStructureItem) {
                dd($loopStructureItem);
            }
        }

    }

}
