$( document ).ready( function () {
	var link = { 
		"footer-Facebook-icon": "footer-Facebook-link",
		"footer-Twitter-icon": "footer-Twitter-link",
		"footer-Youtube-icon": "footer-Youtube-link",
		"footer-Github-icon": "footer-Github-link",
		"footer-Instagram-icon": "footer-Instagram-link",
		"license-use-cc": "rights-type",
		"extra-footer-active": "extra-footer-wikitext",
		"logo-use-custom": "custom-logo-filename"
	};
	
	$( "input, select, textfield" ).on("change", function() {
		var clicked = $(this).attr("id");
	
		if ( $( "#" + clicked ).is(":checked") == false ) {
		$("#" + link[clicked]).prop("disabled", true)
		} else {
		$("#" + link[clicked]).prop("disabled", false).focus()
		}
		/* 
		var empty = 0;
		$( "input[required], textfield[required]textfield[enabled], select[required]" ).each(function(){

			if ( $(this).val() == "" ) {
				empty++; 
			}
			if ( empty > 0 ) {
				$( "#loopstructure-submit" ).prop("disabled", true);
			} else {
				$( "#loopstructure-submit" ).prop("disabled", false);
			}
		})
		*/
		
	})
	$( ".upload-button" ).click( function() {
		var uploadDialog = new mw.Upload.Dialog();
		var windowManager = new OO.ui.WindowManager();
		$( 'body' ).append( windowManager.$element );
		windowManager.addWindows( [ uploadDialog ] );
		windowManager.openWindow( uploadDialog );
	})
	
	/**
	 * Adopted from mediawiki.Upload.BookletLayout.js to modify behaviour 
	 * of file description textfield. Changed marked with oc
	 * 
	 * Renders and returns the information form for collecting
	 * metadata and sets the {@link #infoForm infoForm}
	 * property.
	 *
	 * @protected
	 * @return {OO.ui.FormLayout}
	 */
	mw.Upload.BookletLayout.prototype.renderInfoForm = function () {
		var fieldset;

		this.filePreview = new OO.ui.Widget( {
		classes: [ 'mw-upload-bookletLayout-filePreview' ]
		} );
		this.progressBarWidget = new OO.ui.ProgressBarWidget( {
		progress: 0
		} );
		this.filePreview.$element.append( this.progressBarWidget.$element );

		this.filenameWidget = new OO.ui.TextInputWidget( {
		indicator: 'required',
		required: false,
		validate: /.+/
		} );
		this.descriptionWidget = new OO.ui.MultilineTextInputWidget( {
		indicator: 'required',
		required: false, // changed oc
		// validate: /\S+/, // changed oc
		autosize: true
		} );

		fieldset = new OO.ui.FieldsetLayout( {
		label: mw.msg( 'upload-form-label-infoform-title' )
		} );
		fieldset.addItems( [
		new OO.ui.FieldLayout( this.filenameWidget, {
			label: mw.msg( 'upload-form-label-infoform-name' ),
			align: 'top',
			help: mw.msg( 'upload-form-label-infoform-name-tooltip' )
		} ),
		new OO.ui.FieldLayout( this.descriptionWidget, {
			label: mw.msg( 'upload-form-label-infoform-description' ),
			align: 'top',
			help: mw.msg( 'upload-form-label-infoform-description-tooltip' )
		} )
		] );
		this.infoForm = new OO.ui.FormLayout( {
		classes: [ 'mw-upload-bookletLayout-infoForm' ],
		items: [ this.filePreview, fieldset ]
		} );

		this.on( 'fileUploadProgress', function ( progress ) {
		this.progressBarWidget.setProgress( progress * 100 );
		}.bind( this ) );

		this.filenameWidget.on( 'change', this.onInfoFormChange.bind( this ) );
		this.descriptionWidget.on( 'change', this.onInfoFormChange.bind( this ) );

		return this.infoForm;
	};
});