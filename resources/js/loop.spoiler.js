/**
  * @description Script for <spoiler> tag (includes/LoopSpoiler.php)
  */

$('.loopspoiler').click(function () {
	let $trigger = $(this);
	let id = $trigger.attr('id');

	$("html").css("background-color", $("#main-footer").css("background-color"));

	function typesetIfNeeded($element) {
		if (
			typeof window.MathJax !== 'undefined' &&
			typeof MathJax.typesetPromise === 'function'
		) {
			requestAnimationFrame(() => {
				MathJax.typesetPromise([$element[0]]).catch((err) => {
					console.error('MathJax typeset failed:', err);
				});
			});
		}
	}

	let $content = $('#' + id + '-content');

	$content.toggle();

	if ($content.is(':visible')) {
		$content.find('.smj-container').css('opacity', '1');
		typesetIfNeeded($content);
	}
});

