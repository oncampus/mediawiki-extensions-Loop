/**
  * @description Script for <spoiler> tag (includes/LoopSpoiler.php)
  * @author Dustin Neß <dustin.ness@th-luebeck.de>
  */

$('.loopspoiler').click(function() {
  let getId = $(this).attr('class').split(' ')[3];

  $(this).toggleClass('spoileractive');
  $('#' + getId).toggle();

  return false;
});
