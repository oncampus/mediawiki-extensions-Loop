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

class LoopTicket {

    public $loop;
    public $page;
    public $useremail;
    public $message;

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
        global $wgLoopExternalServiceUrl, $wgLoopExternalServiceUser, $wgLoopExternalServicePw;

        if ( !empty ( $wgLoopExternalServiceUrl ) && !empty ( $wgLoopExternalServiceUser ) && !empty ( $wgLoopExternalServicePw ) ) {
            return true;
        }
        return false;
    }

    public static function emailTickets() {
        global $wgLoopTicketsEmail;

        if ( !empty ( $wgLoopTicketsEmail ) ) {
            return true;
        }
        return false;
    }

}


class SpecialLoopTicket extends SpecialPage {

	public function __construct() {
		parent::__construct ( 'LoopTicket' );
	}

	public function execute( $sub ) {

		$out = $this->getOutput();
		$request = $this->getRequest();
		$user = $this->getUser();
		Loop::handleLoopRequest( $out, $request, $user ); #handle editmode
		
		$out->setPageTitle ( $this->msg ( 'looptickets-specialpage-title' ) );
	    $html = '<h1>';
	    $html .= wfMessage( 'looptickets-specialpage-title' )->text();
        $html .= '</h1>';
        
        $service = LoopTicket::isAvailable();
        $page = urldecode ( $request->getText('page') );
        $url = urldecode ( $request->getText('url') );
        
        $email = urldecode ( $request->getText('email') );
        $message = urldecode ( $request->getText('message') );
        
        if ( $user->isLoggedIn() ) {
            if ( $service != false ) {
                if ( !empty( $page ) && !empty( $url ) ) {

                    $html .= '<form class="mw-editform mt-3 mb-3 ml-2 mr-2" id="bugreport-form" enctype="multipart/form-data">';
                    $html .= '<div class="form-group">';
                    
                    $html .= '<div class="form-row">';
                    $html .= '<label for="page">'. $this->msg("looptickets-page-label")->text().'</label>';
                    $html .= '<input class="mb-2 form-control" type="text" name="page" value="'.$page.'" disabled/>';
                    $html .= '<input class="d-none" type="text" name="url" value="'.$url.'"/>';
                    $html .= '</div>';

                    $html .= '<div class="form-row">';
                    $html .= '<label for="email">'. $this->msg("email")->text().'</label>';
                    $html .= '<input class="mb-2 form-control" type="email" name="email" value="'. $user->getEmail() .'" required/>';
                    $html .= '</div>';
                    
                    $html .= '<div class="form-row">';
                    $html .= '<label for="message">'. $this->msg("looptickets-message-label")->text().'</label>';
                    $html .= '<textarea  class="mb-2 form-control" type="text" name="message" required></textarea>';
                    $html .= '</div>';
                    
                    $html .= '<input type="submit" class="mw-htmlform-submit mw-ui-button mw-ui-primary mw-ui-progressive mt-2 d-block" id="bugreport-submit" value="' . $this->msg( 'looptickets-send' ) . '"></input>';
                    
                    $html .= '</div></form>';

                    
                } elseif ( !empty( $email ) && !empty( $message ) && !empty( $url ) ) {
                    global $wgCanonicalServer;

                    if ( $service == "external" ) {
                        global $wgLoopExternalServiceUrl, $wgLoopExternalServiceUser, $wgLoopExternalServicePw;

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
                        curl_setopt( $ch, CURLOPT_URL, $wgLoopExternalServiceUrl);
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
                            $html .= '<div class="alert alert-success" role="alert">' . $this->msg( "looptickets-success" )->text() .'</div>';
                        } else {
                            $html .= '<div class="alert alert-danger" role="alert">' . $this->msg( "looptickets-fail" )->text() .'</div>';
                        }
                        
                    } elseif ( $service == "internal" ) {
                        global $wgLoopTicketsEmail;

                        $subject = $this->msg( "looptickets-email-subject", str_replace( "https://", "", $wgCanonicalServer ), date("YmdHis") )->text(); 
                        $email = '<html><head><title>'.$subject.'</title></head><body>' . $this->msg("looptickets-email", $wgCanonicalServer, $email, $wgCanonicalServer . $url, $message )->parse() . '</body></html>';
                        $header[] = 'MIME-Version: 1.0';
                        $header[] = 'Content-type: text/html; charset=iso-8859-1';

                        $success = mail( $wgLoopTicketsEmail, $subject, $email, implode("\r\n", $header) );
                        
                        if ( $success ) {
                            $html .= '<div class="alert alert-success" role="alert">' . $this->msg( "looptickets-success" )->text() .'</div>';
                        } else {
                            $html .= '<div class="alert alert-danger" role="alert">' . $this->msg( "looptickets-fail" )->text() .'</div>';
                        }
                    }

                } else {
                    $html .= '<div class="alert alert-warning" role="alert">' . $this->msg( "looptickets-error-nodata" )->text() .'</div>';
                }
            } else {
                $html .= '<div class="alert alert-warning" role="alert">' . $this->msg( "looptickets-error-configuration" )->text() .'</div>';
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