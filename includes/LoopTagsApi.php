<?php

/**
 * @description
 * @ingroup Extensions
 * @author Daniel Waage <danielwaage@hotmail.de>
 */

if (!defined('MEDIAWIKI')) die("This file cannot be run standalone.\n");

use MediaWiki\MediaWikiServices;
use Wikimedia\ParamValidator\ParamValidator;

class ApiLoopTags extends ApiBase
{
    public function __construct($main, $action)
    {
        parent::__construct($main, $action);
    }

    public function execute()
    {
        $permissionManager = MediaWikiServices::getInstance()->getPermissionManager();
        $user = $this->getUser();
        if ($user->getBlock() != null) {
            $this->dieWithError(
                $this->msg('loopfeedback-error-blocked')->escaped(),
                'userblocked'
            );
        }

        if (!$permissionManager->userHasRight($user, 'loopfeedback-view')) {
            $this->dieWithError(
                $this->msg('loopfeedback-error-nopermission')->escaped(),
                'nopermission'
            );
        }


        $result = $this->getResult();
        $params = $this->extractRequestParams();
        $searchedTag = '';
        if (isset($params['tags'])) {
            $searchedTag = $params['tags'];
        }
        $resultArray = LoopTags::getCertainUsedTag($searchedTag);

        $result->addValue(null, $this->getModuleName(), $resultArray);
    }

    /**
     * Requests will return either a complete list of pagelinks (empty params) 
     * or just search for certain tags, in which case it will return all the pagelinks, which contain the requested tags 
     */
    public function getAllowedParams()
    {
        $ret = array(
            'tags' => array(
                ParamValidator::PARAM_TYPE     => 'string',
                ParamValidator::PARAM_REQUIRED => false,
            )
        );
        return $ret;
    }

    protected function getExamples()
    {
        return array(
            'api.php?action=looptags-request'
        );
    }



    public function getParamDescription()
    {
        return array(
            'tags'      => "Multiple (html-)tags to look for in this Loop cluster."
        );
    }


    public function mustBePosted()
    {
        return false;
    }


    public function isWriteMode()
    {
        return false;
    }

    public function getDescription()
    {
        return array(
            'Get Pages, in which tags are used'
        );
    }

    public function getVersion()
    {
        return __CLASS__ . ': version 1.0';
    }

    /**
     * Generate a new, unique id.
     *
     * Data can be sharded over multiple servers, rendering database engine's
     * auto-increment useless to generate a unique id.
     *
     * @return string
     */
    protected function generateId()
    {
        /*
		 * This will return a 128-bit string in base-16, resulting
		 * in a 32-character (at max) string of hexadecimal characters.
		 * Pad the string to full 32-char length if the value is lower.
		 */
        $idGenerator = MediaWikiServices::getInstance()->getGlobalIdGenerator();
        $id = $idGenerator->newTimestampedUID128(16);
        return str_pad($id, 32, 0, STR_PAD_LEFT);
    }
}
