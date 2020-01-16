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
INSERT INTO /*$wgDBprefix*/loop_structure_items (lsi_article,lsi_previous_article,lsi_next_article,lsi_parent_article,lsi_toc_level,lsi_sequence,lsi_toc_number,lsi_toc_text) SELECT ArticleId, PreviousArticleId, NextArticleId, ParentArticleId, TocLevel, Sequence, TocNumber, TocText FROM /*$wgDBprefix*/loopstructure;

-- Set default structure
INSERT INTO /*$wgDBprefix*/loop_structure_items (lsi_structure) VALUES (0);

-- Delete empty row
DELETE FROM /*$wgDBprefix*/loop_structure_items WHERE lsi_article = 0 AND lsi_next_article = 0;