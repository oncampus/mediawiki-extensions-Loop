<?php
/**
 * @description Ticket from users to admins/authors.
 * @ingroup Extensions
 * @author Dennis Krohn @krohnden <dennis.krohn@th-luebeck.de>
 */

if ( !defined( 'MEDIAWIKI' ) ) die ( "This file cannot be run standalone.\n" );

use MediaWiki\Extension\ConfirmEdit\ReCaptchaNoCaptcha\HTMLReCaptchaNoCaptchaField;

class LoopBugReport {

    public static function isAvailable() {
        if ( self::emailTickets() ) {
            return "internal";
        } else {
            return false;
        }
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

        $userEmail = urldecode ( $request->getText('email') );
        $message = urldecode ( $request->getText('message') );

        if ( $user->isRegistered() ) {
            if ( $service != false ) {
                if ( !empty( $page ) && !empty( $url ) ) {
                    $html .= $this->makeForm( $request, $page, $url );
                } elseif (!empty( $message ) && !empty( $url )) {
                    global $wgCanonicalServer;

                    if ( $service == "internal" ) {
                        global $wgLoopBugReportEmail;

                        $subject = $this->msg( "loopbugreport-email-subject", str_replace( "https://", "", $wgCanonicalServer ), date("YmdHis") )->text();
                        $email = '<html><head><title>'.$subject.'</title></head><body>' . $this->msg("loopbugreport-email", $wgCanonicalServer, $userEmail, $wgCanonicalServer . $url, $message )->parse() . '</body></html>';
						$options['contentType'] = 'text/html; charset=UTF-8';
						$to = new MailAddress($wgLoopBugReportEmail);
						$from = new MailAddress($wgLoopBugReportEmail);
						$status = UserMailer::send($to, $from, $subject, $email, $options);

                        if ( $status->isOK() ) {
							$html .= '<div class="alert alert-success" role="alert">' . $this->msg( "loopbugreport-success" )->text() .'</div>';
                        }  else {
                            $html .= '<div class="alert alert-danger" role="alert">' . $this->msg( "loopbugreport-fail" )->text() .'</div>';
                            $showForm = true;
                        }
                    }
                } else {
                    $html .= '<div class="alert alert-warning" role="alert">' . $this->msg( "loopbugreport-error-nodata" )->text() .'</div>';
                    $showForm = true;
                }
            } else {
                $html .= '<div class="alert alert-warning" role="alert">' . $this->msg( "loopbugreport-error-configuration" )->text() .'</div>';
            }
        } else {
            $html .= '<div class="alert alert-warning" role="alert">' . $this->msg( 'specialpage-no-permission' )->text() .'</div>';
        }

        if( isset($showForm) ) {
            $html .= $this->makeForm( $request, $page, $url, true );
        }
        $out->addHtml ( $html );
    }

    public function makeForm( $request, $page, $url, $error = false ) {
        $message = urldecode ( $request->getText('message') );
        $email = urldecode ( $request->getText('email') ) ?? '';

         if( $error) {
            // reconstruct page name
            $url = str_replace( '_', ' ', $url );
            $url = preg_replace( '^(.*[\\\/])^', '', $url ); // remove directory path
            $page = urldecode( $url );
        }

        $html = '<p>' . $this->msg( 'loopbugreport-desc' ) . '</p>';
        $html .= '<form class="mw-editform mt-3 mb-3 ml-2 mr-2" id="bugreport-form" enctype="multipart/form-data" >';
        $html .= '<div class="form-group">';

        $html .= '<div class="form-row">';
        $html .= '<label for="page" class="font-weight-bold">'. $this->msg("loopbugreport-page-label")->text().'</label>';
        $html .= '<input class="mb-2 form-control" type="text" name="page" value="' . $page . '" disabled/>';
        $html .= '<input class="d-none" type="text" name="url" value="' . $url . '"/>';
        $html .= '</div>';

        $html .= '<div class="form-row">';
        $html .= '<label for="email" class="font-weight-bold">'. $this->msg("email")->text().'</label>';
        $html .= '<input class="mb-2 form-control" type="email" name="email" value="' . $email . '">';
        $html .= '</div>';

        $html .= '<div class="form-row">';
        $html .= '<label for="message" class="font-weight-bold">'. $this->msg("loopbugreport-message-label")->text().'</label>';
        $html .= '<textarea  class="mb-2 form-control" type="text" name="message" required>' . $message . '</textarea>';
        $html .= '</div>';

        $html .= '<input type="submit" class="mw-htmlform-submit mw-ui-button mw-ui-primary mw-ui-progressive mt-2 d-block" id="bugreport-submit" value="' . $this->msg( 'loopbugreport-send' ) . '"></input>';

        $html .= '</div></form>';

        return $html;
    }

	protected function getGroupName() {
		return 'loop';
    }

}
