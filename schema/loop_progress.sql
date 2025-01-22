CREATE TABLE IF NOT EXISTS /*_*/loop_progress (
	lp_id binary(32) NOT NULL PRIMARY KEY,
	lp_page integer unsigned NOT NULL,
	lp_user integer unsigned NOT NULL,
	lp_understood integer unsigned NOT NULL,
	lp_user_note TEXT binary NOT NULL DEFAULT '',
	lp_timestamp varbinary(14) NOT NULL DEFAULT ''
	) /*$wgDBTableOptions*/;
