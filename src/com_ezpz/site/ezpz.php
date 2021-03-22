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

if (!defined('EZPIZEE_COMP_ROOT')) {define('EZPIZEE_COMP_ROOT', __DIR__);}
if (!defined('EZPIZEE_DS')) {
    define('EZPIZEE_DS', DIRECTORY_SEPARATOR);
    $ezpzlibAutoLoader = JPATH_LIBRARIES.EZPIZEE_DS.'ezpzlib'.EZPIZEE_DS.'autoload.php';
    /**
     * check if ezpizee library is installed
     */
    if (!file_exists($ezpzlibAutoLoader)) {
        echo "<div style='background:#dfdfdf;margin:20% auto 0;padding:20px;width:500px;'>";
        echo __FILE__ . "<br/>";
        echo "Install ezstorefrontlib first. Download here: https://github.com/ezpizee/php-libs";
        echo "</div>";
        exit;
    }
    /**
     * include ezpizee library autoloader
     */
    include_once $ezpzlibAutoLoader;
}

use Ezpizee\ContextProcessor\CustomLoader;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory as JFactory;

CustomLoader::appendPackage([
    'EzpzJoomla' => JPATH_COMPONENT_ADMINISTRATOR.EZPIZEE_DS.'lib'.EZPIZEE_DS.'src'
], true);

// Get an instance of the controller prefixed by GX2CMS
$controller = BaseController::getInstance('Ezpz');
if ($controller instanceof BaseController) {
    // Perform the Request task
    $input = JFactory::getApplication()->input;
    $controller->execute($input->getCmd('task'));
    // Redirect if set by the controller
    $controller->redirect();
}