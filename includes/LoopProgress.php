<?php

class LoopProgress
{
	public static function showProgressBar(){
		return true;
	}

	public static function renderProgressBar() {
		return "Test renderFeedbackBox";
	}

	public static function showProgress() {
		return true;
	}

	public static function renderProgress() {
		return "Test renderProgress";
	}

	public static function onBeforePageDisplay(OutputPage $out, Skin $skin)
	{
		$out->addModules("loop.progress.js");
		//$out->addJsConfigVars('lfArticle', $lf_arcticle);

		return true;
	}

}
