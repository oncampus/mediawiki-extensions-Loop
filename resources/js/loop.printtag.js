/**
  * @description Script for <loop_print> tag (includes/LoopPrint.php)
  * @author Dustin Neß <dustin.ness@th-luebeck.de>
  */
console.log("hi")
$('.loopprint-tag').click(function() {
    if($(this).parent('.loopprint-button').length <= 0) return;

    let getId = $(this).attr('class').split(' ')[1];
    let btnIcon = '<span class="ic-print-area"></span>';
    let btnText = btnIcon + $(this).attr('data-title');

    $('#' + getId).toggle();
    $(this).toggleClass('printbuttonactive');
    $(this).html($(this).html() == btnText ? btnIcon : btnText);

    return false;
});