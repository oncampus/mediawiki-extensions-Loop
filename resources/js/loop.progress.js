$(document).ready(function() {

	$(".note_text_title").click(function () {
		$(this).children().eq(1).toggleClass("note_text");
	});

	// todo transfer to anohter js class because it is only needed on one page
	$("#not_edited_filter").click( function () {
		//alert("not_edited_filter");
		$(this).toggleClass("not_active");
		//$(this).css('opacity', 1);
		$("#toc_filter_space").toggleClass("show_not_edited_filter");
	});

	//todo create this filter
	$("#not_understood_filter").click( function () {
		$(this).toggleClass("not_active");
		//alert("not_understood_filter");
		$("#toc_filter_space").toggleClass("show_not_understood_filter");
		//console.log("not implemented");
	});

	$("#understood_filter").click( function () {
		//alert("understood_filter");
		$(this).toggleClass("not_active");
		$("#toc_filter_space").toggleClass("show_understood");
	});



	$("#personal_notes").on('input', function () {
		$(this).css('height', this.scrollHeight + 'px');
	});

	$("#personal_notes").trigger('input');


	$('#page_understood').change(function() {
		alert($("#page_understood").val()); //alert("test");
		var api = new mw.Api();
		var lp_articleid = mw.config.get( 'lpArticle' ).id;

		if($("#page_understood").val() == "understood"){
			$("#page_understood").addClass("understood")
			$("#page_understood").removeClass("not_understood")

			api.post( {
				'pageid': lp_articleid,
				"understood": 1,
				'action': 'loopprogress-save',
				'format': 'json'
			} ).done( function ( data ) {
				alert("neuer test123");
			});
		} else {
			$("#page_understood").removeClass("understood")
			$("#page_understood").addClass("not_understood")

			api.post( {
				'pageid': lp_articleid,
				"understood": 0,
				'action': 'loopprogress-save',
				'format': 'json'
			} ).done( function ( data ) {
				//alert("neuer test123");
			});
		}
	});


	$("#save_note_button").click( function () {
		var api = new mw.Api();
		var personal_notes = $("#personal_notes").val();
		var lp_articleid = mw.config.get( 'lpArticle' ).id;

		api.post( {
			'pageid': lp_articleid,
			'user_note': personal_notes,
			'action': 'loopprogress-save-note',
			'format': 'json'
		} ).done( function ( data ) {
			// todo feedback for user
		});
	});


	function resetButtonState() {
		$("#not_edited_button").addClass('not_active');
		$("#understood_button").addClass('not_active');
		$("#not_understood_button").addClass('not_active');
	}


	// todo for this 3 buttons reset the state for not active class
	$("#not_edited_button").click( function () {
		var api = new mw.Api();
		var lp_articleid = mw.config.get( 'lpArticle' ).id;

		resetButtonState();
		$(this).removeClass('not_active');

		api.post( {
			'pageid': lp_articleid,
			"understood": 3,
			'action': 'loopprogress-save',
			'format': 'json'
		} ).done( function ( data ) {
			//alert("not_edited_button");
		});
	});

	$("#understood_button").click( function () {
		var api = new mw.Api();
		var lp_articleid = mw.config.get( 'lpArticle' ).id;

		resetButtonState();
		$(this).removeClass('not_active');

		api.post( {
			'pageid': lp_articleid,
			"understood": 1,
			'action': 'loopprogress-save',
			'format': 'json'
		} ).done( function ( data ) {
			//alert("not_edited_button");
		});
	});

	$("#not_understood_button").click( function () {
		var api = new mw.Api();
		var lp_articleid = mw.config.get( 'lpArticle' ).id;

		resetButtonState();
		$(this).removeClass('not_active');

		api.post( {
			'pageid': lp_articleid,
			"understood": 0,
			'action': 'loopprogress-save',
			'format': 'json'
		} ).done( function ( data ) {
			//alert("not_edited_button");
		});
	});

	var clicked = false;
	$("#extend-all").click( function () {
		if(clicked) {
			$('#note-collection input[type="checkbox"]').prop('checked', false);
			clicked = false;
		} else {
			$('#note-collection input[type="checkbox"]').prop('checked', true);
			clicked = true;
		}
	});

});


