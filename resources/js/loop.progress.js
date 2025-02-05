$(document).ready(function() {

	$(".note_text_title").click(function () {
		$(this).children().eq(1).toggleClass("note_text");
	});

	function resetUnderstoodFilters() {
		$("#not_edited_filter").addClass('not_active');
		$("#not_understood_filter").addClass('not_active');
		$("#understood_filter").addClass('not_active');

		$("#toc_filter_space").removeClass('show_not_edited_filter');
		$("#toc_filter_space").removeClass('show_not_understood_filter');
		$("#toc_filter_space").removeClass('show_understood');
	}

	$("#not_edited_filter").click( function () {
		if ($("#not_edited_filter").hasClass("not_active")) {
			resetUnderstoodFilters();
			$(this).removeClass("not_active");
			$("#toc_filter_space").toggleClass("show_not_edited_filter");
		} else {
			resetUnderstoodFilters();
			$("#toc_filter_space").addClass("show_all");
		}
	});

	$("#not_understood_filter").click( function () {
		if ($("#not_understood_filter").hasClass("not_active")) {
			resetUnderstoodFilters();
			$(this).removeClass("not_active");
			$("#toc_filter_space").toggleClass("show_not_understood_filter");
		} else {
			resetUnderstoodFilters();
			$("#toc_filter_space").addClass("show_all");
		}
	});

	$("#understood_filter").click( function () {
		if ($("#understood_filter").hasClass("not_active")) {
			resetUnderstoodFilters();
			$(this).removeClass("not_active");
			$("#toc_filter_space").toggleClass("show_understood");
		} else {
			resetUnderstoodFilters();
			$("#toc_filter_space").addClass("show_all");
		}
	});



	$("#personal_notes").on('input', function () {
		$(this).css('height', this.scrollHeight + 'px');
		$("#status_saved").removeClass("status-active");
		$("#status_not_saved").addClass("status-active");
		$("#save_note_button").removeClass("status-saved");
	});

	//$("#personal_notes").trigger('input');


	$('#page_understood').change(function() {
		alert($("#page_understood").val());
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
			} );
		} else {
			$("#page_understood").removeClass("understood")
			$("#page_understood").addClass("not_understood")

			api.post( {
				'pageid': lp_articleid,
				"understood": 0,
				'action': 'loopprogress-save',
				'format': 'json'
			} );
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
			$("#save_note_button").addClass("status-saved");
			$("#status_saved").addClass("status-active");
			$("#status_not_saved").removeClass("status-active");
		});
	});


	function resetButtonState() {
		$("#not_edited_button").addClass('not_active');
		$("#understood_button").addClass('not_active');
		$("#not_understood_button").addClass('not_active');
	}


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
		} );
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
		} );
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
		} );
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


