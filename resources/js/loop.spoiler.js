/**
  * @description Script for <spoiler> tag (includes/LoopSpoiler.php)
  * @author Dustin Neß <dustin.ness@th-luebeck.de>
  */

$('.loopspoiler').click(function() {
  
  if($('.loopspoiler_default_content').length) {
    $('.loopspoiler_default_content').remove();
    return 0;
  } else if($('.loopspoiler_transparent_content').length) {
    $('.loopspoiler_transparent_content').remove();
    return 0;
  }

  let content = $(this).attr('data-spoilercontent');

  if($(this).hasClass('loopspoiler_type_transparent')) {
    type = 'transparent';
  } else {
    type = 'default';
  }

  $(this).after('<div class="loopspoiler_' + type + '_content">' + content + '</div>')

});