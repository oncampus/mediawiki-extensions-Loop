<?php
/**
 * @description Special page for LOOP settings
 * @ingroup Extensions
 * @author Dennis Krohn @krohnden <dennis.krohn@th-luebeck.de>
 */
if ( !defined( 'MEDIAWIKI' ) ) die ( "This file cannot be run standalone.\n" );

use MediaWiki\MediaWikiServices;
use MediaWiki\Session\CsrfTokenSet;

class LoopSettings {

    public $id;
    public $timeStamp;
    public $imprintLink; #wgLoopImprintLink
    public $privacyLink; #wgLoopPrivacyLink
    public $rightsText; #wgRightsText
    public $rightsType; #wgLoopRightsType
    public $rightsUrl; #wgRightsUrl
    public $rightsIcon; #wgRightsIcon
    public $customLogo; #wgLoopCustomLogo["useCustomLogo"]
    public $customLogoFileName; #wgLoopCustomLogo["customFileName"]
    public $customLogoFilePath; #wgLoopCustomLogo["customFilePath"]
    public $extraFooter; #wgLoopExtraFooter
    public $skinStyle; #wgDefaultUserOptions['LoopSkinStyle']
    public $facebookIcon; #wgLoopSocialIcons['Facebook']['icon']
    public $facebookLink; #wgLoopSocialIcons['Facebook']['link']
    public $twitterIcon; #wgLoopSocialIcons['Twitter']['icon']
    public $twitterLink; #wgLoopSocialIcons['Twitter']['link']
    public $youtubeIcon; #wgLoopSocialIcons['Youtube']['icon']
    public $youtubeLink; #wgLoopSocialIcons['Youtube']['link']
    public $githubIcon; #wgLoopSocialIcons['Github']['icon']
    public $githubLink; #wgLoopSocialIcons['Github']['link']
    public $instagramIcon; #wgLoopSocialIcons['Instagram']['icon']
    public $instagramLink; #wgLoopSocialIcons['Instagram']['link']
    public $numberingObjects; #wgLoopObjectNumbering
    public $numberingType; #wgLoopNumberingType
    public $citationStyle; #wgLoopLiteratureCiteType
    public $extraSidebar; #wgLoopExtraSidebar
    public $exportT2s; #GUI only
    public $exportAudio; #GUI only
    public $exportPdf; #GUI only
    public $exportEpub; #GUI only
    public $exportScorm; #GUI only
    public $exportXml; #GUI only
    public $exportHtml; #GUI only
    public $captchaEdit; #wgCaptchaTriggers["edit"]
    public $captchaCreate; #wgCaptchaTriggers["create"]
    public $captchaAddurl; #wgCaptchaTriggers["addurl"]
    public $captchaCreateAccount; #wgCaptchaTriggers["createaccount"]
    public $captchaBadlogin; #wgCaptchaTriggers["badlogin"]
    public $bugReportEmail; #wgLoopBugReportEmail
    public $feedbackLevel; #wgLoopFeedbackLevel
    public $feedbackMode; #wgLoopFeedbackMode
	public $personalizationFeature; #wgpersonalizationFeature
    public $objectRenderOption; #wgLoopObjectDefaultRenderOption

    /**
     * Add settings to the database
     * @return bool true
     */
    function addToDatabase() {
        $this->dbkeys = array(
            'lset_imprintlink' => $this->imprintLink,
            'lset_privacylink' => $this->privacyLink,
            'lset_rightstext' => $this->rightsText,
            'lset_rightstype' => $this->rightsType,
            'lset_rightsurl' => $this->rightsUrl,
            'lset_rightsicon' => $this->rightsIcon,
            'lset_customlogo' => $this->customLogo,
            'lset_customlogofilename' => $this->customLogoFileName,
            'lset_customlogofilepath' => $this->customLogoFilePath,
            'lset_extrafooter' => $this->extraFooter,
            'lset_skinstyle' => $this->skinStyle,
            'lset_facebookicon' => $this->facebookIcon,
            'lset_facebooklink' => $this->facebookLink,
            'lset_twittericon' => $this->twitterIcon,
            'lset_twitterlink' => $this->twitterLink,
            'lset_youtubeicon' => $this->youtubeIcon,
            'lset_youtubelink' => $this->youtubeLink,
            'lset_githubicon' => $this->githubIcon,
            'lset_githublink' => $this->githubLink,
            'lset_instagramicon' => $this->instagramIcon,
            'lset_instagramlink' => $this->instagramLink,
            'lset_numberingobjects' => $this->numberingObjects,
            'lset_numberingtype' => $this->numberingType,
            'lset_citationstyle' => $this->citationStyle,
            'lset_extrasidebar' => $this->extraSidebar,
            'lset_exportt2s' => $this->exportT2s,
            'lset_exportaudio' => $this->exportAudio,
            'lset_exportpdf' => $this->exportPdf,
            'lset_exportxml' => $this->exportXml,
            'lset_exporthtml' => $this->exportHtml,
            'lset_exportepub' => $this->exportEpub,
            'lset_exportscorm' => $this->exportScorm,
            'lset_captchaedit' => $this->captchaEdit,
            'lset_captchacreate' => $this->captchaCreate,
            'lset_captchaddurl' => $this->captchaAddurl,
            'lset_captchacreateaccount' => $this->captchaCreateAccount,
            'lset_captchabadlogin' => $this->captchaBadlogin,
            'lset_ticketemail' => $this->bugReportEmail,
            'lset_feedbacklevel' => $this->feedbackLevel,
            'lset_feedbackmode' => $this->feedbackMode,
			'lset_personalizationFeature' => $this->personalizationFeature,
            'lset_objectrenderoption' => $this->objectRenderOption
        );

        $dbw = wfGetDB( DB_PRIMARY );

        try {
            $dbw->delete(
                'loop_settings',
                'lset_structure = 0',
                __METHOD__
            );
        } catch ( Exception $e ) {

        }

        foreach ( $this->dbkeys as $dbk => $val ) {
            $dbw->insert(
                'loop_settings',
                array(
                    'lset_structure' => 0,
                    'lset_property' => $dbk,
                    'lset_value' => $val,
                )
            );
        }

        return true;

    }

    /**
     * Loads settings from DB
     */
    public function loadSettings() {

        $dbr = wfGetDB( DB_REPLICA );

        $res = $dbr->select(
            'loop_settings',
            array(
                'lset_structure',
                'lset_property',
                'lset_value',
            ),
            array(
                 'lset_structure = "' . 0 .'"'
            ),
            __METHOD__
        );

        foreach ( $res as $row ) {
            $data[$row->lset_property] = $row->lset_value;
        }

        global $wgLoopImprintLink, $wgLoopPrivacyLink, $wgRightsText, $wgLoopRightsType, $wgRightsUrl, $wgRightsIcon,
        $wgLoopCustomLogo, $wgLoopExtraFooter, $wgDefaultUserOptions, $wgLoopSocialIcons, $wgLoopObjectNumbering,
        $wgLoopNumberingType, $wgLoopLiteratureCiteType, $wgLoopExtraSidebar, $wgCaptchaTriggers, $wgLoopBugReportEmail,
        $wgLoopFeedbackLevel, $wgLoopFeedbackMode, $wgPersonalizationFeature ,$wgLoopObjectDefaultRenderOption;

        # take values from presets in extension.json and LocalSettings if there is no DB entry
        $this->imprintLink = $wgLoopImprintLink;
        $this->privacyLink = $wgLoopPrivacyLink;
        $this->rightsText = $wgRightsText;
        $this->rightsType = $wgLoopRightsType;
        $this->rightsUrl = $wgRightsUrl;
        $this->rightsIcon = $wgRightsIcon;
        $this->customLogo = $wgLoopCustomLogo["useCustomLogo"];
        $this->customLogoFileName = $wgLoopCustomLogo["customFileName"];
        $this->customLogoFilePath = $wgLoopCustomLogo["customFilePath"];
        $this->extraFooter = $wgLoopExtraFooter;
        $this->skinStyle = $wgDefaultUserOptions['LoopSkinStyle'];
        $this->facebookIcon = $wgLoopSocialIcons['Facebook']['icon'];
        $this->facebookLink = $wgLoopSocialIcons['Facebook']['link'];
        $this->twitterIcon = $wgLoopSocialIcons['Twitter']['icon'];
        $this->twitterLink = $wgLoopSocialIcons['Twitter']['link'];
        $this->youtubeIcon = $wgLoopSocialIcons['Youtube']['icon'];
        $this->youtubeLink = $wgLoopSocialIcons['Youtube']['link'];
        $this->githubIcon = $wgLoopSocialIcons['Github']['icon'];
        $this->githubLink = $wgLoopSocialIcons['Github']['link'];
        $this->instagramIcon = $wgLoopSocialIcons['Instagram']['icon'];
        $this->instagramLink = $wgLoopSocialIcons['Instagram']['link'];
        $this->numberingObjects = $wgLoopObjectNumbering;
        $this->numberingType = $wgLoopNumberingType;
        $this->citationStyle = $wgLoopLiteratureCiteType;
        $this->extraSidebar = $wgLoopExtraSidebar;
        $this->exportT2s = true; # allow all export options that are given
        $this->exportAudio = true;
        $this->exportPdf = true;
        $this->exportEpub = true;
        $this->exportScorm = true;
        $this->exportXml = true;
        $this->exportHtml = true;
        $this->captchaEdit = $wgCaptchaTriggers["edit"];
        $this->captchaCreate = $wgCaptchaTriggers["create"];
        $this->captchaAddurl = $wgCaptchaTriggers["addurl"];
        $this->captchaCreateAccount = $wgCaptchaTriggers["createaccount"];
        $this->captchaBadlogin = $wgCaptchaTriggers["badlogin"];
        $this->bugReportEmail = $wgLoopBugReportEmail;
        $this->feedbackLevel = $wgLoopFeedbackLevel;
        $this->feedbackMode = $wgLoopFeedbackMode;
		$this->personalizationFeature = $wgPersonalizationFeature;
        $this->objectRenderOption = $wgLoopObjectDefaultRenderOption;

        if ( isset($row->lset_structure) ) {
            $this->imprintLink = isset( $data['lset_imprintlink'] ) ? $data['lset_imprintlink'] : $this->imprintLink;
            $this->privacyLink = isset( $data['lset_privacylink'] ) ? $data['lset_privacylink'] : $this->privacyLink;
            $this->rightsText = isset( $data['lset_rightstext'] ) ? $data['lset_rightstext'] : $this->rightsText;
            $this->rightsType = isset( $data['lset_rightstype'] ) ? $data['lset_rightstype'] : $this->rightsType;
            $this->rightsUrl = isset( $data['lset_rightsurl'] ) ? $data['lset_rightsurl'] : $this->rightsUrl;
            $this->rightsIcon = isset( $data['lset_rightsicon'] ) ? $data['lset_rightsicon'] : $this->rightsIcon;
            $this->customLogo = isset( $data['lset_customlogo'] ) ? boolval($data['lset_customlogo']) : $this->customLogo;
            $this->customLogoFileName = isset( $data['lset_customlogofilename'] ) ? $data['lset_customlogofilename'] : $this->customLogoFileName;
            $this->customLogoFilePath = isset( $data['lset_customlogofilepath'] ) ? $data['lset_customlogofilepath'] : $this->customLogoFilePath;
            $this->extraFooter = isset( $data['lset_extrafooter'] ) ? boolval($data['lset_extrafooter']) : $this->extraFooter;
            $this->skinStyle = isset( $data['lset_skinstyle'] ) ? $data['lset_skinstyle'] : $this->skinStyle;
            $this->facebookIcon = isset( $data['lset_facebookicon'] ) ? $data['lset_facebookicon'] : $this->facebookIcon;
            $this->facebookLink = isset( $data['lset_facebooklink'] ) ? $data['lset_facebooklink'] : $this->facebookLink;
            $this->twitterIcon = isset( $data['lset_twittericon'] ) ? $data['lset_twittericon'] : $this->twitterIcon;
            $this->twitterLink = isset( $data['lset_twitterlink'] ) ? $data['lset_twitterlink'] : $this->twitterLink;
            $this->youtubeIcon = isset( $data['lset_youtubeicon'] ) ? $data['lset_youtubeicon'] : $this->youtubeIcon;
            $this->youtubeLink = isset( $data['lset_youtubelink'] ) ? $data['lset_youtubelink'] : $this->youtubeLink;
            $this->githubIcon = isset( $data['lset_githubicon'] ) ? $data['lset_githubicon'] : $this->githubIcon;
            $this->githubLink = isset( $data['lset_githublink'] ) ? $data['lset_githublink'] : $this->githubLink;
            $this->instagramIcon = isset( $data['lset_instagramicon'] ) ? $data['lset_instagramicon'] : $this->instagramIcon;
            $this->instagramLink = isset( $data['lset_instagramlink'] ) ? $data['lset_instagramlink'] : $this->instagramLink;
            $this->numberingObjects = isset( $data['lset_numberingobjects'] ) ? boolval($data['lset_numberingobjects']) : $this->numberingObjects;
            $this->numberingType = isset( $data['lset_numberingtype'] ) ? $data['lset_numberingtype'] : $this->numberingType;
            $this->citationStyle = isset( $data['lset_citationstyle'] ) ? $data['lset_citationstyle'] : $this->citationStyle;
            $this->extraSidebar =  isset( $data['lset_extrasidebar'] ) ? boolval($data['lset_extrasidebar']) : $this->extraSidebar;
            $this->exportT2s =  isset( $data['lset_exportt2s'] ) ? boolval($data['lset_exportt2s']) : $this->exportT2s;
            $this->exportAudio =  isset( $data['lset_exportaudio'] ) ? boolval($data['lset_exportaudio']) : $this->exportAudio;
            $this->exportPdf =  isset( $data['lset_exportpdf'] ) ? boolval($data['lset_exportpdf']) : $this->exportPdf;
            $this->exportEpub =  isset( $data['lset_exportepub'] ) ? boolval($data['lset_exportepub']) : $this->exportEpub;
            $this->exportScorm = isset( $data['lset_exportscorm'] ) ?  boolval($data['lset_exportscorm']) : $this->exportScorm;
            $this->exportXml =  isset( $data['lset_exportxml'] ) ? boolval($data['lset_exportxml']) : $this->exportXml;
            $this->exportHtml =  isset( $data['lset_exporthtml'] ) ? boolval($data['lset_exporthtml']) : $this->exportHtml;
            $this->captchaEdit =  isset( $data['lset_captchaedit'] ) ? boolval($data['lset_captchaedit']) : $this->captchaEdit;
            $this->captchaCreate =  isset( $data['lset_captchacreate'] ) ? boolval($data['lset_captchacreate']) : $this->captchaCreate;
            $this->captchaAddurl =  isset( $data['lset_captchaddurl'] ) ? boolval($data['lset_captchaddurl']) : $this->captchaAddurl;
            $this->captchaCreateAccount =  isset( $data['lset_captchacreateaccount'] ) ? boolval($data['lset_captchacreateaccount']) : $this->captchaCreateAccount;
            $this->captchaBadlogin =  isset( $data['lset_captchabadlogin'] ) ? boolval($data['lset_captchabadlogin']) : $this->captchaBadlogin;
            $this->bugReportEmail =  isset( $data['lset_ticketemail'] ) ? $data['lset_ticketemail'] : $this->bugReportEmail;
            $this->feedbackLevel =  isset( $data['lset_feedbacklevel'] ) ? $data['lset_feedbacklevel'] : $this->feedbackLevel;
            $this->feedbackMode =  isset( $data['lset_feedbackmode'] ) ? $data['lset_feedbackmode'] : $this->feedbackMode;
			$this->personalizationFeature =  isset( $data['lset_personalizationFeature'] ) ? $data['lset_personalizationFeature'] : $this->personalizationFeature;
            $this->objectRenderOption =  isset( $data['lset_objectrenderoption'] ) ? $data['lset_objectrenderoption'] : $this->objectRenderOption;
        }

        return true;

    }

	/**
	 * Parse custom content
	 *
	 * @param String $input Content to parse
	 * @return String
	 */
	function parse( $input ) {

		$parserFactory = MediaWikiServices::getInstance()->getParserFactory();
		$parser = $parserFactory->create();
		$tmpTitle = Title::newFromText( 'NO TITLE' );
		$tmpUser = new User();
	    $parserOutput = $parser->parse( $input, $tmpTitle, new ParserOptions($tmpUser) );
	    return $parserOutput->mText;

	}

    /**
     * Puts request content into array
     *
     * @param Request $request
     * @param User $user
     * @return Bool
     */

    public function getLoopSettingsFromRequest ( $request, $user )
	{

		global $wgLoopSocialIcons, $wgLoopAvailableSkinStyles, $wgAvailableLicenses, $wgLegalTitleChars;
		$this->errors = array();
		$this->rightsText = $request->getText('rights-text'); # no validation required

		$socialArray = array(
			'Facebook' => array(),
			'Twitter' => array(),
			'Youtube' => array(),
			'Github' => array(),
			'Instagram' => array()
		);

		foreach ($wgLoopSocialIcons as $socialIcons => $socialIcon) {

			if (empty($request->getText('footer-' . $socialIcons . '-icon')) || $request->getText('footer-' . $socialIcons . '-icon') == $socialIcons) {
				$socialArray[$socialIcons]['icon'] = $request->getText('footer-' . $socialIcons . '-icon');

				if (!empty($request->getText('footer-' . $socialIcons . '-icon') && filter_var($request->getText('footer-' . $socialIcons . '-url'), FILTER_VALIDATE_URL))) {
					$socialArray[$socialIcons]['link'] = $request->getText('footer-' . $socialIcons . '-url');
				} else {
					$socialArray[$socialIcons]['link'] = '';
				}
			} else {
				array_push($this->errors, wfMessage('loopsettings-error') . ': ' . $socialIcons);
			}
		}

		$this->facebookIcon = $socialArray['Facebook']['icon'];
		$this->facebookLink = $socialArray['Facebook']['link'];
		$this->twitterIcon = $socialArray['Twitter']['icon'];
		$this->twitterLink = $socialArray['Twitter']['link'];
		$this->youtubeIcon = $socialArray['Youtube']['icon'];
		$this->youtubeLink = $socialArray['Youtube']['link'];
		$this->githubIcon = $socialArray['Github']['icon'];
		$this->githubLink = $socialArray['Github']['link'];
		$this->instagramIcon = $socialArray['Instagram']['icon'];
		$this->instagramLink = $socialArray['Instagram']['link'];

		$regExLoopLink = '/([' . $wgLegalTitleChars . ']{1,})/';

		if (filter_var($request->getText('privacy-link'), FILTER_VALIDATE_URL) || preg_match($regExLoopLink, $request->getText('privacy-link'))) {
			$this->privacyLink = $request->getText('privacy-link');
		} else {
			array_push($this->errors, wfMessage('loopsettings-error') . ': ' . wfMessage('loopsettings-privacy-label'));
		}

		if (filter_var($request->getText('imprint-link'), FILTER_VALIDATE_URL) || preg_match($regExLoopLink, $request->getText('imprint-link'))) {
			$this->imprintLink = $request->getText('imprint-link');
		} else {
			array_push($this->errors, wfMessage('loopsettings-error') . ': ' . wfMessage('loopsettings-imprint-label'));
		}

		if (empty ($request->getText('rights-type')) || isset ($wgAvailableLicenses[$request->getText('rights-type')])) {
			$this->rightsType = $request->getText('rights-type');
			$this->rightsIcon = $wgAvailableLicenses[$this->rightsType]['icon'];
			$this->rightsUrl = $wgAvailableLicenses[$this->rightsType]['url'];
		} else {
			array_push($this->errors, wfMessage('loopsettings-error') . ': ' . wfMessage('loopsettings-use-cc-label'));
		}
		if (empty($wgLoopAvailableSkinStyles)) {
			$wgLoopAvailableSkinStyles[] = "style-blue";
		}
		if (in_array($request->getText('skin-style'), $wgLoopAvailableSkinStyles)) {
			$this->skinStyle = $request->getText('skin-style');
			$userOptionsManager = MediaWikiServices::getInstance()->getUserOptionsManager();
			$userOptionsManager->setOption($user, 'LoopSkinStyle', $this->skinStyle);
			$userOptionsManager->saveOptions($user);
		} else {
			array_push($this->errors, wfMessage('loopsettings-error') . ': ' . wfMessage('loopsettings-skin-style-label'));
		}

		if ($request->getText('extra-footer-active') == 'on') {
			$this->extraFooter = true;
		} elseif (empty($request->getText('extra-footer-active'))) {
			$this->extraFooter = false;
		} else {
			array_push($this->errors, wfMessage('loopsettings-error') . ': ' . wfMessage('loopsettings-extra-footer-label'));
		}

		if (empty($request->getText('logo-use-custom'))) {
			$this->customLogo = false;
			$this->customLogoFileName = "";
			$this->customLogoFilePath = "";
		} elseif ($request->getText('logo-use-custom') == 'on') {
			$this->customLogo = true;
			$this->customLogoFileName = $request->getText('custom-logo-filename');
			$tmpParsedResult = $this->parse('{{filepath:' . $request->getText('custom-logo-filename') . '}}');
			preg_match('/href="(.*)"/', $tmpParsedResult, $tmpOutputArray);
			if (isset ($tmpOutputArray[1])) {
				$this->customLogoFilePath = $tmpOutputArray[1];
			} else {
				$this->customLogo = false;
				$this->customLogoFileName = "";
				$this->customLogoFilePath = "";
				array_push($this->errors, wfMessage('loopsettings-error-customlogo-notfound'));
			}
		} else {
			$this->customLogo = false;
			$this->customLogoFileName = "";
			$this->customLogoFilePath = "";
			array_push($this->errors, wfMessage('loopsettings-error') . ': ' . wfMessage('loopsettings-customlogo-label'));
		}

		# Numbering objects
		if ($request->getText('numbering-objects') == 'numberingObjects') {
			$this->numberingObjects = true;
		} elseif (empty ($request->getText('numbering-objects'))) {
			$this->numberingObjects = false;
		} else {
			$this->numberingObjects = false;
			array_push($this->errors, wfMessage('loopsettings-error') . ': ' . wfMessage('loopsettings-numbering-objects-label'));
		}

		# Render objects
		if (!empty ($request->getText('render-objects'))) {
			if (in_array($request->getText('render-objects'), LoopObject::$mRenderOptions)) {
				$this->objectRenderOption = $request->getText('render-objects');
			} else {
				array_push($this->errors, wfMessage('loopsettings-error') . ': ' . wfMessage('loopsettings-render-objects-label'));
			}
		}

		# Numbering type
		if (!empty ($request->getText('numbering-type'))) {
			if ($request->getText('numbering-type') == "ongoing") {
				$this->numberingType = "ongoing";
			} else {
				$this->numberingType = "chapter";
			}
		}

		# citation style
		if (!empty ($request->getText('citation-style'))) {
			if ($request->getText('citation-style') == "vancouver") {
				$this->citationStyle = "vancouver";
			} else {
				$this->citationStyle = "harvard";
			}
		} else {
			array_push($this->errors, wfMessage('loopsettings-error') . ': ' . wfMessage('loopsettings-citation-style-label'));
		}

		if ($request->getText('extra-sidebar-active') == 'on') {
			$this->extraSidebar = true;
		} elseif (empty($request->getText('extra-sidebar-active'))) {
			$this->extraSidebar = false;
		} else {
			array_push($this->errors, wfMessage('loopsettings-error') . ': ' . wfMessage('loopsettings-extra-sidebar-label'));
		}


		# Export t2s
		if ($request->getText('export-t2s') == 'on') {
			$this->exportT2s = true;
		} elseif (empty ($request->getText('export-t2s'))) {
			$this->exportT2s = false;
		} else {
			array_push($this->errors, wfMessage('loopsettings-error') . ': ' . wfMessage('loopsettings-export-t2s-label'));
		}
		# Export audio
		if ($request->getText('export-audio') == 'on') {
			$this->exportAudio = true;
		} elseif (empty ($request->getText('export-audio'))) {
			$this->exportAudio = false;
		} else {
			array_push($this->errors, wfMessage('loopsettings-error') . ': ' . wfMessage('loopsettings-export-audio-label'));
		}
		# Export pdf
		if ($request->getText('export-pdf') == 'on') {
			$this->exportPdf = true;
		} elseif (empty ($request->getText('export-pdf'))) {
			$this->exportPdf = false;
		} else {
			array_push($this->errors, wfMessage('loopsettings-error') . ': ' . wfMessage('loopsettings-export-pdf-label'));
		}
		# Export epub
		if ($request->getText('export-epub') == 'on') {
			$this->exportEpub = true;
		} elseif (empty ($request->getText('export-epub'))) {
			$this->exportEpub = false;
		} else {
			array_push($this->errors, wfMessage('loopsettings-error') . ': ' . wfMessage('loopsettings-export-epub-label'));
		}
		# Export scorm
		if ($request->getText('export-scorm') == 'on') {
			$this->exportScorm = true;
		} elseif (empty ($request->getText('export-scorm'))) {
			$this->exportScorm = false;
		} else {
			array_push($this->errors, wfMessage('loopsettings-error') . ': ' . wfMessage('loopsettings-export-scorm-label'));
		}
		# Export xml
		if ($request->getText('export-xml') == 'on') {
			$this->exportXml = true;
		} elseif (empty ($request->getText('export-xml'))) {
			$this->exportXml = false;
		} else {
			array_push($this->errors, wfMessage('loopsettings-error') . ': ' . wfMessage('loopsettings-export-xml-label'));
		}
		# Export html
		if ($request->getText('export-html') == 'on') {
			$this->exportHtml = true;
		} elseif (empty ($request->getText('export-html'))) {
			$this->exportHtml = false;
		} else {
			array_push($this->errors, wfMessage('loopsettings-error') . ': ' . wfMessage('loopsettings-export-html-label'));
		}

		# Captcha edit
		if ($request->getText('captcha-edit') == 'on') {
			$this->captchaEdit = true;
		} elseif (empty ($request->getText('captcha-ecit'))) {
			$this->captchaEdit = false;
		} else {
			array_push($this->errors, wfMessage('loopsettings-error') . ': ' . wfMessage('loopsettings-captcha-edit-label'));
		}
		# Captcha create
		if ($request->getText('captcha-create') == 'on') {
			$this->captchaCreate = true;
		} elseif (empty ($request->getText('captcha-create'))) {
			$this->captchaCreate = false;
		} else {
			array_push($this->errors, wfMessage('loopsettings-error') . ': ' . wfMessage('loopsettings-captcha-create-label'));
		}
		# Captcha createaccount
		if ($request->getText('captcha-createaccount') == 'on') {
			$this->captchaAddurl = true;
		} elseif (empty ($request->getText('captcha-createaccount'))) {
			$this->captchaAddurl = false;
		} else {
			array_push($this->errors, wfMessage('loopsettings-error') . ': ' . wfMessage('loopsettings-captcha-createaccount-label'));
		}
		# Captcha addurl
		if ($request->getText('captcha-addurl') == 'on') {
			$this->captchaCreateAccount = true;
		} elseif (empty ($request->getText('captcha-addurl'))) {
			$this->captchaCreateAccount = false;
		} else {
			array_push($this->errors, wfMessage('loopsettings-error') . ': ' . wfMessage('loopsettings-captcha-addurl-label'));
		}
		# Captcha badlogin
		if ($request->getText('captcha-badlogin') == 'on') {
			$this->captchaBadlogin = true;
		} elseif (empty ($request->getText('captcha-badlogin'))) {
			$this->captchaBadlogin = false;
		} else {
			array_push($this->errors, wfMessage('loopsettings-error') . ': ' . wfMessage('loopsettings-captcha-badlogin-label'));
		}

		# Bugreport Mail
		if (empty ($request->getText('ticket-email'))) {
			$this->bugReportEmail = null;
		} elseif (filter_var($request->getText('ticket-email'), FILTER_VALIDATE_EMAIL)) {
			$this->bugReportEmail = $request->getText('ticket-email');
		} else {
			array_push($this->errors, wfMessage('loopsettings-error') . ': ' . wfMessage('loopsettings-ticket-email-label'));
		}

		# Feedback mode
		if (empty ($request->getText('feedback-mode'))) {
			$this->feedbackMode = "second_half";
		} elseif (in_array($request->getText('feedback-mode'), array("always", "last_sublevel", "second_half"))) {
			$this->feedbackMode = $request->getText('feedback-mode');
		} else {
			global $wgLoopFeedbackMode;
			$this->feedbackMode = $wgLoopFeedbackMode;
			array_push($this->errors, wfMessage('loopsettings-error') . ': ' . wfMessage('loopsettings-feedback-mode-label'));
		}

		if (empty($request->getText('personalization-feature'))) {
			$this->personalizationFeature = 'false';
		} else {
			if(in_array($request->getText('personalization-feature'), array("false", "true"))) {
				$this->personalizationFeature = $request->getText('personalization-feature');
			}
	    }

        # Feedback level
        if  ( empty ( $request->getText( 'feedback-level' ) ) ) {
            $this->feedbackLevel = "none";
        } elseif ( in_array( $request->getText( 'feedback-level' ), array( "none", "module", "chapter" ) ) ) {
            $this->feedbackLevel = $request->getText( 'feedback-level' );
        } else {
            global $wgLoopFeedbackLevel;
            $this->feedbackLevel = $wgLoopFeedbackLevel;
            array_push( $this->errors, wfMessage( 'loopsettings-error' ) . ': ' . wfMessage( 'loopsettings-feedback-level-label' ) );
        }

        $this->addToDatabase();
        SpecialPurgeCache::purge();
        return true;

    }

	public static function setupGlobalVariablesFromLoopSettings() {

		global $wgLoopImprintLink, $wgLoopPrivacyLink, $wgRightsText, $wgLoopRightsType, $wgRightsUrl, $wgRightsIcon,
        $wgLoopCustomLogo, $wgLoopExtraFooter, $wgDefaultUserOptions, $wgLoopSocialIcons, $wgLoopObjectNumbering,
        $wgLoopNumberingType, $wgLoopLiteratureCiteType, $wgLoopExtraSidebar, $wgCaptchaTriggers, $wgLoopBugReportEmail,
        $wgLoopFeedbackLevel, $wgLoopFeedbackMode, $wgLoopObjectDefaultRenderOption, $wgLoopUnprotectedRSS, $wgPersonalizationFeature;

		// $dbr = wfGetDB( DB_REPLICA ); // old
		$dbProvider = MediaWikiServices::getInstance()->getConnectionProvider();
		$dbr = $dbProvider->getReplicaDatabase();

		# Check if table exists. SetupAfterCache hook fails if there is no loop_settings table.
		# maintenance/update.php can't create loop_settings table if SetupAfterCache Hook fails, so this check is nescessary.
		if ( $dbr->tableExists( 'loop_settings' ) ) {

			$res = $dbr->select(
				'loop_settings',
				array(
					'lset_structure',
					'lset_property',
					'lset_value',
				),
				array(
					'lset_structure = "' . 0 .'"' # TODO Structure support
				),
				__METHOD__
			);
			foreach ( $res as $row ) {
				$data[$row->lset_property] = $row->lset_value;
			}

			#set global variables to content from settings
			if ( isset( $row->lset_structure ) ) {
				$wgRightsText = ( !isset( $data['lset_rightstext'] ) ? $wgRightsText : $data['lset_rightstext'] );
				$wgRightsUrl = ( !isset( $data['lset_rightsurl'] ) ? $wgRightsUrl : $data['lset_rightsurl'] );
				$wgRightsIcon = ( !isset( $data['lset_rightsicon'] ) ? $wgRightsIcon : $data['lset_rightsicon'] );
				$wgLoopRightsType = ( !isset( $data['lset_rightstype'] ) ? $wgLoopRightsType : $data['lset_rightstype'] );
				$wgDefaultUserOptions['LoopSkinStyle'] = ( !isset( $data['lset_skinstyle'] ) ? $wgDefaultUserOptions['LoopSkinStyle'] : $data['lset_skinstyle'] );
				$wgLoopImprintLink = ( !isset( $data['lset_imprintlink'] ) ? $wgLoopImprintLink : $data['lset_imprintlink'] );
				$wgLoopPrivacyLink = ( !isset( $data['lset_privacylink'] ) ? $wgLoopPrivacyLink : $data['lset_privacylink'] );
				$wgLoopObjectNumbering = ( !isset( $data['lset_numberingobjects'] ) ? $wgLoopObjectNumbering : boolval( $data['lset_numberingobjects'] ) );
				$wgLoopNumberingType = ( !isset( $data['lset_numberingtype'] ) ? $wgLoopNumberingType : $data['lset_numberingtype'] );
				$wgLoopLiteratureCiteType = ( !isset( $data['lset_citationstyle'] ) ? $wgLoopLiteratureCiteType : $data['lset_citationstyle'] );
				$wgLoopCustomLogo = ( !isset( $data['lset_citationstyle'] ) ? $wgLoopCustomLogo : array( "useCustomLogo" => $data['lset_customlogo'], "customFileName" => $data['lset_customlogofilename'], "customFilePath" => $data['lset_customlogofilepath'] ) );
				$wgLoopExtraFooter = ( !isset( $data['lset_extrafooter'] ) ? $wgLoopExtraFooter : $data['lset_extrafooter'] );
				$wgLoopExtraSidebar = ( !isset( $data['lset_extrasidebar'] ) ? $wgLoopExtraSidebar : $data['lset_extrasidebar'] );
				$wgLoopSocialIcons['Facebook'] = ( !isset( $data['lset_facebooklink'] ) ? $wgLoopSocialIcons['Facebook'] : array( "icon" => $data['lset_facebookicon'], "link" => $data['lset_facebooklink'] ) );
				$wgLoopSocialIcons['Twitter'] = ( !isset( $data['lset_twitterlink'] ) ?$wgLoopSocialIcons['Twitter'] : array( "icon" => $data['lset_twittericon'], "link" => $data['lset_twitterlink'] ) );
				$wgLoopSocialIcons['Youtube'] = ( !isset( $data['lset_youtubelink'] ) ? $wgLoopSocialIcons['Youtube'] : array( "icon" => $data['lset_youtubeicon'], "link" => $data['lset_youtubelink'] ) );
				$wgLoopSocialIcons['Github'] = ( !isset( $data['lset_githublink'] ) ? $wgLoopSocialIcons['Github'] : array( "icon" => $data['lset_githubicon'], "link" => $data['lset_githublink'] ) );
				$wgLoopSocialIcons['Instagram'] = ( !isset( $data['lset_instagramlink'] ) ? $wgLoopSocialIcons['Instagram'] : array( "icon" => $data['lset_instagramicon'], "link" => $data['lset_instagramlink'] ) );
				$wgCaptchaTriggers["edit"] = ( !isset( $data['lset_captchaedit'] ) ?$wgCaptchaTriggers["edit"] : boolval( $data['lset_captchaedit'] ) );
				$wgCaptchaTriggers["create"] = ( !isset( $data['lset_captchacreate'] ) ? $wgCaptchaTriggers["create"] : boolval( $data['lset_captchacreate'] ) );
				$wgCaptchaTriggers["addurl"] = ( !isset( $data['lset_captchaddurl'] ) ? $wgCaptchaTriggers["addurl"] : boolval( $data['lset_captchaddurl'] ) );
				$wgCaptchaTriggers["createaccount"] = ( !isset( $data['lset_captchacreateaccount'] ) ? $wgCaptchaTriggers["createaccount"] : boolval( $data['lset_captchacreateaccount'] ) );
				$wgCaptchaTriggers["badlogin"] = ( !isset( $data['lset_captchabadlogin'] ) ? $wgCaptchaTriggers["badlogin"] : boolval( $data['lset_captchabadlogin'] ) );
				$wgCaptchaTriggers["bugreport"] = ( !isset( $data['lset_captchabugreport'] ) ? $wgCaptchaTriggers["bugreport"] : boolval( $data['lset_captchabugreport'] ) );
				$wgLoopBugReportEmail = ( !isset( $data['lset_ticketemail'] ) ? $wgLoopBugReportEmail : $data['lset_ticketemail'] );
				$wgLoopFeedbackLevel = ( !isset( $data['lset_feedbacklevel'] ) ? $wgLoopFeedbackLevel : $data['lset_feedbacklevel'] );
				$wgLoopFeedbackMode = ( !isset( $data['lset_feedbackmode'] ) ? $wgLoopFeedbackMode : $data['lset_feedbackmode'] );
				$wgPersonalizationFeature = ( !isset( $data['lset_personalizationFeature'] ) ? $wgPersonalizationFeature : $data['lset_personalizationFeature'] );
				$wgLoopUnprotectedRSS = ( !isset( $data['lset_rssunprotected'] ) ? $wgLoopUnprotectedRSS : $data['lset_rssunprotected'] );
				$wgLoopObjectDefaultRenderOption = ( !isset( $data['lset_objectrenderoption'] ) ? $wgLoopObjectDefaultRenderOption : $data['lset_objectrenderoption'] );
			}

		}
	}
}


class SpecialLoopSettings extends SpecialPage {

	function __construct() {
		parent::__construct( 'LoopSettings' );
	}
	function execute( $sub ) {

		$out = $this->getOutput();
		$request = $this->getRequest();
		$user = $this->getUser();
		$csrfTokenSet = new CsrfTokenSet($request);
		Loop::handleLoopRequest( $out, $request, $user ); #handle editmode

	    $linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
	    $linkRenderer->setForceArticlePath(true);
		$html = '';
		$permissionManager = MediaWikiServices::getInstance()->getPermissionManager();
		if ( $permissionManager->userHasRight( $user, 'loop-settings-edit' ) ) {

			global $wgSecretKey, $wgLoopSocialIcons, $wgAvailableLicenses,
			$wgLoopSkinStyles, $wgLoopAvailableSkinStyles, $wgLoopEditableSkinStyles;

			$this->setHeaders();

 			$out->addModules( 'loop.special.settings.js' );
			$out->setPageTitle( $this->msg( 'loopsettings-specialpage-title' ) );

			$requestToken = $request->getText( 't' );
			$uploadButton = $this->msg( 'loopsettings-upload-hint' ) .
				$linkRenderer->makelink(
					new TitleValue( NS_SPECIAL, 'ListFiles' ),
                    new HtmlArmor( $this->getSkin()->msg ( 'listfiles' )),
                    array( "class" => "ml-1")
				) . '<button type="button" class="upload-button mw-htmlform-submit mw-ui-button mt-2 d-block">' . $this->msg( 'upload' ) . '</button>';

			$currentLoopSettings = new LoopSettings();

			if ( ! empty( $requestToken ) ) {

				if ( $csrfTokenSet->matchToken( $requestToken, $request->getSessionId()->__tostring() ) ) {

                    $currentLoopSettings->getLoopSettingsFromRequest( $request, $user );

                    if ( empty ( $currentLoopSettings->errors ) ) {

                        $html .= '<div class="alert alert-success" role="alert">' . $this->msg( 'loopsettings-save-success' ) . '</div>';
                    } else {
                        $errorMsgs = '';
                        foreach( $currentLoopSettings->errors as $error ) {

                            $errorMsgs .= $error . '<br>';

                        }
                        $html .= '<div class="alert alert-danger" role="alert">' . $errorMsgs.'</div>';

                    }

                } else {
                    $currentLoopSettings->loadSettings();
                }
            } else {
                $currentLoopSettings->loadSettings();
            }
		$saltedToken = $csrfTokenSet->getToken( $request->getSessionId()->__tostring() );

			$html .= '<nav>
				<div class="nav nav-tabs" id="nav-tab" role="tablist">
					<a class="nav-item nav-link active" id="nav-general-tab" data-toggle="tab" href="#nav-general" role="tab" aria-controls="nav-general" aria-selected="true">' . $this->msg( 'loopsettings-tab-general' )->text() . '</a>
					<a class="nav-item nav-link" id="nav-appearance-tab" data-toggle="tab" href="#nav-appearance" role="tab" aria-controls="nav-appearance" aria-selected="true">' . $this->msg( 'loopsettings-tab-appearance' )->text() . '</a>
                    <a class="nav-item nav-link" id="nav-content-tab" data-toggle="tab" href="#nav-content" role="tab" aria-controls="nav-content" aria-selected="true">' . $this->msg( 'loopsettings-tab-content' )->text() . '</a>
                    <a class="nav-item nav-link" id="nav-tech-tab" data-toggle="tab" href="#nav-tech" role="tab" aria-controls="nav-tech" aria-selected="true">' . $this->msg( 'loopsettings-tab-advanced' )->text() . '</a>
				</div>
			</nav>
			<form class="needs-validation mw-editform mt-3 mb-3" id="loopsettings-form" method="post" novalidate enctype="multipart/form-data">';

			$html .= '<div class="tab-content" id="nav-tabContent">
				<div class="tab-pane fade show active" id="nav-general" role="tabpanel" aria-labelledby="nav-general-tab">';

				/**
				 * GENERAL  TAB
				 */


					### LICENSE BLOCK ###
					$html .= '<h3>' . $this->msg( 'loopsettings-headline-license' ) . '</h3>';
					$html .= '<div class="form-row mb-4">';

					# cc-license
					$licenseOptions = '';
					foreach( $wgAvailableLicenses as $license => $option ) {
						if ( $license == $currentLoopSettings->rightsType ) {
							$selected = 'selected';
						} else {
							$selected = '';
						}
						if ( $license == '' ) {
							$licenseText = $this->msg( 'Htmlform-chosen-placeholder' );
						} else {
							$licenseText = $license;
						}

						$licenseOptions .= '<option value="' . $license.'" ' . $selected.'>' . $licenseText.'</option>';
					}

					$html .= "<div class='col-12 col-sm-6'>
							<input type='checkbox' name='license-use-cc' id='license-use-cc' value='licenseUseCC' ". ( ! empty( $currentLoopSettings->rightsType ) ? 'checked' : '' ) .">
							<label for='license-use-cc'>" . $this->msg( 'loopsettings-use-cc-label' ) . "</label>
							<select class='form-control' ". ( empty( $currentLoopSettings->rightsType ) ? 'disabled' : '' ) ." name='rights-type' id='rights-type' selected='". $currentLoopSettings->rightsType ." '>
							" . $licenseOptions . "</select>
						</div>";

					# license text
					$html .=
						'<div class="col-12 col-sm-6">
							<label for="rights-text">' . $this->msg( 'loopsettings-rights-label' ) . '</label>
							<input type="text"' . ' placeholder="'. $this->msg( 'loopsettings-rights-text-placeholder' ) .'" name="rights-text" id="rights-text" class="setting-input form-control" value=' . '"' . $currentLoopSettings->rightsText.'"' . '>
							<div class="invalid-feedback">' . $this->msg( 'loopsettings-rights-text-hint' ) . " ©,:._-!?&/()'</div>" .
						'</div>';
					$html .= '</div>';

					### LINK BLOCK ###
                    $html .= '<h3>' . $this->msg( 'loopsettings-headline-footer-links' ) . '</h3>';


                    global $wgLoopExternalImprintPrivacy, $wgLoopExternalPrivacyUrl;

                    if ( ! $wgLoopExternalImprintPrivacy || empty ( $wgLoopExternalPrivacyUrl ) ) {
                        $html .= '<div class="form-row mb-4">';

                        # imprint link
                        $html .=
                        '<div class="col-12 col-sm-6">
                            <label for="imprint-link">' . $this->msg( 'loopsettings-imprint-label' ) . '</label>
                            <input type="text" required name="imprint-link" placeholder="URL" id="imprint-link" class="setting-input form-control" value="'. $currentLoopSettings->imprintLink .'">
                            <div class="invalid-feedback">' . $this->msg( 'loopsettings-url-imprint-privacy-hint' ) . '</div>
                        </div>';
                        # privacy link
                        $html .=
                        '<div class="col-12 col-sm-6">
                            <label for="privacy-link">' . $this->msg( 'loopsettings-privacy-label' ) . '</label>
                            <input type="text" required name="privacy-link" placeholder="URL" id="privacy-link" class="setting-input form-control" value="'. $currentLoopSettings->privacyLink .'">
                            <div class="invalid-feedback">' . $this->msg( 'loopsettings-url-imprint-privacy-hint' ) . '</div>
                        </div>';
                        $html .= '</div>';
                    }

					#footer-social
					$socialArray = array(
						'Facebook' => array( 'icon' => $currentLoopSettings->facebookIcon, 'link' => $currentLoopSettings->facebookLink ),
						'Twitter' => array( 'icon' => $currentLoopSettings->twitterIcon, 'link' => $currentLoopSettings->twitterLink ),
						'Youtube' => array( 'icon' => $currentLoopSettings->youtubeIcon, 'link' => $currentLoopSettings->youtubeLink ),
						'Github' => array( 'icon' => $currentLoopSettings->githubIcon, 'link' => $currentLoopSettings->githubLink ),
						'Instagram' => array( 'icon' => $currentLoopSettings->instagramIcon, 'link' => $currentLoopSettings->instagramLink )
                    );
                    $i = 1;
					foreach( $wgLoopSocialIcons as $socialIcons => $socialIcon ) {
                        if ( $i % 2 != 0 ) {
                            $html .= '<div class="form-row">';
                        }
						$html .= '
                        <div class="col-6">
							<input type="checkbox" name="footer-'. $socialIcons .'-icon" id="footer-'. $socialIcons .'-icon" value="'. $socialIcons .'" '. ( ! empty ( $socialArray[$socialIcons]['icon']) ? 'checked' : '' ) .'>

							<label for="footer-' . $socialIcons .'-icon"><span class="ic ic-social-' . strtolower( $socialIcons ) . '"></span>
							'. $socialIcons . ' ' . $this->msg( 'loopsettings-link-icon-label' ) . '</label>

							<div class="input-group mb-3">
								<input type="url" ' . ( empty( $socialArray[$socialIcons]['icon'] ) ? 'disabled' : '' ) . ' name="footer-'. $socialIcons .'-url" placeholder="https://www.'. strtolower( $socialIcons) .'.com/" id="footer-'. $socialIcons .'-url" class="setting-input form-control" value="'. $socialArray[$socialIcons]['link'] .'">
								<div class="invalid-feedback" id="feedback-'. $socialIcons .'">' . $this->msg( 'loopsettings-url-hint' ) . '</div>
                            </div>
                        </div>';
                        if ( $i % 2 == 0 || $i == count( $wgLoopSocialIcons ) ) {
                            $html .= '</div>';
                        }
                        $i++;
                    }

                    ### EXPORT BLOCK ###
                    $showExportOptions = false;
					$exporthtml = '<h3>' . $this->msg( 'loopsettings-headline-export' ) . '</h3>';
					$exporthtml .= '<div class="form-row mb-4">';
					$exporthtml .= '<div class="col-12">';

                    $dummyLoopSettings = new LoopSettings(); # check further requirements of export types without interfering LoopSettings
                    $dummyLoopSettings->exportT2s = true;
                    $dummyLoopSettings->exportAudio = true;
                    $dummyLoopSettings->exportPdf = true;
                    $dummyLoopSettings->exportEpub = true;
                    $dummyLoopSettings->exportScorm = true;
                    $dummyLoopSettings->exportXml = true;
                    $dummyLoopSettings->exportHtml = true;

                    if ( LoopExportPageMp3::isAvailable( $dummyLoopSettings ) ) {
                        $exporthtml .= '<div><input type="checkbox" name="export-t2s" id="export-t2s" class="setting-input mr-1" ' . ( $currentLoopSettings->exportT2s ? 'checked' : '' ) .'>';
                        $exporthtml .= '<label for="export-t2s"><span class="mr-1 ic ic-audio"></span>' . $this->msg( 'loopsettings-export-t2s-label' ) . '</label></div>';
                        $showExportOptions = true;
                    }
                    if ( LoopExportPdf::isAvailable( $dummyLoopSettings ) ) {
                        $exporthtml .= '<div><input type="checkbox" name="export-pdf" id="export-pdf" class="setting-input mr-1" ' . ( $currentLoopSettings->exportPdf ? 'checked' : '' ) .'>';
                        $exporthtml .= '<label for="export-pdf"><span class="mr-1 ic ic-file-pdf"></span>' . $this->msg( 'loopsettings-export-pdf-label' ) . '</label></div>';
                        $showExportOptions = true;
                    }
                    if ( LoopExportMp3::isAvailable( $dummyLoopSettings ) ) {
					    $exporthtml .= '<div><input type="checkbox" name="export-audio" id="export-audio" class="setting-input mr-1" ' . ( $currentLoopSettings->exportAudio ? 'checked' : '' ) .'>';
                        $exporthtml .= '<label for="export-audio"><span class="mr-1 ic ic-file-mp3"></span>' . $this->msg( 'loopsettings-export-audio-label' ) . '</label></div>';
                        $showExportOptions = true;
                    }
                    if ( LoopExportEpub::isAvailable( $dummyLoopSettings ) ) {
					    $exporthtml .= '<div><input type="checkbox" name="export-epub" id="export-epub" class="setting-input mr-1" ' . ( $currentLoopSettings->exportEpub ? 'checked' : '' ) .'>';
                        $exporthtml .= '<label for="export-epub"><span class="mr-1 ic ic-file-epub"></span>' . $this->msg( 'loopsettings-export-epub-label' ) . '</label></div>';
                        $showExportOptions = true;
                    }
                    if ( LoopExportScorm::isAvailable( $dummyLoopSettings ) ) {
                        $exporthtml .= '<div><input type="checkbox" name="export-scorm" id="export-scorm" class="setting-input mr-1" ' . ( $currentLoopSettings->exportScorm ? 'checked' : '' ) .'>';
                        $exporthtml .= '<label for="export-scorm"><span class="mr-1 ic ic-file-scorm"></span>' . $this->msg( 'loopsettings-export-scorm-label' ) . '</label></div>';
                        $showExportOptions = true;
					}
                    if ( LoopExportHtml::isAvailable( $dummyLoopSettings ) ) {
                        $exporthtml .= '<div><input type="checkbox" name="export-html" id="export-html" class="setting-input mr-1" ' . ( $currentLoopSettings->exportHtml ? 'checked' : '' ) .'>';
                        $exporthtml .= '<label for="export-html"><span class="mr-1 ic ic-file-xml"></span>' . $this->msg( 'loopsettings-export-html-label' ) . '</label></div>';
                        $showExportOptions = true;
					}
                    if ( LoopExportXml::isAvailable( $dummyLoopSettings ) ) {
					    $exporthtml .= '<div><input type="checkbox" name="export-xml" id="export-xml" class="setting-input mr-1" ' . ( $currentLoopSettings->exportXml ? 'checked' : '' ) .'>';
                        $exporthtml .= '<label for="export-xml"><span class="mr-1 ic ic-file-xml"></span>' . $this->msg( 'loopsettings-export-xml-label' ) . '</label></div>';
                        $showExportOptions = true;
                    }
                    $exporthtml .= '</div></div>';
                    if ( $showExportOptions ) {
                        $html .= $exporthtml;
                    }

				$html .= '</div>'; // end of general-tab

				/**
				 * APPEARANCE TAB
				 */
				$html .= '<div class="tab-pane fade" id="nav-appearance" role="tabpanel" aria-labelledby="nav-appearance-tab">';
                    ### SKIN BLOCK ###
					$html .= '<div class="form-row mb-4">';
					$html .=    '<div class="col-6 pl-1">';
					$html .=        '<h3>' . $this->msg( 'loopsettings-headline-skinstyle' ) . '</h3>';
                    $skinStyleOptions = '';
                    $styles = array_unique ( ! isset( $wgLoopAvailableSkinStyles ) ? $wgLoopSkinStyles : $wgLoopAvailableSkinStyles );
                    if ( empty( $styles ) ) {
                        $styles[] = "style-blue";
                    }
					foreach( $styles as $style ) {
					    if ( $style == $currentLoopSettings->skinStyle ) {
							$selected = 'selected';
						} else {
							$selected = '';
                        }
                        if ( $style == "" ) {
                            continue; # fallback for wrong configuration
                        }
						$skinStyleOptions .= '<option value="' . $style.'" ' . $selected.'>'. $this->msg( 'loop-skin-'. $style ) .'</option>';
                    }

					$html .= '<label for="skin-style">' . $this->msg( 'loopsettings-skin-style-label' ) . '</label>';
					$html .= '<select class="form-control" name="skin-style" id="skin-style" selected="'. $currentLoopSettings->skinStyle .' ">' . $skinStyleOptions . '</select>';
                    $html .=    '</div>';

                    $html .= '</div>'; #end of form-row

                    ### LOGO BLOCK ###
                    $editableStyles = "";
                    foreach( $wgLoopEditableSkinStyles as $editableStyle ) {
                        if ( !empty( $editableStyles ) ) {
                            $editableStyles .= ", ";
                        }
                        $editableStyles .= $this->msg( 'loop-skin-'. $editableStyle );
                    }
                    $html .= '<h3>' . $this->msg( 'loopsettings-headline-logo' ) . '</h3>';
					$html .= '<div class="form-row mb-4">';
					$html .=    '<div class="col-6 col-sm-6 pl-1">';
					$html .=        '<input type="checkbox" class="mr-1" name="logo-use-custom" id="logo-use-custom" '. ( ! empty( $currentLoopSettings->customLogo ) ? 'checked' : '' ) .'>';
                    $html .=        '<label for="logo-use-custom">' . $this->msg( 'loopsettings-customlogo-label' ) . '</label>';
                    $html .=        '<span class="logo-hint ic ic-info pl-1 small" id="logo-hint" title="' . $this->msg( 'loopsettings-customlogo-editablestyles', $editableStyles ). '"></span>';
					$html .=        '<input '. ( ! $currentLoopSettings->customLogo ? 'disabled' : '' ) .' name="custom-logo-filename" placeholder="Logo.png" id="custom-logo-filename" class="form-control setting-input" value="' . $currentLoopSettings->customLogoFileName.'">';
					$html .=        '<div class="invalid-feedback">' . $this->msg( 'loopsettings-customlogo-hint' ) . '</div>';
					$html .=        '<input type="hidden" name="custom-logo-filepath" id="custom-logo-filepath" value="' . $currentLoopSettings->customLogoFilePath.'">';
					$html .=    '</div>';
                    $html .=    '<div class="col-3 col-sm-6">';
                    if ( $currentLoopSettings->customLogo && ! empty( $currentLoopSettings->customLogoFilePath ) ) {
                        $html .=    "<p class='mb-1 mr-2'>" . $this->msg( 'Prefs-preview' ) . ' ' . $currentLoopSettings->customLogoFileName.":</p>";
                        $html .=    "<img src='" . $currentLoopSettings->customLogoFilePath."' style='max-width:100%; max-height: 50px;'></img>";
                    }
                    $html .=    '</div>';
                    $html .= $uploadButton;
                    $html .= '</div>';


                    $html .= '<div class="form-row mb-4">';
                    # extra sidebar
                    $html .=    '<div class="col-6 pl-1">';
					$html .=        '<h3>' . $this->msg( 'loopsettings-headline-sidebar' ) . '</h3>';
					$html .=        '<input class="mr-1" type="checkbox" name="extra-sidebar-active" id="extra-sidebar-active" ' . ( ! empty ( $currentLoopSettings->extraSidebar ) ? 'checked' : '' ) .'>';
                    $html .=        '<label for="extra-sidebar-active">' . $this->msg( 'loopsettings-extra-sidebar-label' ) . '</label>';
                    $html .= $linkRenderer->makeLink(
                        Title::newFromText("MediaWiki:ExtraSidebar"),
                        new HtmlArmor( $this->msg("loopsettings-extra-sidebar-linktext") ),
                        array("target" => "blank", "class" => "ml-1")
                    );
                    $html .= '  </div>';


					# extra footer
                    $html .= '<div class="col-6 pl-1">';
                    $html .= '<h3>' . $this->msg( 'loopsettings-headline-extrafooter' ) . '</h3>';
					$html .= '<input type="checkbox" name="extra-footer-active" id="extra-footer-active" ' . ( ! empty ( $currentLoopSettings->extraFooter ) ? 'checked' : '' ) .'>
						<label for="extra-footer-active">' . $this->msg( 'loopsettings-extra-footer-label' ) . '</label>';
					$html .= $linkRenderer->makeLink(
						Title::newFromText("MediaWiki:ExtraFooter"),
						new HtmlArmor( $this->msg("loopsettings-extra-footer-linktext") ),
						array("target" => "blank", "class" => "ml-1")
					);
                    $html .= '</div>';
                    $html .= '</div>'; #end of form-row

				$html .= '</div>'; // end of appearence-tab

				/**
				 * TECH SETTINGS TAB
				 */
				$html .= '<div class="tab-pane fade" id="nav-tech" role="tabpanel" aria-labelledby="nav-tech-tab">';

                    $html .= '<div class="form-row mb-4">';

                    ### FEEDBACK ###
                        $html .= '<div class="col-6">';
                        $html .= '<h3>' . $this->msg( 'loopsettings-headline-feedback' )->text() . '</h3>';

                        $html .= '<label for="feedback-level">'. $this->msg("loopsettings-feedback-level-label")->text().'</label>';
                        $html .= '<select class="form-control mb-2" name="feedback-level" id="feedback-level" selected="'.$currentLoopSettings->feedbackLevel.'">';
                        $html .= '<option value="none" ' . ( $currentLoopSettings->feedbackLevel == "none" ? "selected" : "" ) .'>' . $this->msg("loopsettings-feedback-level-none")->text() .'</option>';
                        $html .= '<option value="chapter" ' . ( $currentLoopSettings->feedbackLevel == "chapter" ? "selected" : "" ) .'>' . $this->msg("loopsettings-feedback-level-chapter")->text() .'</option>';
                        $html .= '<option value="module" ' . ( $currentLoopSettings->feedbackLevel == "module" ? "selected" : "" ) .'>' . $this->msg("loopsettings-feedback-level-module")->text() .'</option>';
                        $html .= '</select>';

                        $html .= '<label for="feedback-mode">'. $this->msg("loopsettings-feedback-mode-label")->text().'</label>';
                        $html .= '<select class="form-control" name="feedback-mode" id="feedback-mode" selected="'.$currentLoopSettings->feedbackMode.'" ' . ( $currentLoopSettings->feedbackLevel == "none" ? "disabled" : "" ) .'>';
                        $html .= '<option value="always" ' . ( $currentLoopSettings->feedbackMode == "always" ? "selected" : "" ) .'>' . $this->msg("loopsettings-feedback-mode-always")->text() .'</option>';
                        $html .= '<option value="last_sublevel" ' . ( $currentLoopSettings->feedbackMode == "last_sublevel" ? "selected" : "" ) .'>' . $this->msg("loopsettings-feedback-mode-last_sublevel")->text() .'</option>';
                        $html .= '<option value="second_half" ' . ( $currentLoopSettings->feedbackMode == "second_half" ? "selected" : "" ) .'>' . $this->msg("loopsettings-feedback-mode-second_half")->text() .'</option>';
                        $html .= '</select>';

                        $html .= '</div>';

						$html .= '<div class="col-6">';
						$html .= '<h3>' . $this->msg( 'loopsettings-personalization' )->text() . '</h3>';

						// select
						$html .= '<select class="form-control" name="personalization-feature" id="personalization-feature" selected="'.$currentLoopSettings->personalizationFeature.'" ' . '>';
						$html .= '<option value="false" ' . ( $currentLoopSettings->personalizationFeature == 'false' ? "selected" : "" ) .'>' . $this->msg("loopsettings-personalization-feature-off")->text() . '</option>';
						$html .= '<option value="true" ' . ( $currentLoopSettings->personalizationFeature == 'true' ? "selected" : "" ) .'>' . $this->msg("loopsettings-personalization-feature-on")->text() . '</option>';
						$html .= '</select>';

						$html .= '</div>';

                    ### BUGREPORT ###
                    if ( LoopBugReport::isAvailable() != "external" ) {
                        $html .= '<div class="col-6">';
                        $html .= '<h3>' . $this->msg( 'loopsettings-headline-bugreport' )->text() . '</h3>';
                        $html .= '<label for="ticket-email">'. $this->msg("loopsettings-bugreport-email-label")->text().'</label>';
                        $html .= '<input class="mb-2 mt-2 form-control" type="email" placeholder="'.$this->msg("email")->text().'" value="'.$currentLoopSettings->bugReportEmail.'" name="ticket-email"/>';
                        $html .= '</div>';
                    }
                    $html .= '</div>';

                    ### CAPTCHA BLOCK ###
                    global $wgReCaptchaSiteKey, $wgReCaptchaSecretKey;
                    $captchaDisabled = "";
                    $captchaDisabledMsg = "";
                    if ( empty( $wgReCaptchaSiteKey ) || empty( $wgReCaptchaSecretKey ) ) {
                        $captchaDisabled = "disabled";
                        $captchaDisabledMsg = '<div class="alert alert-warning w-100">'. $this->msg( 'loopsettings-captcha-nokeys' )->text().'</div>';
                    }
					$html .= '<div class="form-row mb-4">';
                    $html .= '<h3>' . $this->msg( 'loopsettings-headline-captcha' )->text() . '</h3>';
                    $html .= $captchaDisabledMsg;
                    $html .= '<p class="mb-1">'.$this->msg( "loopsettings-captcha-desc")->text().'</p>';
					$html .= '<div class="col-12">';
					$html .= '<div><input type="checkbox" name="captcha-edit" id="captcha-edit" class="setting-input mr-1" ' . $captchaDisabled . " " . ( $currentLoopSettings->captchaEdit == "captchaEdit" ? 'checked' : '' ) .'>';
                    $html .= '<label for="captcha-edit">' . $this->msg( 'loopsettings-captcha-edit-label' )->text() . '*</label></div>';
					$html .= '<div><input type="checkbox" name="captcha-create" id="captcha-create" class="setting-input mr-1" ' . $captchaDisabled . " " . ( $currentLoopSettings->captchaCreate == "captchaCreate" ? 'checked' : '' ) .'>';
					$html .= '<label for="captcha-create">' . $this->msg( 'loopsettings-captcha-create-label' )->text() . '*</label></div>';
					$html .= '<div><input type="checkbox" name="captcha-addurl" id="captcha-addurl" class="setting-input mr-1" ' . $captchaDisabled . " " . ( $currentLoopSettings->captchaAddurl == "captchaAddurl" ? 'checked' : '' ) .'>';
                    $html .= '<label for="captcha-addurl">' . $this->msg( 'loopsettings-captcha-addurl-label' )->text() . '*</label></div>';
                    $html .= '<div><input type="checkbox" name="captcha-createaccount" id="captcha-createaccount" class="setting-input mr-1" ' . $captchaDisabled . " " . ( $currentLoopSettings->captchaCreateAccount == "captchaCreateAccount" ? 'checked' : '' ) .'>';
                    $html .= '<label for="captcha-createaccount">' . $this->msg( 'loopsettings-captcha-createaccount-label' )->text() . '</label></div>';
					$html .= '<div><input type="checkbox" name="captcha-badlogin" id="captcha-badlogin" class="setting-input mr-1" ' . $captchaDisabled . " " . ( $currentLoopSettings->captchaBadlogin == "captchaBadlogin" ? 'checked' : '' ) .'>';
                    $html .= '<label for="captcha-badlogin">' . $this->msg( 'loopsettings-captcha-badlogin-label' ) . '</label></div>';
					$html .= '<p><i>* '.$this->msg( "loopsettings-captcha-usergroup")->text().'</i></p>';
                    $html .= '</div>';
                    $html .= '</div>';

				$html .= '</div>'; // end of tech-tab


				/**
				 * CONTENT TAB
				 */
				$html .= '<div class="tab-pane fade" id="nav-content" role="tabpanel" aria-labelledby="nav-content-tab">';

                    $html .= '<div class="form-row">';
                    $html .= '<h3>' . $this->msg( "loopsettings-headline-objects" ) . '</h3>';
                    $html .= '<p class="pl-2 pr-2">' . $this->msg( "loopsettings-objects-hint" ) . '</p>';
                        $html .= '<div class="col-8">';
                        $html .= '<fieldset>';
                            $html .= '<h5>' . $this->msg( 'loopsettings-renderobjects' ) . '</h5>';
                            $html .= '<div class="mb-1"><input type="radio" name="render-objects" id="render-marked" value="marked" ' . ( $currentLoopSettings->objectRenderOption == "marked" ? 'checked="checked"' : '' ) .'>
                            <label for="render-marked">' . $this->msg( 'loopsettings-render-objects-marked-label' ) . '</label></div>';

                            $html .= '<div class="mb-1"><input type="radio" name="render-objects" id="render-icon" value="icon" ' . ( $currentLoopSettings->objectRenderOption == "icon" ? 'checked="checked"' : '' ) .'>
                            <label for="render-icon">' . $this->msg( 'loopsettings-render-objects-icon-label' ) . '</label></div>';

                            $html .= '<div class="mb-1"><input type="radio" name="render-objects" id="render-title" value="title" ' . ( $currentLoopSettings->objectRenderOption == "title" ? 'checked="checked"' : '' ) .'>
                            <label for="render-title">' . $this->msg( 'loopsettings-render-objects-title-label' ) . '</label></div>';

                            $html .= '<div class="mb-3"><input type="radio" name="render-objects" id="render-none" value="none" ' . ( $currentLoopSettings->objectRenderOption == "none" ? 'checked="checked"' : '' ) .'>
                            <label for="render-none">' . $this->msg( 'loopsettings-render-objects-none-label' ) . '</label></div>';

                        $html .= '</fieldset>';
                        $html .= '</div>'; // end of col

                        $html .= '<div class="col-3">';
                        $render = $currentLoopSettings->objectRenderOption;
                        $html .= '<div id="loopsettings-object-example" class="loop_object figure">
                        <div class="loop_object_content">
                        <img id="loopsettings-object-example-img" src="/mediawiki/skins/Loop/resources/img/logo_loop.svg" class="image w-100"></div>
                        <div class="ls-none '.($render=="none"?"d-none ":"").'loop_object_footer">
                        <div class="loop_object_title">
                            <span class="ls-title '.($render=="title"?"d-none ":"").'loop_object_icon"><span class="ic ic-figure"></span>&nbsp;</span>
                            <span class="ls-icon ls-title '.($render=="title"||$render=="icon"?"d-none ":"").'loop_object_name">'.$this->msg( 'loop_figure-name-short' ).'</span>
                            <span class="ls-icon ls-title '.($render=="title"||$render=="icon"?"d-none ":"").'loop_object_number"><span class="' . ( $currentLoopSettings->numberingType == "ongoing" ? 'd-none' : '' ) .'" id="ls-chapter">3.12</span></span>
                            <span class="ls-icon ls-title '.($render=="title"||$render=="icon"?"d-none ":"").'loop_object_number"><span class="' . ( $currentLoopSettings->numberingType == "ongoing" ? '' : 'd-none' ) .'" id="ls-ongoing">47</span></span>
                            <span class="ls-icon ls-title '.($render=="title"||$render=="icon"?"d-none ":"").'loop_object_title_seperator">:&nbsp;</span><wbr>
                            <span class="loop_object_title_content">Title</span>
                        </div>
                        <div class="ls-title '.($render=="title"?"d-none ":"").'loop_object_description">Description</div>
                        <div class="ls-title '.($render=="title"?"d-none ":"").'loop_object_copyright">Copyright</div>
                        </div>
                        </div>';
                        $html .= '</div>'; // end of col
                    $html .= '</div>'; // end of row

                    $html .= '<div class="form-row">';
                        $html .= '<div class="col-8">';
                            $html .= '<h5>' . $this->msg( 'loopsettings-numbering' ) . '</h5>';

                            $html .= '<div class="mb-1"><input type="checkbox" name="numbering-objects" id="numbering-objects" value="numberingObjects" ' . ( $currentLoopSettings->numberingObjects == true ? 'checked' : '' ) . ( $currentLoopSettings->objectRenderOption != "marked" ? ' disabled' : '' ) .'>
                            <label for="numbering-objects">' . $this->msg( 'loopsettings-numbering-objects-label' ) . '</label></div>';

                            $html .= '<div class="mb-1"><input type="radio" name="numbering-type" id="ongoing" value="ongoing" ' . ( $currentLoopSettings->numberingType == "ongoing" ? 'checked' : '' ) . ( $currentLoopSettings->numberingObjects != true || $currentLoopSettings->objectRenderOption != "marked" ? ' disabled' : '' ) .'>
                            <label for="ongoing">' . $this->msg( 'loopsettings-numbering-type-ongoing-label' ) . '</label></div>';

                            $html .= '<div class="mb-3"><input type="radio" name="numbering-type" id="chapter" value="chapter" ' . ( $currentLoopSettings->numberingType == "chapter" ? 'checked' : '' ) . ( $currentLoopSettings->numberingObjects != true || $currentLoopSettings->objectRenderOption != "marked" ? ' disabled' : '' ) .'>
                            <label for="chapter">' . $this->msg( 'loopsettings-numbering-type-chapter-label' ) . '</label></div>';
                        $html .= '</div>'; // end of col

                    $html .= '</div>'; // end of row

                    $html .= '<div class="form-row">';
                        $html .= '<div class="col-12">';
                            $html .= '<h3>' . $this->msg( "loopsettings-citation-style" ) . '</h3>';
                            $html .= '<div class="mb-1"><input type="radio" name="citation-style" id="harvard" value="harvard" ' . ( $currentLoopSettings->citationStyle == "harvard" ? 'checked' : '' ) .'>';
                            $html .= '<label for="harvard"> ' . $this->msg( 'loopsettings-citation-style-harvard-label' ) . '</label></div>';

                            $html .= '<div class="mb-1"><input type="radio" name="citation-style" id="vancouver" value="vancouver" ' . ( $currentLoopSettings->citationStyle == "vancouver" ? 'checked' : '' ) .'>';
                            $html .= '<label for="vancouver"> ' . $this->msg( 'loopsettings-citation-style-vancouver-label' ) . '</label></div>';
                        $html .= '</div>'; // end of col
                    $html .= '</div>'; // end of row

				$html .= '</div>'; // end of content-tab

			$html .= '</div>'; // end of tab-content

			$html .= '<input type="hidden" name="t" id="loopsettings-token" value="' . $saltedToken . '"></input>';
			$html .= '<input type="submit" class="mw-htmlform-submit mw-ui-button mw-ui-primary mw-ui-progressive mt-2 d-block" id="loopsettings-submit" value="' . $this->msg( 'submit' ) . '"></input>';

			$html .= '</form>';

			} else {
				$html .= '<div class="alert alert-warning" role="alert">' . $this->msg( 'loopsettings-no-permission' ) . '</div>';
		}
		$out->addHTML( $html );
	}


	/**
	 * Specify the specialpages-group loop
	 *
	 * @return string
	 */
	protected function getGroupName() {
		return 'loop';
	}

}
