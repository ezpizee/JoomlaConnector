<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_ezpz
 *
 * @copyright   Copyright (C) 2020 - 2021 Ezpizee Co., Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

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
use Joomla\CMS\Access\Exception\NotAllowed as AccessExceptionNotAllowed;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;

if (!Factory::getUser()->authorise('core.manage', 'com_ezpz')) {
    throw new AccessExceptionNotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
}

include_once __DIR__.EZPIZEE_DS.'helpers'.EZPIZEE_DS.'Constants.php';
include_once __DIR__.EZPIZEE_DS.'helpers'.EZPIZEE_DS.'EzpzAdminHelper.php';

CustomLoader::appendPackage([
    'EzpzJoomla' => __DIR__.EZPIZEE_DS.'lib'.EZPIZEE_DS.'src'
], true);

// Set some global property
$document = Factory::getDocument();
$document->addStyleSheet(EzpzAdminHelper::assetRoot().'/css/style.css');

// Access check: is this user allowed to access the backend of this component?
if (!Factory::getUser()->authorise('core.manage', 'com_ezpz'))
{
    throw new Exception(Text::_('JERROR_ALERTNOAUTHOR'));
}

// Get an instance of the controller prefixed by Ezpz
$controller = BaseController::getInstance('Ezpz');

// Perform the Request task
$controller->execute(Factory::getApplication()->input->get('task'));;

// Redirect if set by the controller
$controller->redirect();