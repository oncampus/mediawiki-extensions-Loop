<?php

/**
 * @description A parser extension that adds the tag <loop_task> to mark content as task and provide a table of tasks
 * @ingroup Extensions
 * @author Marc Vorreiter @vorreiter <marc.vorreiter@th-luebeck.de>
 * @author Dennis Krohn @krohnden <dennis.krohn@th-luebeck.de>
 */
if (!defined('MEDIAWIKI')) die("This file cannot be run standalone.\n");

use MediaWiki\MediaWikiServices;

class LoopTags
{
    public static function onPageSaveComplete($wikiPage)
    {
        $contentText = ContentHandler::getContentText($wikiPage->getContent());
        return self::extractUsedTags($contentText, $wikiPage->getTitle());
    }


    /**
     * Call to be used, when the page just needs to be scanned manually
     * @param Title title the title object, from which one can get the content
     */
    public static function savePageUsingTitle($title)
    {
        $wikiPage = MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle($title);
        $contentText = ContentHandler::getContentText($wikiPage->getContent());
        return self::extractUsedTags($contentText, $title);
    }

    /**
     * Extracts used tags and saves them in the db
     * @param string $text 
     */
    public static function extractUsedTags($text, $title)
    {
        // Get tags from text
        $html_pattern = "/<[^\/].+?[>||\s]/"; // Pattern to get the tags as "<tag "
        preg_match_all($html_pattern, $text, $htmlTags);
        $tagArray = [];
        foreach ($htmlTags[0] as $tag) {
            // We remove the < at the beginning and the " " at the end
            array_push($tagArray, substr($tag, 1, strlen($tag) - 2));
        }
        if (!empty($tagArray)) {
            // remove duplicates
            $tagArray = array_unique($tagArray);
            // get ID from title
            $articleID = $title->getArticleID();
            self::saveTagsToDB($tagArray, $articleID);
        }
        return true;
    }

    /**
     * Function to insert Tags into the DB
     * We will use "##" as the split symbol in the db, so we need just one entry per article
     * @param array $tagArray array full of strings with the used tags
     * @param string $articleID corresponding ID (can be taken from the $title object)
     */
    protected static function saveTagsToDB($tagArray, $articleID)
    {
        // Get DB Connection
        $dbr = wfGetDB(DB_REPLICA);
        // look for already existing entry
        $result = $dbr->select(
            array(
                'loop_used_tags'
            ),
            array(
                'ltu_tags_used'
            ),
            array(
                'ltu_article_id = ' . $articleID
            ),
            __METHOD__
        );
        // If one already exists, we update the entry
        if (count($result) > 0) {
            // simplest way to get the entry:
            foreach ($result as $res) {
                // we also check, if the value is the same one
                if ($res->ltu_tags_used != implode("##", $tagArray)) {
                    // we found something, so establish a connection
                    $dbw = wfGetDB(DB_PRIMARY);
                    $dbw->update(
                        'loop_used_tags',
                        array(
                            'ltu_tags_used = \'' . implode("##", $tagArray) . '\'',
                        ),
                        array(
                            'ltu_article_id = ' . $articleID,
                        ),
                        __METHOD__
                    );
                }
                return true;
            }
        } else { // else we insert a new one
            // get a write connection
            $dbw = wfGetDB(DB_PRIMARY);
            $dbw->insert(
                'loop_used_tags',
                array(
                    'ltu_article_id' => $articleID,
                    'ltu_tags_used' => implode("##", $tagArray)
                ),
                __METHOD__
            );
            return true;
        }
    }

    /**
     * When deleting a page, remove all Reference entries from DB.
     * Attached to ArticleDeleteComplete hook.
     */
    public static function onArticleDeleteComplete(&$article, User &$user, $reason, $id, $content, LogEntry $logEntry, $archivedRevisionCount)
    {
        self::removeFromDatabase($id);

        return true;
    }

    // deletes all tag references of a page
    public static function removeFromDatabase($articleID)
    {
        $dbw = wfGetDB(DB_PRIMARY);
        $dbw->delete(
            'loop_used_tags',
            'ltu_article_id = "' . $articleID . '"',
            __METHOD__
        );
        return true;
    }

    /**
     * Returns all used tags from the db as [id]=>[array of tags]
     */
    public static function getAllUsedTags()
    {
        $dbr = wfGetDB(DB_PRIMARY);
        $result = $dbr->select(
            'loop_used_tags',
            array('ltu_article_id', 'ltu_tags_used')
        );
        $returnTags = [];
        foreach ($result as $res) {
            $returnTags[$res->ltu_article_id] = explode('##', $res->ltu_tags_used);
        }
        return $returnTags;
    }

    /**
     * Returns true, if the DB is empty
     */
    private static function checkIfEmpty()
    {
        return empty(self::getAllUsedTags());
    }
}

class SpecialLoopTags extends SpecialPage
{
    public function __construct()
    {
        parent::__construct('LoopTags');
    }

    public function execute($sub)
    {

        $out = $this->getOutput();
        $request = $this->getRequest();
        $user = $this->getUser();
        Loop::handleLoopRequest($out, $request, $user); #handle editmode
        $out->addModules('loop.special.tags.js');

        $out->setPageTitle($this->msg('looptags-specialpage-title'));
        $html = self::renderLoopTagSpecialPage();
        $out->addHtml($html);
    }

    /**
     * Renders the specialpage for the tags
     */
    public static function renderLoopTagSpecialPage()
    {
        $html = '<h1>';
        $html .= wfMessage('looptags-specialpage-title');
        $html .= '</h1>';

        $tagArray = LoopTags::getAllUsedTags();

        if (isset($_POST['getAll'])) {
            LoopUpdater::saveAllWikiPages();
            $html .= wfMessage('looptags-specialpage-refresh-text');
            $html .= ' <form method="post"><input type="submit" value="' . wfMessage('looptags-specialpage-refresh-back') . '"/></form>';
            return $html;
        }

        // Updatebutton
        $html .= ' <form method="post"><input type="submit" name="getAll" value="' . wfMessage('looptags-specialpage-get-all-button') . '"/></form>';

        if (!empty($tagArray)) {
            $filteredTags = $tagArray;
            // Get used tags
            $html .= self::renderTagOccurenceList($tagArray);
            $html .= self::renderTagFilters($filteredTags);
        }

        return $html;
    }

    /**
     * Renders the List of each tag in a chapter
     * @param array $tagArray An Array containing alls tags to be displayed in the list
     */
    private static function renderTagOccurenceList($tagArray)
    {
        $out = '<div id="tag_overview">';
        // create link-renderer
        $linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
        //create table
        $out .= '<h2>';
        $out .= wfMessage('looptags-specialpage-overview-list')->text() . ':';
        $out .= '</h2>';

        $out .= '<table class="table table-hover list_of_objects"><tbody id="tag_table">';
        //fill table
        foreach ($tagArray as $id => $tags) {
            $title = Title::newFromID($id);
            $out .= '<tr class="ml-1 pb-1" scope="row">';
            $out .= '<td class="pl-1 pr-1" scope="col">';
            $out .= '<span>';
            $out .= $linkRenderer->makeLink($title);
            $out .= '</span>';
            $out .= '</td>';
            $out .= '<td scope="col"><span><ul>';
            foreach ($tags as $tag) {
                $out .= '<li>' . $tag . '</li>';
            }
            $out .= '</ul></span></td>';
            $out .= '</tr>';
        }

        $out .= '</tbody></table></div>';
        return $out;
    }


    /**
     * Renders the List of each tag in a chapter
     * @param array $tagArray An Array containing alls tags to be displayed in the list
     */
    private static function renderTagFilters($tagArrayInput)
    {
        $tags = [];
        $amountInRow = 2;
        foreach ($tagArrayInput as $tagArray) {
            foreach ($tagArray as $tempTag) {
                array_push($tags, $tempTag);
            }
        }
        $tags = array_unique($tags);
        $out = '<div class="p-3 bg-light border"><form id="tag-filter">';
        $out .= '<div id="filter-table">';
        $out .= '<h4 class="float-left">' . wfMessage('looptags-specialpage-filter-tags-desc')->text() . '</h4>';
        $out .= '<div class="float-right">';
        $out .= '<input id="tag-filter-toggle-all" type="button" value="' . wfMessage('looptags-specialpage-all')->text() . '"/>';
        $out .= '<input id="tag-filter-toggle-none" class="ml-1" type="button" value="' . wfMessage('looptags-specialpage-none')->text() . '"/>';
        $out .= '</div>';
        $out .= '<div class="border-bottom" style="clear: both"></div>';

        $counter = 0;
        $out .= '<div class="container pt-2 pb-2"><div class="row">';
        foreach ($tags as $tag) {
            $out .= '<div class="col"><input class="filter-check" type="checkbox" id="' . $tag . '" name="filter" value="1"/><lable class="ml-2" for="' . $tag . '">' . $tag . '</label></div>';
            $counter++;
            if ($counter >= $amountInRow) {
                $out .= '</div><div class="row">';
                $counter -= $amountInRow;
            }
        }
        $out .= '</div></div>';

        $out .= '</div>';
        $out .= '</form>';
        $out .= '<div class="border-bottom"></div>';
        $out .= '<div id="filtered-tag-list">';
        $out .= '<table class="table table-hover list_of_objects"><tbody id="filtered-tag-table">';
        $out .= '</tbody></table>';
        $out .= '</div>';
        $out .= '</div>';
        return $out;
    }

    /**
     * Small helper-function, to wrap html in a dropdown menu.
     * The toggle will be the title of the dropdown with a small arrow
     * 
     * @param string $inputToHide String, containing html to wrap into the dropdown
     * @param string $title what to put in the headline, which will later toggle the dropdown
     */
    private static function renderDropDown($inputToHide, $title)
    {
        $wrapper = '<p>
            <button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#' . $title . '" aria-expanded="true" aria-controls="' . $title . '">
            ' . $title . '
            </button>
        </p>
        <div class="collapse" id="' . $title . '">
            <div class="card card-body">
            ' . $inputToHide . '
            </div>
        </div>';

        return $wrapper;
    }

    protected function getGroupName()
    {
        return 'loop';
    }
}
