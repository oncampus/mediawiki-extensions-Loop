/**
  * @description Script for <spoiler> tag (includes/LoopSpoiler.php)
  * @author Dustin Neß <dustin.ness@th-luebeck.de>
  * @author Dennis Krohn <dustin.ness@th-luebeck.de>
  */

$('.loopspoiler').click(function() {
  let id = $(this).attr("id");
  $("html").css("background-color", $("#main-footer").css("background-color"));
  if ($(this).attr("data-loaded") == "true") {
    if($('.loopspoiler_default_content.'+id).length) {
      $('.loopspoiler_default_content.'+id).toggle();
    } else if($('.loopspoiler_transparent_content.'+id).length) {
      $('.loopspoiler_transparent_content.'+id).toggle();
    }
  } else {
    let content = $(this).attr('data-spoilercontent');

    if($(this).hasClass('loopspoiler_type_transparent')) {
      type = 'transparent';
    } else {
      type = 'default';
    }
    
    let regexp = /data-marker="(\w{8})"/g;
    let matches = content.matchAll(regexp);
    let contentids = new Array;
    let mathids = new Array;

    for ( let match of matches ) {
      contentids.push(match[1]);
    }
    contentids.sort();

    $('#' + $(this).attr("id") +  " .replacemath").each(function() {
      mathids.push($(this).attr("id"));
    })
    for ( let i = 0; i < mathids.length; i++ ) {
      content = content.replace('<span class="loopspoiler_math_replace" data-marker="'+contentids[i]+'"></span>', $('#' + $(this).attr("id") +  ' #' + mathids[i] + " .mwe-math-element").html() );
      $('#' + $(this).attr("id") + " .replacemath #"+mathids[i] + " .mwe-math-element").remove();
    }
    $('#' + $(this).attr("id")).attr("data-loaded", true);
    $(this).after('<div class="loopspoiler_' + type + '_content ' + id + '">' + content + '</div>')
    
  }
  
});