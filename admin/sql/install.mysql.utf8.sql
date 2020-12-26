DROP TABLE IF EXISTS `#__ezpz`;

--
-- Table structure for table `#__ezpz`
--

CREATE TABLE `#__ezpz` (
  `config_key_md5` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `config_key` varchar(255) NOT NULL,
  `config_value` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `#__ezpz`
--
ALTER TABLE `#__ezpz`
  ADD PRIMARY KEY (`config_key_md5`),
  ADD KEY `config_key` (`config_key`);