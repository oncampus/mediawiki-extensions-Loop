CREATE TABLE IF NOT EXISTS /*_*/loop_literature_references (
  `llr_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `llr_itemkey` varchar(255) NOT NULL,
  `llr_pageid` int(10) NOT NULL,
  `llr_refid` varchar(255) NOT NULL,
  `llr_nthitem` int(10) NOT NULL,
  PRIMARY KEY (llr_id)
) /*$wgDBTableOptions*/;