/**
  * @description Script for <spoiler> tag (includes/LoopSpoiler.php)
  * @author Dustin Neß <dustin.ness@th-luebeck.de>
  */
 
// workaround to fix inline spoilers
// supposing it's surrounded by <p>-Tags
// $('.loopspoiler.loopspoiler_type_default').each(function() {
//   let spoiler = $(this).parent();
//   let before = spoiler.prev();
//   let after = spoiler.next();

//   before.append(spoiler[0].outerHTML, after.html());

//   spoiler.detach();
//   after.detach();
// });


$('.loopspoiler-container').on('click', '.loopspoiler', function() {
  let getId = $(this).attr('class').split(' ')[3];

  $(this).toggleClass('spoileractive');
  $('#' + getId).toggle();

  return false;
});