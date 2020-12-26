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

$patterns = ["\n", "\r", "\t", "\s+"];
$replaces = ["", "", "", " "];
if (!empty($this->ezpzConfig)) {
    foreach ($this->ezpzConfig as $key=>$val) {
        $patterns[] = '{'.$key.'}';
        $replaces[] = $val;
    }
}
if ($this->mode === 'admin') {
    $patterns[] = '{loginPageRedirectUrl}';
    $replaces[] = '/administrator';
}
$dir = EzpzAdminHelper::assetFileRoot().DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR;
$override = str_replace($patterns, $replaces, file_get_contents($dir . 'ezpz_'.$this->mode.'_override.js'));
echo str_replace('<' . 'head>', '<' . 'head' . '><' . 'script>' . $override . '</' . 'script>', $this->portalContent);

exit(0);