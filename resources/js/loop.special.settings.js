$( document ).ready( function () {
	var link = { 
		"footer-Facebook-icon": "footer-Facebook-url",
		"footer-Twitter-icon": "footer-Twitter-url",
		"footer-Youtube-icon": "footer-Youtube-url",
		"footer-Github-icon": "footer-Github-url",
		"footer-Instagram-icon": "footer-Instagram-url",
		"license-use-cc": "rights-type",
		"logo-use-custom": "custom-logo-filename"
	};
	
	$('#logo-hint').tooltip({ boundary: 'window' })
	
	$( "input, select, textfield" ).on("change", function() {
		var clicked = $(this).attr("id");
	
		if ( $( "#" + clicked ).is(":checked") == false ) {
			$("#" + link[clicked]).prop("disabled", true)
		} else {
			$("#" + link[clicked]).prop("disabled", false).focus()
		}
	})
	
	$( "input[name='render-objects']" ).on("change", function() {
		$(".ls-none, .ls-title, .ls-icon").removeClass("d-none");
		$(".ls-" + $(this).val() ).addClass("d-none");
		if ( $(this).val() != "marked" ) {
			$("#numbering-objects, input[name='numbering-type']").prop("disabled", true)
		} else {
			$("#numbering-objects").prop("disabled", false)
			if ( $("#numbering-objects").is(":checked") == true ) {
				$("input[name='numbering-type']").prop("disabled", false )
			} 
		}
	})
	
	
	$( "#numbering-objects" ).on("change", function() {
		if ( $( this ).is(":checked") == false ) {
			$( "input[name='numbering-type']" ).prop("disabled", true)
		} else {
			$( "input[name='numbering-type']" ).prop("disabled", false).focus()
		}
	})

	$( "input[name='numbering-type']" ).on("change", function() {
		$("#ls-ongoing, #ls-chapter").addClass("d-none");
		$("#ls-" + $(this).val() ).removeClass("d-none");
	})

	$( "#feedback-level" ).on("change", function() {
		if ( $("#feedback-level").val() == "none" ) {
			$( "#feedback-mode" ).prop("disabled", true)
		} else {
			$( "#feedback-mode" ).prop("disabled", false)
		}
	})
	
	/**
	 * Show invalid fields 
	 * of file description textfield. Changed marked with oc
	 * 
	 */
	
	// Fetch all the forms for custom Bootstrap validation
    var forms = document.getElementsByClassName('needs-validation');
    // Loop over them and prevent submission
    var validation = Array.prototype.filter.call(forms, function(form) {
      form.addEventListener('submit', function(event) {
        if (form.checkValidity() === false) {
          event.preventDefault();
          event.stopPropagation();
        }
        form.classList.add('was-validated');
				$id = $( ".invalid-feedback" ).parent().parent().parent(".tab-pane").attr("id")
				$( "#" + $id + "-tab" ).tab('show')

      }, false);
    });
    
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
	
	var copyText = document.getElementById("rss-link");
	$( "#rss-link-btn" ).click( function() {
		copyText.select();
		copyText.setSelectionRange(0, 99999);
		document.execCommand("copy");
		console.log(copyText);
	});
	$( "#rss-link" ).on( "change keyup", function() {
		var rsslink = copyText.value;
		$(this).val(rsslink);
	});
});