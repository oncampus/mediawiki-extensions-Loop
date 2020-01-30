CREATE TABLE IF NOT EXISTS /*_*/loop_feedback (
  lf_id binary(32) NOT NULL PRIMARY KEY,
  lf_page integer unsigned NOT NULL,
  lf_user integer unsigned NOT NULL,
  lf_user_text varchar(255) binary NOT NULL DEFAULT '',
  lf_rating integer unsigned NOT NULL DEFAULT 0,
  lf_comment mediumblob NOT NULL DEFAULT '',
  lf_timestamp varbinary(14) NOT NULL DEFAULT '',
  lf_archive_timestamp varbinary(14) NOT NULL DEFAULT ''
) /*$wgDBTableOptions*/;