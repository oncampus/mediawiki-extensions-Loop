CREATE TABLE IF NOT EXISTS /*_*/loop_settings (
  `lset_imprintlink` varchar(255) NOT NULL,
  `lset_privacylink` varchar(255) NOT NULL,
  `lset_oncampuslink` varchar(255) NOT NULL,
  `lset_rightstext` varchar(255) NOT NULL,
  `lset_rightstype` varchar(255) NOT NULL,
  `lset_rightsurl` varchar(255) NOT NULL,
  `lset_rightsicon` varchar(255) NOT NULL,
  `lset_customlogo_use` varchar(255) NOT NULL,
  `lset_customlogo_filename` varchar(255) NOT NULL,
  `lset_customlogo_filepath` varchar(255) NOT NULL,
  `lset_languagecode` varchar(255) NOT NULL,
  `lset_soc_fb_icon` varchar(255) NOT NULL,
  `lset_soc_fb_link` varchar(255) NOT NULL,
  `lset_soc_tw_icon` varchar(255) NOT NULL,
  `lset_soc_tw_link` varchar(255) NOT NULL,
  `lset_soc_yt_icon` varchar(255) NOT NULL,
  `lset_soc_yt_link` varchar(255) NOT NULL,
  `lset_soc_gh_icon` varchar(255) NOT NULL,
  `lset_soc_gh_link` varchar(255) NOT NULL,
  `lset_soc_in_icon` varchar(255) NOT NULL,
  `lset_soc_in_link` varchar(255) NOT NULL
) /*$wgDBTableOptions*/;