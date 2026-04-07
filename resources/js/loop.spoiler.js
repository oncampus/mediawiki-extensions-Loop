/**
  * @description Script for <spoiler> tag (includes/LoopSpoiler.php)
  * @author Dustin Neß <dustin.ness@th-luebeck.de>
  * @author Dennis Krohn <dustin.ness@th-luebeck.de>
  */

$('.loopspoiler').click(function () {
	let id = $(this).attr("id");
	let $trigger = $(this);

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

	if ($trigger.attr("data-loaded") == "true") {
		let $content;

		if ($('.loopspoiler_default_content.' + id).length) {
			$content = $('.loopspoiler_default_content.' + id);
		} else if ($('.loopspoiler_transparent_content.' + id).length) {
			$content = $('.loopspoiler_transparent_content.' + id);
		}

		if ($content && $content.length) {
			$content.toggle();

			if ($content.is(':visible')) {
				$content.find('.smj-container').css('opacity', '1');
				typesetIfNeeded($content);
			}
		}
	} else {
		let content = $trigger.attr('data-spoilercontent');
		let type;

		if ($trigger.hasClass('loopspoiler_type_transparent')) {
			type = 'transparent';
		} else {
			type = 'default';
		}

		let regexp = /data-marker="(\w{8})"/g;
		let matches = content.matchAll(regexp);
		let contentids = [];
		let mathids = [];

		for (let match of matches) {
			contentids.push(match[1]);
		}
		contentids.sort();

		$('#' + id + " .replacemath").each(function () {
			mathids.push($(this).attr("id"));
		});

		for (let i = 0; i < mathids.length; i++) {
			content = content.replace(
				'<span class="loopspoiler_math_replace" data-marker="' + contentids[i] + '"></span>',
				$('#' + id + ' #' + mathids[i] + " .mwe-math-element").html()
			);
			$('#' + id + " .replacemath #" + mathids[i] + " .mwe-math-element").remove();
		}

		content = content.replace(/opacity\s*:\s*(?:\.5|0\.5)/g, 'opacity:1');

		$trigger.attr("data-loaded", true);

		let $newContent = $('<div class="loopspoiler_' + type + '_content ' + id + '">' + content + '</div>');
		$newContent.find('.smj-container').css('opacity', '1');
		$trigger.after($newContent);

		typesetIfNeeded($newContent);
	}

});
