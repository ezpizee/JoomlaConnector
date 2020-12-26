<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_ezpz
 *
 * @copyright   Copyright (C) 2020 - 2021 Ezpizee Co., Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted Access');

use Ezpizee\ConnectorUtils\Client;

header(Client::HEADER_PARAM_CTYPE.': '.Client::HEADER_VALUE_JSON);
echo $this->portalData;
exit(0);