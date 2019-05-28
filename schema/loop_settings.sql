CREATE TABLE IF NOT EXISTS /*_*/loop_settings (
  `lset_structure` int(11) NOT NULL,
  `lset_property` varbinary(255) NOT NULL,
  `lset_value` blob
) /*$wgDBTableOptions*/;