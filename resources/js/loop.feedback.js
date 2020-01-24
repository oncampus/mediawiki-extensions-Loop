
$(document).ready(function() {
	
	var lf_title = mw.config.get( 'lfArticle' ).title;
	
	if (mw.config.get( 'lfArticle' ).toclevel == 0) {
		var loopfeedback_feedback_for = mw.message( 'loopfeedback-feedback-for-module' , lf_title ).text();
		var loopfeedback_rating_descr = mw.message( 'loopfeedback-rating-descr-module').text();
	} else {
		var loopfeedback_feedback_for = mw.message( 'loopfeedback-feedback-for-chapter' , lf_title ).text();
		var loopfeedback_rating_descr = mw.message( 'loopfeedback-rating-descr-chapter').text();
	}
	
	var $sidebarFeedback = $( '<div id="sidebarFeedback" class="generated-sidebar">\
	<h2>'+ mw.message( 'loopfeedback-sidebar-headertext' ).text() +'</h2>\
	<div class="pBody">\
		<div id="lf_form">\
			<div id="lf_feedback_for">\
				'+ loopfeedback_feedback_for +'\
			</div>\
			<div id="lf_rating">\
				<div id="lf_rating_wrapper">\
					<div id="lf_rating_descr">'+ loopfeedback_rating_descr +'</div>\
					<div id="lf_rating_stars">\
						<input name="lf_rating" type="radio" title="'+ mw.message( 'loopfeedback-rating-value1' ).text() +'" value="1" class="star required"/>\
						<input name="lf_rating" type="radio" title="'+ mw.message( 'loopfeedback-rating-value2' ).text() +'" value="2" class="star"/>\
						<input name="lf_rating" type="radio" title="'+ mw.message( 'loopfeedback-rating-value3' ).text() +'" value="3" class="star"/>\
						<input name="lf_rating" type="radio" title="'+ mw.message( 'loopfeedback-rating-value4' ).text() +'" value="4" class="star"/>\
						<input name="lf_rating" type="radio" title="'+ mw.message( 'loopfeedback-rating-value5' ).text() +'" value="5" class="star"/>\
					</div>\
				</div>\
			</div>\
			<textarea id="lf_comment" placeholder="'+ mw.message( 'loopfeedback-comment-placeholder' ).text() +'"></textarea>\
			<input type="button" value="'+ mw.message( 'loopfeedback-submit-button-text' ).text() +'" id="lf_send"/>\
		</div>\
	</div>\
</div>' );

	
	
	
	$("#sidebar_right").append($sidebarFeedback);

	$('input[type=radio].star').rating();	

	
	/*
	* auto-resize for comment field
	*/
	
	var txt = $('#lf_comment'),  
		hiddenDiv = $(document.createElement('div')),  
		content = null;  
	txt.addClass('txtstuff');  
	hiddenDiv.addClass('hiddendiv common');  
	$('body').append(hiddenDiv);  
    txt.on('keyup', function () {
  
       content = $(this).val();  
  
        content = content.replace(/\n/g, '<br>');  
        hiddenDiv.html(content + '<br class="lbr">');  
  
        $(this).css('height', hiddenDiv.height());  
  
    })
});  


$( "#lf_send" ).click(function() {
	
	var api = new mw.Api();
	var pageid = mw.config.get('wgArticleId');
	
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
			var lf_id = data['loopfeedback-save']['lf_id'];
			
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
						

						
							var resultStars = '<div class="lf_rating_wrapper">';
							
							resultStars = resultStars + '<input name="lf_result" type="radio" class="star" value="1" title="'+pageDetails.average+'" disabled="disabled" ';
							if (parseInt(parseInt(pageDetails.average_stars)) == 1) {resultStars = resultStars + 'checked="checked" ';}
							resultStars = resultStars + '/>';
							resultStars = resultStars + '<input name="lf_result" type="radio" class="star" value="1" title="'+pageDetails.average+'" disabled="disabled" ';
							if (parseInt(pageDetails.average_stars) == 2) {resultStars = resultStars + 'checked="checked" ';}
							resultStars = resultStars + '/>';
							resultStars = resultStars + '<input name="lf_result" type="radio" class="star" value="3" title="'+pageDetails.average+'" disabled="disabled" ';
							if (parseInt(pageDetails.average_stars) == 3) {resultStars = resultStars + 'checked="checked" ';}
							resultStars = resultStars + '/>';
							resultStars = resultStars + '<input name="lf_result" type="radio" class="star" value="4" title="'+pageDetails.average+'" disabled="disabled" ';
							if (parseInt(pageDetails.average_stars) == 4) {resultStars = resultStars + 'checked="checked" ';}
							resultStars = resultStars + '/>';
							resultStars = resultStars + '<input name="lf_result" type="radio" class="star" value="5" title="'+pageDetails.average+'" disabled="disabled" ';
							if (parseInt(pageDetails.average_stars) == 5) {resultStars = resultStars + 'checked="checked" ';}					
							resultStars = resultStars + '/>';
							resultStars = resultStars + '</div>';
							
							
							

							
										
							resulthtml = resulthtml + mw.message(  'loopfeedback-specialpage-feedback-info-sum-all', pageDetails.count_all)+'<br/>';
							
							resulthtml = resulthtml + resultStars+'<br/>';
							
							resulthtml = resulthtml + mw.message(  'loopfeedback-specialpage-feedback-info-average-stars', pageDetails.average)+'<br/>';				

							
							if (view_comments == 1) {	
								if (parseInt(pageDetails.count_comments) > 0) {
									resulthtml = resulthtml + mw.message(  'loopfeedback-specialpage-feedback-info-count-comments', pageDetails.count_comments);
								} else {
									resulthtml = resulthtml + mw.message(  'loopfeedback-specialpage-feedback-info-no-comments');
								}					
							}
							var resultlink = mw.config.get( 'lfArticle' ).resultlink;
							
							resulthtml = resulthtml + '<br/><br/>'+resultlink;
						
							$("#lf_form").html(resulthtml);
							
							$('input[type=radio].star').rating();	
					}
				});			
			
			
			}	
			$("#lf_form").html(resulthtml);
			
			$('input[type=radio].star').rating();				
			
			/*
			$("#lf_form").html('<p>'+mw.message( 'loopfeedback-thanks-after-submit' ).text()+'</p>');
			*/
		}
	});
	
	
});



