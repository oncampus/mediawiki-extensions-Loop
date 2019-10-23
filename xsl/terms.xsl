<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:func="http://exslt.org/functions" extension-element-prefixes="func" xmlns:functx="http://www.functx.com">

	<xsl:param name="terms_file">
		<xsl:value-of select="'terms.xml'"/>
	</xsl:param>
 
	<func:function name="functx:get_term_name">
		<xsl:param name="term_name_key"/>
		<func:result select="document($terms_file)/terms/msg[(@name=$term_name_key) and (@lang=$lang)]"/>
	</func:function>
	<func:function name="functx:get_icon">
		<xsl:param name="term_icon_key"/>
		<func:result select="document($terms_file)/terms/msg[(@name=$term_icon_key)]"/>
	</func:function>

	<xsl:variable name="icon_figure" select="functx:get_icon('icon_figure')"/>
	<xsl:variable name="icon_formula" select="functx:get_icon('icon_formula')"/>
	<xsl:variable name="icon_listing" select="functx:get_icon('icon_listing')"/>
	<xsl:variable name="icon_media" select="functx:get_icon('icon_media')"/>
	<xsl:variable name="icon_table" select="functx:get_icon('icon_table')"/>
	<xsl:variable name="icon_task" select="functx:get_icon('icon_task')"/>
	<xsl:variable name="icon_rollover" select="functx:get_icon('icon_rollover')"/>
	<xsl:variable name="icon_video" select="functx:get_icon('icon_video')"/>
	<xsl:variable name="icon_interaction" select="functx:get_icon('icon_interaction')"/>
	<xsl:variable name="icon_click" select="functx:get_icon('icon_click')"/>
	<xsl:variable name="icon_audio" select="functx:get_icon('icon_audio')"/>
	<xsl:variable name="icon_animation" select="functx:get_icon('icon_animation')"/>
	<xsl:variable name="icon_simulation" select="functx:get_icon('icon_simulation')"/>
	<xsl:variable name="icon_dragdrop" select="functx:get_icon('icon_dragdrop')"/>
	<xsl:variable name="icon_print" select="functx:get_icon('icon_print')"/>

	<xsl:variable name="icon_timerequirement" select="functx:get_icon('icon_timerequirement')"/>
	<xsl:variable name="icon_learningobjectives" select="functx:get_icon('icon_learningobjectives')"/>
	<xsl:variable name="icon_arrangement" select="functx:get_icon('icon_arrangement')"/>
	<xsl:variable name="icon_example" select="functx:get_icon('icon_example')"/>
	<xsl:variable name="icon_reflection" select="functx:get_icon('icon_reflection')"/>
	<xsl:variable name="icon_notice" select="functx:get_icon('icon_notice')"/>
	<xsl:variable name="icon_important" select="functx:get_icon('icon_important')"/>
	<xsl:variable name="icon_annotation" select="functx:get_icon('icon_annotation')"/>
	<xsl:variable name="icon_definition" select="functx:get_icon('icon_definition')"/>
	<xsl:variable name="icon_markedsentence" select="functx:get_icon('icon_markedsentence')"/>
	<xsl:variable name="icon_sourcecode" select="functx:get_icon('icon_sourcecode')"/>
	<xsl:variable name="icon_summary" select="functx:get_icon('icon_summary')"/>
	<xsl:variable name="icon_indentation" select="functx:get_icon('icon_indentation')"/>
	<xsl:variable name="icon_norm" select="functx:get_icon('icon_norm')"/>
	<xsl:variable name="icon_law" select="functx:get_icon('icon_law')"/>
	<xsl:variable name="icon_question" select="functx:get_icon('icon_question')"/>
	<xsl:variable name="icon_practice" select="functx:get_icon('icon_practice')"/>
	<xsl:variable name="icon_exercise" select="functx:get_icon('icon_exercise')"/>
	<xsl:variable name="icon_websource" select="functx:get_icon('icon_websource')"/>
	<xsl:variable name="icon_experiment" select="functx:get_icon('icon_experiment')"/>
	<xsl:variable name="icon_citation" select="functx:get_icon('icon_citation')"/>

	<xsl:variable name="icon_h5p" select="functx:get_icon('icon_h5p')"/>
	<xsl:variable name="icon_quizlet" select="functx:get_icon('icon_quizlet')"/>
	<xsl:variable name="icon_slideshare" select="functx:get_icon('icon_slideshare')"/>
	<xsl:variable name="icon_prezi" select="functx:get_icon('icon_prezi')"/>
	<xsl:variable name="icon_padlet" select="functx:get_icon('icon_padlet')"/>
	<xsl:variable name="icon_learningapps" select="functx:get_icon('icon_learningapps')"/>
	<xsl:variable name="icon_ngspice" select="functx:get_icon('icon_ngspice')"/>
	<xsl:variable name="icon_zip" select="functx:get_icon('icon_zip')"/>
	<xsl:variable name="icon_youtube" select="functx:get_icon('icon_youtube')"/>

	<xsl:variable name="word_chapter" select="functx:get_term_name('word_chapter')"/>
	<xsl:variable name="word_state" select="functx:get_term_name('word_state')"/>
	<xsl:variable name="word_content" select="functx:get_term_name('word_content')"/>
	
	<xsl:variable name="word_figure" select="functx:get_term_name('word_figure')"/>
	<xsl:variable name="word_figure_short" select="functx:get_term_name('word_figure_short')"/>
	<xsl:variable name="phrase_figure" select="functx:get_term_name('phrase_figure')"/>	
	<xsl:variable name="phrase_figure_number" select="functx:get_term_name('phrase_figure_number')"/>	
	<xsl:variable name="word_list_of_figures" select="functx:get_term_name('word_list_of_figures')"/>
	
	<xsl:variable name="word_formula" select="functx:get_term_name('word_formula')"/>
	<xsl:variable name="word_formula_short" select="functx:get_term_name('word_formula_short')"/>
	<xsl:variable name="phrase_formula" select="functx:get_term_name('phrase_formula')"/>	
	<xsl:variable name="phrase_formula_number" select="functx:get_term_name('phrase_formula_number')"/>	
	<xsl:variable name="word_list_of_formulas" select="functx:get_term_name('word_list_of_formulas')"/>
	
	<xsl:variable name="word_listing" select="functx:get_term_name('word_listing')"/>
	<xsl:variable name="word_listing_short" select="functx:get_term_name('word_listing_short')"/>
	<xsl:variable name="phrase_listing" select="functx:get_term_name('phrase_listing')"/>	
	<xsl:variable name="phrase_listing_number" select="functx:get_term_name('phrase_listing_number')"/>	
	<xsl:variable name="word_list_of_listings" select="functx:get_term_name('word_list_of_listings')"/>
	
	<xsl:variable name="word_media" select="functx:get_term_name('word_media')"/>
	<xsl:variable name="word_media_short" select="functx:get_term_name('word_media_short')"/>
	<xsl:variable name="phrase_media" select="functx:get_term_name('phrase_media')"/>	
	<xsl:variable name="phrase_media_number" select="functx:get_term_name('phrase_media_number')"/>	
	<xsl:variable name="word_list_of_media" select="functx:get_term_name('word_list_of_media')"/>
	
	<xsl:variable name="word_table" select="functx:get_term_name('word_table')"/>
	<xsl:variable name="word_table_short" select="functx:get_term_name('word_table_short')"/>
	<xsl:variable name="phrase_table" select="functx:get_term_name('phrase_table')"/>	
	<xsl:variable name="phrase_table_number" select="functx:get_term_name('phrase_table_number')"/>	
	<xsl:variable name="word_list_of_tables" select="functx:get_term_name('word_list_of_tables')"/>
	
	<xsl:variable name="word_task" select="functx:get_term_name('word_task')"/>
	<xsl:variable name="word_task_short" select="functx:get_term_name('word_task_short')"/>
	<xsl:variable name="phrase_task" select="functx:get_term_name('phrase_task')"/>	
	<xsl:variable name="phrase_task_number" select="functx:get_term_name('phrase_task_number')"/>	
	<xsl:variable name="word_list_of_tasks" select="functx:get_term_name('word_list_of_tasks')"/>
	
	<xsl:variable name="word_appendix" select="functx:get_term_name('word_appendix')"/>
	
	<xsl:variable name="word_looparea_task" select="functx:get_term_name('word_looparea_task')"/>
	<xsl:variable name="word_looparea_timerequirement" select="functx:get_term_name('word_looparea_timerequirement')"/>
	<xsl:variable name="word_looparea_learningobjectives" select="functx:get_term_name('word_looparea_learningobjectives')"/>
	<xsl:variable name="word_looparea_arrangement" select="functx:get_term_name('word_looparea_arrangement')"/>
	<xsl:variable name="word_looparea_example" select="functx:get_term_name('word_looparea_example')"/>
	<xsl:variable name="word_looparea_reflection" select="functx:get_term_name('word_looparea_reflection')"/>
	<xsl:variable name="word_looparea_notice" select="functx:get_term_name('word_looparea_notice')"/>
	<xsl:variable name="word_looparea_important" select="functx:get_term_name('word_looparea_important')"/>
	<xsl:variable name="word_looparea_annotation" select="functx:get_term_name('word_looparea_annotation')"/>
	<xsl:variable name="word_looparea_definition" select="functx:get_term_name('word_looparea_definition')"/>
	<xsl:variable name="word_looparea_formula" select="functx:get_term_name('word_looparea_formula')"/>
	<xsl:variable name="word_looparea_markedsentence" select="functx:get_term_name('word_looparea_markedsentence')"/>
	<xsl:variable name="word_looparea_sourcecode" select="functx:get_term_name('word_looparea_sourcecode')"/>
	<xsl:variable name="word_looparea_summary" select="functx:get_term_name('word_looparea_summary')"/>
	<xsl:variable name="word_looparea_indentation" select="functx:get_term_name('word_looparea_indentation')"/>
	<xsl:variable name="word_looparea_norm" select="functx:get_term_name('word_looparea_norm')"/>
	<xsl:variable name="word_loopmedia_notice" select="functx:get_term_name('word_loopmedia_notice')"/>
	<xsl:variable name="word_looparea_law" select="functx:get_term_name('word_looparea_law')"/>
	<xsl:variable name="word_looparea_question" select="functx:get_term_name('word_looparea_question')"/>
	<xsl:variable name="word_looparea_practice" select="functx:get_term_name('word_looparea_practice')"/>
	<xsl:variable name="word_looparea_exercise" select="functx:get_term_name('word_looparea_exercise')"/>
	<xsl:variable name="word_looparea_websource" select="functx:get_term_name('word_looparea_websource')"/>
	<xsl:variable name="word_looparea_experiment" select="functx:get_term_name('word_looparea_experiment')"/>
	<xsl:variable name="word_looparea_citation" select="functx:get_term_name('word_looparea_citation')"/>	

	<xsl:variable name="word_spoiler_defaulttitle" select="functx:get_term_name('word_spoiler_defaulttitle')"/>
	<xsl:variable name="phrase_syntaxhighlight" select="functx:get_term_name('phrase_syntaxhighlight')"/>
	<xsl:variable name="phrase_looparea_start" select="functx:get_term_name('phrase_looparea_start')"/>
	<xsl:variable name="phrase_looparea_end" select="functx:get_term_name('phrase_looparea_end')"/>
	<xsl:variable name="phrase_spoiler_start" select="functx:get_term_name('phrase_spoiler_start')"/>
	<xsl:variable name="phrase_spoiler_end" select="functx:get_term_name('phrase_spoiler_end')"/>
	<xsl:variable name="word_glossary" select="functx:get_term_name('word_glossary')"/>
	<xsl:variable name="phrase_literature_list" select="functx:get_term_name('phrase_literature_list')"/>
	<xsl:variable name="word_literature_page" select="functx:get_term_name('word_literature_page')"/>
	<xsl:variable name="word_literature_pages" select="functx:get_term_name('word_literature_pages')"/>
	<xsl:variable name="word_bibliography" select="functx:get_term_name('word_bibliography')"/>
	<xsl:variable name="phrase_interactive_element" select="functx:get_term_name('phrase_interactive_element')"/>
	<xsl:variable name="word_index" select="functx:get_term_name('word_index')"/>
	<xsl:variable name="phrase_video" select="functx:get_term_name('phrase_video')"/>
	<xsl:variable name="phrase_youtube_video" select="functx:get_term_name('phrase_youtube_video')"/>

</xsl:stylesheet>