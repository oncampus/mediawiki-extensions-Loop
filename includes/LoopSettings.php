<?php

class LoopSettings {

public $id;
public $timeStamp;
public $imprintLink;
public $privacyLink;
public $oncampusLink;
public $rightsText;
public $rightsType;
public $rightsUrl;
public $rightsIcon;
public $customLogo;
public $customLogoFileName;
public $customLogoFilePath;
public $languageCode;
public $extraFooter;
public $extraFooterWikitext;
public $extraFooterParsed;
public $skinStyle;
public $socialFbIcon;
public $socialFbLink;
public $socialTwIcon;
public $socialTwLink;
public $socialYtIcon;
public $socialYtLink;
public $socialGhIcon;
public $socialGhLink;
public $socialInIcon;
public $socialInLink;

/**
 * Add settings to the database
 * @return bool true
 */
function addToDatabase() {
    
    $dbw = wfGetDB( DB_MASTER );
    //$this->id = $dbw->nextSequenceValue( 'LoopStructureItem_id_seq' );
        
    $dbw->insert(
        'loop_settings',
        array(
            //'lset_id' => $this->id,
            //'lset_timestamp' => $this->timeStamp,
            'lset_imprintlink' => $this->imprintLink,
            'lset_privacylink' => $this->privacyLink,
            'lset_oncampuslink' => $this->oncampusLink,
            'lset_rightstext' => $this->rightsText,
            'lset_rightstype' => $this->rightsType,
            'lset_rightsurl' => $this->rightsUrl,
            'lset_rightsicon' => $this->rightsIcon,
            'lset_customlogo' => $this->customLogo,
            'lset_customlogofilename' => $this->customLogoFileName,
            'lset_customlogofilepath' => $this->customLogoFilePath,
            'lset_languagecode' => $this->languageCode,
            'lset_extrafooter' => $this->extraFooter/*,
            'lset_extrafooterwikitext' => $this->extraFooterWikitext,
            'lset_extrafooterparsed' => $this->extraFooterParsed,
            'lset_skinstyle' => $this->skinStyle,
            'lset_socialfbicon' => $this->socialFbIcon,
            'lset_socialfblink' => $this->socialFbLink,
            'lset_socialtwicon' => $this->socialTwIcon,
            'lset_socialtwlink' => $this->socialTwLink,
            'lset_socialyticon' => $this->socialYtIcon,
            'lset_socialytlink' => $this->socialYtLink,
            'lset_socialghicon' => $this->socialGhIcon,
            'lset_socialghlink' => $this->socialGhLink,
            'lset_socialinicon' => $this->socialInIcon,
            'lset_socialinlink' => $this->socialInLink*/
        )
    );
    //$this->id = $dbw->insertId();
    
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
            'lset_id',
            'lset_timestamp',
            'lset_imprintlink',
            'lset_privacylink',
            'lset_oncampuslink',
            'lset_rightstext',
            'lset_rightstype',
            'lset_rightsurl',
            'lset_rightsicon',
            'lset_customlogo',
            'lset_customlogofilename',
            'lset_customlogofilepath',
            'lset_languagecode',
            'lset_extrafooter',
            'lset_extrafooterwikitext',
            'lset_extrafooterparsed',
            'lset_skinstyle',
            'lset_socialfbicon',
            'lset_socialfblink',
            'lset_socialtwicon',
            'lset_socialtwlink',
            'lset_socialyticon',
            'lset_socialytlink',
            'lset_socialghicon',
            'lset_socialghlink',
            'lset_socialinicon',
            'lset_socialinlink'
        ),
        array(),
        __METHOD__,
        array(
            'ORDER BY' => 'lset_id DESC LIMIT 1'
        )
    );
    //echo "test";
    $row = $res->fetchObject();
    if ( isset($row->lset_id) ) {
    if( $row ) {
        //$loopSettings = new LoopSettings();

        $this->id = $row->lset_id;
        $this->timeStamp = $row->lset_timestamp;
        $this->imprintLink = $row->lset_imprintlink;
        $this->privacyLink = $row->lset_privacylink;
        $this->oncampusLink = $row->lset_oncampuslink;
        $this->rightsText = $row->lset_rightstext;
        $this->rightsType = $row->lset_rightstype;
        $this->rightsUrl = $row->lset_rightsurl;
        $this->rightsIcon = $row->lset_rightsicon;
        $this->customLogo = $row->lset_customlogo;
        $this->customLogoFileName = $row->lset_customlogofilename;
        $this->customLogoFilePath = $row->lset_customlogofilepath;
        $this->languageCode = $row->lset_languagecode;
        $this->extraFooter = $row->lset_extrafooter;
        $this->extraFooterWikitext = $row->lset_extrafooterwikitext;
        $this->extraFooterParsed = $row->lset_extrafooterparsed;
        $this->skinStyle = $row->lset_skinstyle;
        $this->facebookIcon = $row->lset_socialfbicon;
        $this->facebookLink = $row->lset_socialfblink;
        $this->twitterIcon = $row->lset_socialtwicon;
        $this->twitterLink = $row->lset_socialtwlink;
        $this->youtubeIcon = $row->lset_socialyticon;
        $this->youtubeLink = $row->lset_socialytlink;
        $this->githubIcon = $row->lset_socialghicon;
        $this->githubLink = $row->lset_socialghlink;
        $this->instagramIcon = $row->lset_socialinicon;
        $this->instagramLink = $row->lset_socialinlink;
            
        return true;
    } else {
            
        return false;
            
    }
} else { // fetch data from global variables
    global $wgSkinStyles, $wgImprintLink, $wgPrivacyLink, $wgOncampusLink;

    $this->oncampusLink = $wgOncampusLink;
    $this->skinStyles = $wgSkinStyles;
    $this->imprintLink = $wgImprintLink;
    $this->privacyLink = $wgPrivacyLink;

    var_dump($this);
    return true;
}

}



/**
 * Puts request content into array
 *
 * @param Request $request 
 * @return Array $newLoopSettings
 */

public function getLoopSettingsFromRequest ( $request ) {
    
    global $wgSocialIcons, $wgSkinStyles, $wgAvailableLicenses, $wgSupportedLoopLanguages;
    
    $errors = array();
    //$newLoopSettings = new LoopSettings();


    if ( empty ( $request->getText( 'rights-text' ) ) || preg_match( "/([-a-zA-Z0-9äöüAÖÜß:_\/\(\)©åæÅÆç&!é\?,\.')]{0,})/", $request->getText( 'rights-text' ) ) ) {
        $this->rightsText = $request->getText( 'rights-text' );
    } else {
        $this->rightsText = $this->rightsText;
        array_push( $errors, wfMessage( 'loopsettings-error' )  . ': ' . wfMessage( 'loopsettings-rights-label' ) );
    }
    $socialArray = array(
        'Facebook' => array(),
        'Twitter' => array(),
        'Youtube' => array(),
        'Github' => array( ),
        'Instagram' => array()
    );
    foreach( $wgSocialIcons as $socialIcons => $socialIcon ) {
        
        if ( empty( $request->getText( 'footer-' . $socialIcons . '-icon' ) ) || $request->getText( 'footer-' . $socialIcons . '-icon' ) == $socialIcons ) {
           $socialArray[$socialIcons]['icon'] = $request->getText( 'footer-' . $socialIcons . '-icon' );
            
            if ( ! empty( $request->getText( 'footer-' . $socialIcons . '-icon' ) && filter_var( $request->getText( 'footer-' . $socialIcons . '-url' ), FILTER_VALIDATE_URL ) ) ) {
               $socialArray[$socialIcons]['link'] = $request->getText( 'footer-' . $socialIcons . '-url' );
            } else {
               $socialArray[$socialIcons]['link'] = '';
            }
        } else {
           $socialArray[$socialIcons]['icon'] = $socialArray[$socialIcons]['icon'];
           $socialArray[$socialIcons]['link'] = $socialArray[$socialIcons]['link'];
            array_push( $errors, wfMessage( 'loopsettings-error' )  . ': ' . $socialIcons );
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

    
    
    $regExLoopLink = '/([\/]{1}[Ll]{1}[Oo]{2}[Pp]{1}[\/]{1}[-a-zA-Z0-9äöüØåæÅÆøÄÖÜß_:.\/()\[\]]{1,})/';
    
    if ( filter_var( $request->getText( 'privacy-link' ), FILTER_VALIDATE_URL ) || preg_match( $regExLoopLink, $request->getText( 'privacy-link' ) ) ) {
        $this->privacyLink = $request->getText( 'privacy-link' );
    } else {
        $this->privacyLink = $this->privacyLink;
        array_push( $errors, wfMessage( 'loopsettings-error' )  . ': ' . wfMessage( 'loopsettings-privacy-label' ) );
    }
    
    if ( filter_var( $request->getText( 'imprint-link' ), FILTER_VALIDATE_URL ) || preg_match( $regExLoopLink, $request->getText( 'imprint-link' ) ) ) {
        $this->imprintLink = $request->getText( 'imprint-link' );
    } else {
        $this->imprintLink = $this->imprintLink;
        array_push( $errors, wfMessage( 'loopsettings-error' )  . ': ' . wfMessage( 'loopsettings-imprint-label' ) );
    }
    
    if ( empty ( $request->getText( 'oncampus-link' ) ) || $request->getText( 'oncampus-link' ) == 'showOncampusLink' ) {
        $this->oncampusLink = $request->getText( 'oncampus-link' );
    } else {
        $this->oncampusLink = $this->oncampusLink;
        array_push( $errors, wfMessage( 'loopsettings-error' )  . ': ' . wfMessage( 'loopsettings-oncampus-label' ) );
    }
    
    if ( empty ( $request->getText( 'rights-type' ) ) || isset ( $wgAvailableLicenses[$request->getText( 'rights-type' )] ) ) {
        $this->rightsType = $request->getText( 'rights-type' );
        $this->rightsIcon = $wgAvailableLicenses[$this->rightsType]['icon'];
        $this->rightsUrl = $wgAvailableLicenses[$this->rightsType]['url'];
    } else {
        $this->rightsType = $this->rightsType;
        $this->rightsIcon = $this->rightsIcon;
        $this->rightsUrl = $this->rightsUrl;
        array_push( $errors, wfMessage( 'loopsettings-error' )  . ': ' . wfMessage( 'loopsettings-use-cc-label' ) );
    }
    
    if ( in_array( $request->getText( 'skin-style' ), $wgSkinStyles ) ) {
        $this->skinStyle = $request->getText( 'skin-style' );
    } else {
        $this->skinStyle = $this->skinStyle;
        array_push( $errors, wfMessage( 'loopsettings-error' )  . ': ' . wfMessage( 'loopsettings-skin-style-label' ) );
    }
    
    if ( in_array( $request->getText( 'language-select' ), $wgSupportedLoopLanguages ) ) {
        $this->languageCode = $request->getText( 'language-select' );
    } else {
        $this->languageCode = $this->languageCode;
        array_push( $errors, wfMessage( 'loopsettings-error' )  . ': ' . wfMessage( 'loopsettings-language-label' ) );
    }
    
    if ( empty ( $request->getText( 'extra-footer-active' ) ) || $request->getText( 'extra-footer-active' ) == 'useExtraFooter' ) {
            $this->extraFooter = $request->getText( 'extra-footer-active' );
        if ( ! empty ( $request->getText( 'extra-footer-wikitext' ) ) ) {
            $this->extraFooterWikitext = $request->getText( 'extra-footer-wikitext' );
            $this->extraFooterParsed = $this->parse( $this->extraFooterWikitext );
        }
    } else {
        $this->extraFooter = $this->extraFooter;
        $this->extraFooterWikitext = $this->extraFooter;
        $this->extraFooterParsed = $this->extraFooterParsed;
        array_push( $errors, wfMessage( 'loopsettings-error' )  . ': ' . wfMessage( 'loopsettings-extra-footer-label' ) );
    }
    
    if ( empty( $request->getText( 'logo-use-custom' ) ) ) {
        $this->customLogo = '';
        $this->customLogoFileName = '';
        $this->customLogoFilePath = '';
    } else if ( $request->getText( 'logo-use-custom' ) == 'useCustomLogo' ) {
        $this->customLogo = 'useCustomLogo';
        if ( preg_match( '/[-a-zA-Z_\.\(\)0-9äöüØåæÅÆßÄÖÜØ]{1,}[\.]{1}[a-zA-Z]{3,}/', $request->getText( 'custom-logo-filename' ) ) ) {
            $this->customLogoFileName = $request->getText( 'custom-logo-filename' );
            $tmpParsedResult = $this->parse( '{{filepath:' . $request->getText( 'custom-logo-filename' ) . '}}' );
            preg_match( '/href="(.*)"/', $tmpParsedResult, $tmpOutputArray);
            if ( isset ( $tmpOutputArray[1] ) ) {
                $this->customLogoFilePath = $tmpOutputArray[1];
            } else {
                $this->customLogo = $previousLoopSettings->customLogo;
                $this->customLogoFileName = $previousLoopSettings->customLogoFileName;
                $this->customLogoFilePath = $previousLoopSettings->customLogoFilePath;
                array_push( $errors, wfMessage( 'loopsettings-error-customlogo-notfound' ) );
            }
        } else {
            $this->customLogo = $previousLoopSettings->customLogo;
            $this->customLogoFileName = $previousLoopSettings->customLogoFileName;
            $this->customLogoFilePath = $previousLoopSettings->customLogoFilePath;
            array_push( $errors, wfMessage( 'loopsettings-error' )  . ': ' . wfMessage( 'loopsettings-customlogo-label' ) );
        }
    } else {
        $this->customLogo = $previousLoopSettings->customLogo;
        $this->customLogoFileName = $previousLoopSettings->customLogoFileName;
        $this->customLogoFilePath = $previousLoopSettings->customLogoFilePath;
        array_push( $errors, wfMessage( 'loopsettings-error' )  . ': ' . wfMessage( 'loopsettings-customlogo-label' ) );
    }
        
    //var_dump($this);
    $this->addToDatabase();
    return true;

}
}