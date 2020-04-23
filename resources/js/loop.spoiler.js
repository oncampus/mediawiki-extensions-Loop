/**
  * @description Script for <spoiler> tag (includes/LoopSpoiler.php)
  * @author Dustin Neß <dustin.ness@th-luebeck.de>
  */

// LoopSpoiler.php

var spoilerContents = mw.config.get('wgLoopSpoilerContents');


$('.loopspoiler_type_default').click(function() {
  
  if($('.loopspoiler_default_content').length) {
    $('.loopspoiler_default_content').remove();
    return 0;
  }

  let btnId = $(this).attr('class').split(' ').pop();
  
  if(btnId in spoilerContents) {
    $(this).after('<div class="loopspoiler_default_content">' + spoilerContents[btnId] + '</div>')
  }

});

$('.loopspoiler_type_transparent').click(function() {
  
  if($('.loopspoiler_transparent_content').length) {
    $('.loopspoiler_transparent_content').remove();
    return 0;
  }

  let btnId = $(this).attr('class').split(' ').pop();
  
  if(btnId in spoilerContents) {
    $(this).after('<div class="loopspoiler_transparent_content">' + spoilerContents[btnId] + '</div>')
  }

});

// $('.loopspoiler-container').click(function() {
//   let getId = $(this).attr('class').split(' ')[3];

//   $(this).toggleClass('spoileractive');
//   $('#' + getId).toggle();

//   return false;
// });