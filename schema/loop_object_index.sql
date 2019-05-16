CREATE TABLE IF NOT EXISTS /*_*/loop_object_index (
  `loi_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `loi_pageid` varbinary(255) NOT NULL,
  `loi_refid` varbinary(255) NOT NULL,
  `loi_nthoftype` varbinary(255) NOT NULL,
  `loi_index` varbinary(255) NOT NULL,
  `loi_itemtype` varbinary(255),
  `loi_itemtitle` varbinary(255),
  `loi_itemdesc` varbinary(255),
  `loi_itemthumb` varbinary(255),
  PRIMARY KEY (loi_id)
) /*$wgDBTableOptions*/;