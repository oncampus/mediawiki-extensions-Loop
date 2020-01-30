$(document).ready(function() {
	/*
	* helper for star css, enable button
	*/
	$('.lf-rating-wrapper input[name=lf_rating]').on( "change", function() {
		$(this).parent().addClass("rated");

		if ( $('input[name=lf_rating]:checked').val() != undefined ) {
			$('#lf_send').prop("disabled", false);
		}
	});	
	
	/*
	* auto-resize for comment field
	*/
	
	$('#lf_comment').on( 'change', autosize );
				
	function autosize() {
		var el = this;
		setTimeout( function() {
			el.style.cssText = 'height:auto; padding:0';
			el.style.cssText = '-moz-box-sizing:content-box';
			el.style.cssText = 'height:' + el.scrollHeight + 'px';
		},0);
	}
});  


$( "#lf_send" ).click( function() {
	
	var api = new mw.Api();
	
	var numstars = $('[name="lf_rating"]').select().serializeArray(); 
	var lf_rating = numstars[0]['value'];
	var lf_comment = $('#lf_comment').val();
	var lf_articleid = mw.config.get( 'lfArticle' ).id;
	
	api.get( {
		'pageid': lf_articleid,
		'rating': lf_rating,
		'comment': lf_comment,
		'action': 'loopfeedback-save',
		'format': 'json'
	} )
	.done( function ( data ) {
		if ( 'loopfeedback-save' in data && 'lf_id' in data['loopfeedback-save'] ) {
			
			var resulthtml = '<p>'+ mw.message( 'loopfeedback-thanks-after-submit' ).text() +'</p>';					
			var view_results = mw.config.get( 'lfArticle' ).view_results;
			var view_comments = mw.config.get( 'lfArticle' ).view_comments;
			
			if (view_results == 1) {			
			
				api.get( {
					'pageid': lf_articleid,
					'action': 'loopfeedback-page-details',
					'format': 'json'
				} )
				.done( function ( data ) {
					if ( 'loopfeedback-page-details' in data && 'pageDetails' in data['loopfeedback-page-details'] ) {
						var pageDetails = data['loopfeedback-page-details']['pageDetails'];
						
							var resultStars = '<div class="lf_rating_wrapper mb-1">';
							resultStars = resultStars + '<span class="ic ic-star ' + ( parseInt( pageDetails.average_stars ) >= 1 ? 'lf-colour-active' : 'lf-colour-idle' ) + '"></span>';
							resultStars = resultStars + '<span class="ic ic-star ' + ( parseInt( pageDetails.average_stars ) >= 2 ? 'lf-colour-active' : 'lf-colour-idle' ) + '"></span>';
							resultStars = resultStars + '<span class="ic ic-star ' + ( parseInt( pageDetails.average_stars ) >= 3 ? 'lf-colour-active' : 'lf-colour-idle' ) + '"></span>';
							resultStars = resultStars + '<span class="ic ic-star ' + ( parseInt( pageDetails.average_stars ) >= 4 ? 'lf-colour-active' : 'lf-colour-idle' ) + '"></span>';
							resultStars = resultStars + '<span class="ic ic-star ' + ( parseInt( pageDetails.average_stars ) >= 5 ? 'lf-colour-active' : 'lf-colour-idle' ) + '"></span>';
							resultStars = resultStars + '</div>';

							resulthtml = resulthtml + '<div class="´mb-2"' + mw.message(  'loopfeedback-specialpage-feedback-info-sum-all', pageDetails.count_all ) + '</div>';
							resulthtml = resulthtml + resultStars;
							resulthtml = resulthtml + '<div class="´mb-2"' + mw.message(  'loopfeedback-specialpage-feedback-info-average-stars', pageDetails.average ) + '</div>';				

							if ( view_comments == 1 ) {	
								if ( parseInt(pageDetails.count_comments ) > 0 ) {
									resulthtml = resulthtml + mw.message(  'loopfeedback-specialpage-feedback-info-count-comments', pageDetails.count_comments);
								} else {
									resulthtml = resulthtml + mw.message(  'loopfeedback-specialpage-feedback-info-no-comments');
								}					
							}
							var resultlink = mw.config.get( 'lfArticle' ).resultlink;
							
							resulthtml = resulthtml + '<br/>'+resultlink;
						
							$("#lf_form").html(resulthtml);
							
					}
				});			
			
			}	
			$("#lf_form").html( resulthtml );
		}
	});
	
});