CREATE TABLE IF NOT EXISTS /*_*/loop_literature_references (
  `llr_itemkey` varchar(255) NOT NULL,
  `llr_pageid` int(10) NOT NULL,
  `llr_nthoftype` int(10) NOT NULL,
  `llr_refid` varbinary(255) NOT NULL,
  PRIMARY KEY (llr_itemkey)
) /*$wgDBTableOptions*/;