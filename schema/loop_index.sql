CREATE TABLE IF NOT EXISTS /*_*/loop_index (
  `li_index` varchar(255) NOT NULL,
  `li_refid` varchar(255) NOT NULL,
  `li_pageid` varchar(255) NOT NULL,
  PRIMARY KEY (li_refid)
) /*$wgDBTableOptions*/;