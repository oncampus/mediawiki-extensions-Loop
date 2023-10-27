CREATE TABLE IF NOT EXISTS /*_*/loop_used_tags (
  `ltu_article_id` varchar(255) NOT NULL,
  `ltu_tags_used` varchar(255) NOT NULL,
  PRIMARY KEY (ltu_article_id)
) /*$wgDBTableOptions*/;