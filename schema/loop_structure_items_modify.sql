-- Migrate old structures items
INSERT INTO /*$wgDBprefix*/loop_structure_items (lsi_article,lsi_previous_article,lsi_next_article,lsi_parent_article,lsi_toc_level,lsi_sequence,lsi_toc_number,lsi_toc_text) SELECT ArticleId, PreviousArticleId, NextArticleId, ParentArticleId, TocLevel, Sequence, TocNumber, TocText FROM /*$wgDBprefix*/loopstructure;

-- Set default structure
INSERT INTO /*$wgDBprefix*/loop_structure_items (lsi_structure) VALUES (0);

-- Delete empty row
DELETE FROM /*$wgDBprefix*/loop_structure_items WHERE lsi_article = 0 AND lsi_next_article = 0;