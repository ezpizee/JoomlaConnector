<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_gx2cms
 *
 * @copyright   Copyright (C) 2018 - 2021 WEBCONSOL Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

if (!defined('EZPIZEE_DS')) {
    define('EZPIZEE_DS', DIRECTORY_SEPARATOR);
    define('EZPIZEE_COMP_ROOT', __DIR__);
}

if (!file_exists(JPATH_LIBRARIES.EZPIZEE_DS.'ezpzlib'.EZPIZEE_DS.'autoload.php')) {
    include JPATH_COMPONENT_ADMINISTRATOR.EZPIZEE_DS.'asset'.EZPIZEE_DS.'html'.EZPIZEE_DS.'install-instructions.php';
}
else {
    include_once JPATH_LIBRARIES.EZPIZEE_DS.'ezpzlib'.EZPIZEE_DS.'autoload.php';
    \Ezpizee\ContextProcessor\CustomLoader::appendPackage([
        'EzpizeeJoomla' => JPATH_COMPONENT_ADMINISTRATOR.EZPIZEE_DS.'lib'.EZPIZEE_DS.'src'
    ], true);

    // Get an instance of the controller prefixed by GX2CMS
    $controller = JControllerLegacy::getInstance('Ezpz');

    // Perform the Request task
    $input = JFactory::getApplication()->input;
    $controller->execute($input->getCmd('task'));

    // Redirect if set by the controller
    $controller->redirect();
}