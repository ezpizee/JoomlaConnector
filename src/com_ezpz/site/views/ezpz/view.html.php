<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_ezpz
 *
 * @copyright   2020 - 2021 Ezpizee Co., Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\View\HtmlView as JViewLegacy;

/**
 * HTML View class for the Ezpz Component
 *
 * @since  0.0.1
 */
class EzpzViewEzpz extends JViewLegacy
{
    /**
     * Display the Ezpizee view
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     */
    function display($tpl = null)
    {
        // Display the view
        parent::display($tpl);
    }
}