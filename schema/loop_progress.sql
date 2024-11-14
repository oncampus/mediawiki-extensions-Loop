CREATE TABLE IF NOT EXISTS /*_*/loop_progress (
	lp_id binary(32) NOT NULL PRIMARY KEY, /* varchar? */
	lp_page integer unsigned NOT NULL,
	lp_user integer unsigned NOT NULL,
	lp_understood integer unsigned NOT NULL,
	lp_user_note varchar(255) binary NOT NULL DEFAULT '',
	) /*$wgDBTableOptions*/;
