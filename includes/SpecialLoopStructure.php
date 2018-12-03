<?php

class SpecialLoopStructure extends SpecialPage {
	
	public function __construct() {
		parent::__construct( 'LoopStructure' );
	}
	
	public function execute( $sub ) {
		
		$user = $this->getUser();
		$this->setHeaders();
		$out = $this->getOutput();
		$out->setPageTitle( $this->msg( 'loopstructure-specialpage-title' ) );
		
		$loopStructure = new LoopStructure();
		$loopStructure->loadStructureItems();
		
		$loopEditMode = $this->getSkin()->getUser()->getOption( 'LoopEditMode', false, true );
		$loopRenderMode = $this->getSkin()->getUser()->getOption( 'LoopRenderMode' );
	
		$out->addHtml(Html::openElement(
				'h1',
				array(
					'id' => 'loopstructure-h1'
				)
			)
			. $this->msg( 'loopstructure-specialpage-title' )->parse()
		);
		
		if( ! $user->isAnon() && $user->isAllowed( 'loop-toc-edit' ) && $loopRenderMode == 'default' && $loopEditMode ) {
			
			# show link to the edit page if user is permitted
			
			$out->addHtml(
				Html::rawElement(
					'a',
					array(
						'href' => Title::newFromText( 'Special:LoopStructureEdit' )->getFullURL(),
						'id' => 'edittoclink',
						'class' => 'ml-2'
					),
					'<i class="ic ic-edit"></i>'
				)
			);
		}
		
		$out->addHtml(Html::closeElement(
					'h1'
				)
				. Html::rawElement(
				'div',
				array(
					'style' => 'white-space: pre;'
				),
				$loopStructure->render()
			)
		);
		
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


/**
 *  Special page representing the table of contents
 */
 
class SpecialLoopStructureEdit extends SpecialPage {
	
	public function __construct() {
		parent::__construct( 'LoopStructureEdit' );
	}

	public function execute( $sub ) {
		
		global $wgSecretKey;
		
		$user = $this->getUser();
		$this->setHeaders();
		$out = $this->getOutput();
		$out->setPageTitle( $this->msg( 'loopstructure-edit-specialpage-title' ) );

		$tabindex = 0;

        # headline output
        $out->addHtml(
            Html::rawElement(
                'h1',
                array(
                    'id' => 'loopstructure-h1'
                ),
                $this->msg( 'loopstructure-edit-specialpage-title' )->parse()
            )
        );

		$loopStructure = new LoopStructure();
		$loopStructure->loadStructureItems();
		$currentStructureAsWikiText = $loopStructure->renderAsWikiText();
		
        $request = $this->getRequest();
        $saltedToken = $user->getEditToken( $wgSecretKey, $request );
		$newStructureContent = $request->getText( 'loopstructure-content' );
		$requestToken = $request->getText( 't' );

		$userIsPermitted = (! $user->isAnon() && $user->isAllowed( 'loop-toc-edit' ));
		
		$error = false;
		$feedbackMessageClass = 'success';

		if( ! empty( $newStructureContent ) && ! empty( $requestToken )) {
			if( $userIsPermitted ) {
				if( $user->matchEditToken( $requestToken, $wgSecretKey, $request )) {

					# the content was changend
					# use local parser to get a default parsed result
					$localParser = new Parser();
					$tmpTitle = Title::newFromText( 'NO TITLE' );
                    $parserOutput = $localParser->parse( $newStructureContent, $tmpTitle, new ParserOptions() );

					if( is_object( $parserOutput )) {

						$parsedStructure = $parserOutput->mText;

						if( ! empty( $parsedStructure )) {

							$tmpLoopStructure = new LoopStructure();
							$parseResult = $tmpLoopStructure->setStructureItemsFromWikiText( $parsedStructure, $user );

							if( $parseResult !== false ) {

                                $newStructureContentParsedWikiText = $tmpLoopStructure->renderAsWikiText();

                                # if new parsed structure is different to the new one save it
                                if( $currentStructureAsWikiText != $newStructureContentParsedWikiText ) {

                                    $loopStructure->deleteItems();
                                    $loopStructure->setStructureItemsFromWikiText( $parsedStructure, $user );
                                    $loopStructure->saveItems();
                                    $currentStructureAsWikiText = $loopStructure->renderAsWikiText();

                                    # save success output
                                    $out->addHtml(
                                        Html::rawElement(
                                            'div',
                                            array(
                                                'name' => 'loopstructure-content',
                                                'class' => 'alert alert-'.$feedbackMessageClass
                                            ),
                                            $this->msg( 'loopstructure-save-success' )->parse()
                                        )
                                    );

                                } else {
                                    $error = $this->msg( 'loopstructure-save-equal-error' )->parse();
                                    $feedbackMessageClass = 'warning';
                                }

                            } else {
                                $error = $this->msg( 'loopstructure-save-parse-error' )->parse();
                                $feedbackMessageClass = 'danger';
                            }
								
						} else {
							$error = $this->msg( 'loopstructure-save-parsed-structure-error' )->parse();
                            $feedbackMessageClass = 'danger';
						}
					
					} else {
						$error = $this->msg( 'loopstructure-save-parse-error' )->parse();
                        $feedbackMessageClass = 'danger';
					}
					
				} else {
					$error = $this->msg( 'loop-token-error' )->parse();
                    $feedbackMessageClass = 'danger';
				}

			} else {
				$error = $this->msg( 'loop-permission-error' )->parse();
                $feedbackMessageClass = 'danger';
			}
			
		}

        # error message output (if exists)
        if( $error !== false ) {
            $out->addHTML(
                Html::rawElement(
                    'div',
                    array(
                        'class' => 'alert alert-'.$feedbackMessageClass,
                        'role' => 'alert'
                    ),
                    $error
                )
            );
        }
        
        if( $userIsPermitted ) {
        	
        	# user is permitted to edit the toc, print edit form here
        	
	        $out->addHTML(
	            Html::openElement(
	                'form',
	                array(
	                    'class' => 'mw-editform mt-3 mb-3',
	                    'id' => 'loopstructure-form',
	                    'method' => 'post',
	                    'enctype' => 'multipart/form-data'
	                )
	            )
	            . Html::rawElement(
	                'textarea',
	                array(
	                    'name' => 'loopstructure-content',
	                    'id' => 'loopstructure-textarea',
	                    'tabindex' => ++$tabindex,
	                    'class' => 'd-block mt-3',
	                ),
	                $currentStructureAsWikiText
	            )
	            . Html::rawElement(
	                'input',
	                array(
	                    'type' => 'hidden',
	                    'name' => 't',
	                    'id' => 'loopstructure-token',
	                    'value' => $saltedToken
	                )
	            )
	            . Html::rawElement(
	                'input',
	                array(
	                    'type' => 'submit',
	                    'tabindex' => ++$tabindex,
	                    'class' => 'mw-htmlform-submit mw-ui-button mw-ui-primary mw-ui-progressive mt-2',
	                    'id' => 'loopstructure-submit',
	                    'value' => $this->msg( 'submit' )->parse()
	                )
	            ) . Html::closeElement(
	                'form'
	            )
	        );
	        
        } else {

        	# user has no permission, just show content without textarea
        	
        	$out->addHtml(
        		Html::rawElement(
        			'div',
        			array(
        				'class' => 'alert alert-dark',
        				'role' => 'alert',
        				'style' => 'white-space: pre;'
        			),
        			$currentStructureAsWikiText
        		)
        	);

        }

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
