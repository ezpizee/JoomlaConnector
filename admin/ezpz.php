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

include_once __DIR__.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';
include_once __DIR__.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'Constants.php';
include_once __DIR__.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'EzpzAdminHelper.php';

// Set some global property
$document = JFactory::getDocument();
$document->addStyleSheet(EzpzAdminHelper::assetRoot().'/css/style.css');

// Access check: is this user allowed to access the backend of this component?
if (!JFactory::getUser()->authorise('core.manage', 'com_ezpz'))
{
    throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
}

// Get an instance of the controller prefixed by Ezpz
$controller = JControllerLegacy::getInstance('Ezpz');

// Perform the Request task
$controller->execute(JFactory::getApplication()->input->get('task'));;

// Redirect if set by the controller
$controller->redirect();