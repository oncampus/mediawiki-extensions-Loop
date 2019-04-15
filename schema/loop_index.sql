CREATE TABLE IF NOT EXISTS /*_*/loop_index (
  `li_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `li_pageid` varbinary(255) NOT NULL,
  `li_refid` varbinary(255) NOT NULL,
  `li_nthoftype` varbinary(255) NOT NULL,
  `li_index` varbinary(255) NOT NULL,
  `li_itemtype` varbinary(255),
  `li_itemtitle` varbinary(255),
  `li_itemdesc` varbinary(255),
  `li_thumb` varbinary(255),
  PRIMARY KEY (li_id)
) /*$wgDBTableOptions*/;