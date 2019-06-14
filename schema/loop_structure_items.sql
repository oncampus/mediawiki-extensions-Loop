-- Add table for loop structure items
CREATE TABLE IF NOT EXISTS /*_*/loop_structure_items (
	lsi_id int(10) unsigned NOT NULL AUTO_INCREMENT,
	lsi_structure int(10) unsigned NOT NULL,
	lsi_article int(10) unsigned NOT NULL,
	lsi_previous_article int(10) unsigned NOT NULL,
	lsi_next_article int(10) unsigned NOT NULL,
	lsi_parent_article int(10) unsigned NOT NULL,
	lsi_toc_level int(10) unsigned NOT NULL,
	lsi_sequence int(10) unsigned NOT NULL,
	lsi_toc_number varbinary(255) NOT NULL,
	lsi_toc_text varbinary(255) NOT NULL,
	PRIMARY KEY (lsi_id),
	UNIQUE KEY structure_article (lsi_structure,lsi_article)
)/*$wgDBTableOptions*/;

-- Migrate old structures items
INSERT INTO /*$wgDBprefix*/loop_structure_items (lsi_structure,lsi_article,lsi_previous_article,lsi_next_article,lsi_parent_article,lsi_toc_level,lsi_sequence,lsi_toc_number,lsi_toc_text) SELECT IndexArticleId, ArticleId, PreviousArticleId, NextArticleId, ParentArticleId, TocLevel, Sequence, TocNumber, TocText FROM /*$wgDBprefix*/loopstructure;

-- Migate toc page in new namespace
UPDATE page SET page_namespace = 3100, page_title = (SELECT lsi_toc_text from loop_structure_items WHERE lsi_toc_level = 0) WHERE page_title = 'Loop_toc' AND page_namespace = 8;

-- Delete old table
--DROP TABLE IF EXISTS /*$wgDBprefix*/loopstructure; 