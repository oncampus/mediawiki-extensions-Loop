/**
  * @description Script for <spoiler> tag (includes/LoopSpoiler.php)
  * @author Dustin Neß <dustin.ness@th-luebeck.de>
  */

$('.loopspoiler').click(function() {
  
  if ($(this).attr("data-loaded") == "true") {
    if($('.loopspoiler_default_content').length) {
      $('.loopspoiler_default_content').toggle();
    } else if($('.loopspoiler_transparent_content').length) {
      $('.loopspoiler_transparent_content').toggle();
    }
  } else {
    let content = $(this).attr('data-spoilercontent');

    if($(this).hasClass('loopspoiler_type_transparent')) {
      type = 'transparent';
    } else {
      type = 'default';
    }
    
    $('#' + $(this).attr("id") +  " .mwe-math-element").each(function() {
      content = content.replace('<span class="loopspoiler_math_replace"></span>', $(this).html() );
      $(this).remove();
    })
    $('#' + $(this).attr("id")).attr("data-loaded", true);
    $(this).after('<div class="loopspoiler_' + type + '_content">' + content + '</div>')
  
  }
  
});