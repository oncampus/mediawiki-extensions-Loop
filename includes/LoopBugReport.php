<?php
/**
 * @description Ticket from users to admins/authors.
 * @ingroup Extensions
 * @author Dennis Krohn @krohnden <dennis.krohn@th-luebeck.de>
 */

if ( !defined( 'MEDIAWIKI' ) ) {
    die( "This file cannot be run standalone.\n" );
}

use MediaWiki\MediaWikiServices;

class LoopBugReport {

    public static function isAvailable() {
        if ( self::externalTicketService() ) {
            return "external";
        } elseif ( self::emailTickets() ) {
            return "internal";
        } else {
            return false;
        }
    }

    public static function externalTicketService() {
        global $wgLoopBugReportEnabled, $wgLoopExternalServiceBugReportUrl, $wgLoopExternalServiceUser, $wgLoopExternalServicePw;

        if ( !empty ( $wgLoopExternalServiceBugReportUrl ) && !empty ( $wgLoopExternalServiceUser ) && !empty ( $wgLoopExternalServicePw ) && $wgLoopBugReportEnabled ) {
            return true;
        }
        return false;
    }

    public static function emailTickets() {
        global $wgLoopBugReportEnabled, $wgLoopBugReportEmail;

        if ( !empty ( $wgLoopBugReportEmail ) && $wgLoopBugReportEnabled ) {
            return true;
        }
        return false;
    }

}


class SpecialLoopBugReport extends SpecialPage {

	public function __construct() {
		parent::__construct ( 'LoopBugReport' );
	}

	public function execute( $sub ) {
        global $wgRequest, $wgReCaptchaSiteKey, $wgReCaptchaSecretKey;

		$out = $this->getOutput();
		$request = $this->getRequest();
		$user = $this->getUser();
		Loop::handleLoopRequest( $out, $request, $user ); #handle editmode
   
		$out->setPageTitle ( $this->msg ( 'loopbugreport-specialpage-title' ) );
    
        $html = '<h1>';
	    $html .= wfMessage( 'loopbugreport-specialpage-title' )->text();
        $html .= '</h1>';
        
        $service = LoopBugReport::isAvailable();
        $page = urldecode ( $request->getText('page') );
        $url = urldecode ( $request->getText('url') );

        $captcha = new HTMLReCaptchaNoCaptchaField( [
            "key" => $wgReCaptchaSiteKey,
            "error" => null,
            "fieldname" => "g-recaptcha-response"
        ]);
        $captcha->mParent = $out;

        $email = urldecode ( $request->getText('email') );
        $message = urldecode ( $request->getText('message') );
        $response = urldecode( $request->getText('g-recaptcha-response') );

        if ( $user->isLoggedIn() ) {
            if ( $service != false ) {
                if ( !empty( $page ) && !empty( $url ) ) {
                    $html .= '<p>' . $this->msg( 'loopbugreport-desc' ) . '</p>';
                    $html .= '<form class="mw-editform mt-3 mb-3 ml-2 mr-2" id="bugreport-form" enctype="multipart/form-data">';
                    $html .= '<div class="form-group">';
                    
                    $html .= '<div class="form-row">';
                    $html .= '<label for="page" class="font-weight-bold">'. $this->msg("loopbugreport-page-label")->text().'</label>';
                    $html .= '<input class="mb-2 form-control" type="text" name="page" value="'.$page.'" disabled/>';
                    $html .= '<input class="d-none" type="text" name="url" value="'.$url.'"/>';
                    $html .= '</div>';

                    $html .= '<div class="form-row">';
                    $html .= '<label for="email" class="font-weight-bold">'. $this->msg("email")->text().'</label>';
                    $html .= '<input class="mb-2 form-control" type="email" name="email" required/>';
                    $html .= '</div>';
                    
                    $html .= '<div class="form-row">';
                    $html .= '<label for="message" class="font-weight-bold">'. $this->msg("loopbugreport-message-label")->text().'</label>';
                    $html .= '<textarea  class="mb-2 form-control" type="text" name="message" required></textarea>';
                    $html .= '</div>';
                              
                    $html .= $captcha->getInputHTML(1);
                    
                    $html .= '<input type="submit" class="mw-htmlform-submit mw-ui-button mw-ui-primary mw-ui-progressive mt-2 d-block" id="bugreport-submit" value="' . $this->msg( 'loopbugreport-send' ) . '"></input>';
                    
                    $html .= '</div></form>';
                } elseif ( !empty( $email ) && !empty( $message ) && !empty( $url ) ) { 
                    if( !empty( $response ) ) {
                        $data = [
                            'secret' => $wgReCaptchaSecretKey,
                            'response' => $response,
                            'remoteip' => $wgRequest->getIP()
                        ];

                        $url = 'https://www.google.com/recaptcha/api/siteverify';
                        $url = wfAppendQuery( $url, $data );
                        $request = MWHttpRequest::factory( $url, [ 'method' => 'GET' ] );
                        $status = $request->execute();
                        $result = FormatJson::decode( $request->getContent(), true );
                            
                        if( !$result['success'] ) {
                            $html .= '<div class="alert alert-warning" role="alert">' . $this->msg( "loopbugreport-error-nocaptcha" )->text() .'</div>';
                        }
                
                        global $wgCanonicalServer;

                        if ( $service == "external" ) {
                            global $wgLoopExternalServiceBugReportUrl, $wgLoopExternalServiceUser, $wgLoopExternalServicePw;

                            $params = array(
                                'bugreport_page' => $wgCanonicalServer . $url,
                                'bugreport_loop' => $wgCanonicalServer,
                                'bugreport_desc' => $message,
                                'bugreport_sender' => $email
                            );

                            $postfields = array(
                                "lang" => "de",
                                "username" => $wgLoopExternalServiceUser,
                                "password" => $wgLoopExternalServicePw,
                                "TRIGGER_login" => "1",
                                "host" => "webservice",
                                "svc" => "func",
                                "func" => "create_loopticket",
                                "ret" => "phpa",
                                "report" => "1_",
                                "elevel" => "4_",
                                "service_params" => array(0 => $params)
                            );

                            $postfields = http_build_query( $postfields );
                            $ch = curl_init();
                            curl_setopt( $ch, CURLOPT_URL, $wgLoopExternalServiceBugReportUrl);
                            curl_setopt( $ch, CURLOPT_FAILONERROR, 1 );
                            curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
                            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
                            curl_setopt( $ch, CURLOPT_TIMEOUT, 30 );
                            curl_setopt( $ch, CURLOPT_POST, 1 );
                            curl_setopt( $ch, CURLOPT_POSTFIELDS, $postfields );

                            $result = curl_exec( $ch );
                            curl_close($ch);

                            $tmp = ( array )unserialize( $result );
                            $webservice_result = $tmp["webservice/func"];
                            
                            if ( $webservice_result == 1 ) {
                                $html .= '<div class="alert alert-success" role="alert">' . $this->msg( "loopbugreport-success" )->text() .'</div>';
                            } else {
                                $html .= '<div class="alert alert-danger" role="alert">' . $this->msg( "loopbugreport-fail" )->text() .'</div>';
                            }
                            
                        } elseif ( $service == "internal" ) {
                            global $wgLoopBugReportEmail;

                            $subject = $this->msg( "loopbugreport-email-subject", str_replace( "https://", "", $wgCanonicalServer ), date("YmdHis") )->text(); 
                            $email = '<html><head><title>'.$subject.'</title></head><body>' . $this->msg("loopbugreport-email", $wgCanonicalServer, $email, $wgCanonicalServer . $url, $message )->parse() . '</body></html>';
                            $header[] = 'MIME-Version: 1.0';
                            $header[] = 'Content-type: text/html; charset=iso-8859-1';

                            $success = mail( $wgLoopBugReportEmail, $subject, $email, implode("\r\n", $header) );
                            
                            if ( $success ) {
                                $html .= '<div class="alert alert-success" role="alert">' . $this->msg( "loopbugreport-success" )->text() .'</div>';
                            } else {
                                $html .= '<div class="alert alert-danger" role="alert">' . $this->msg( "loopbugreport-fail" )->text() .'</div>';
                            }
                        }
                    } else {
                        $html .= '<div class="alert alert-warning" role="alert">' . $this->msg( "loopbugreport-error-nocaptcha" )->text() .'</div>';
                    }    
                } else {
                    $html .= '<div class="alert alert-warning" role="alert">' . $this->msg( "loopbugreport-error-nodata" )->text() .'</div>';
                }
            } else {
                $html .= '<div class="alert alert-warning" role="alert">' . $this->msg( "loopbugreport-error-configuration" )->text() .'</div>';
            }
        } else {
            $html .= '<div class="alert alert-warning" role="alert">' . $this->msg( 'specialpage-no-permission' )->text() .'</div>';
        }
        $out->addHtml ( $html );
    }

	protected function getGroupName() {
		return 'loop';
    }

}