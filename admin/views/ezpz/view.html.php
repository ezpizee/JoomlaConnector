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

use Ezpizee\ConnectorUtils\Client;
use Joomla\CMS\Factory;

/**
 * Ezpz View
 *
 * @since  0.0.1
 */
class EzpzViewEzpz extends JViewLegacy
{
    protected $ezpzConfig;
    protected $portalContent = '';
    protected $mode = 'install';

    /**
     * @param null $tpl
     *
     * @throws Exception
     */
    public function display($tpl = null)
    {
        $this->ezpzConfig = EzpzAdminHelper::loadConfigData();

        if (!empty($this->ezpzConfig)) {
            $this->mode = 'admin';
            $env = $this->ezpzConfig['env'];
            $this->portalContent = Client::getContentAsString(Client::cdnEndpointPfx($env).Client::adminUri('joomla'));
            // Display the template
            parent::display($tpl);
        }
        else {
            Factory::getApplication()->redirect('/administrator/index.php?option=com_ezpz&view=install');
        }
    }
}