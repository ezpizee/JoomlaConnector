DROP TABLE IF EXISTS `#__ezpz`;

--
-- Table structure for table `#__ezpz`
--

CREATE TABLE `#__ezpz` (
  `config_key_md5` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `config_key` varchar(255) NOT NULL,
  `config_value` longtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `config_type` varchar(64) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `#__ezpz`
--
ALTER TABLE `i1w4v_ezpz`
  ADD PRIMARY KEY (`config_key_md5`),
  ADD KEY `config_key` (`config_key`),
  ADD KEY `config_type` (`config_type`);
COMMIT;