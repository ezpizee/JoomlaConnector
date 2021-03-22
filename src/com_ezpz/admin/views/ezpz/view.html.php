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
use Ezpizee\Utils\StringUtil;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView;

/**
 * Ezpz View
 *
 * @since  0.0.1
 */
class EzpzViewEzpz extends HtmlView
{
    private $ezpzConfig;
    private $portalContent = '';

    /**
     * @param null $tpl
     *
     * @throws Exception
     */
    public function display($tpl = null)
    {
        $this->ezpzConfig = EzpzAdminHelper::loadConfigData();

        if (!empty($this->ezpzConfig)) {
            $env = $this->ezpzConfig[Client::KEY_ENV];
            $url = $this->ezpzConfig[Constants::KEY_SCHEMA].Client::cdnHost($env).Client::adminUri('joomla');
            if (!StringUtil::isHttps($url)) {
                if ($_SERVER['HTTPS']) {
                    $url = str_replace('http://', 'https://', $url);
                }
                else if ($env === 'local') {
                    Client::setIgnorePeerValidation(true);
                }
            }
            $this->portalContent = Client::getContentAsString($url);
            $this->formatOutput();
            die($this->portalContent);
        }
        else {
            Factory::getApplication()->redirect('/administrator/index.php?option=com_ezpz&view=install');
        }
    }

    private function formatOutput(): void {
        $patterns = ["\n", "\r", "\t", "\s+", '{loginPageRedirectUrl}'];
        $replaces = ["", "", "", " ", '/administrator'];
        $dir = EzpzAdminHelper::assetFileRoot().DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR;
        $override = str_replace($patterns, $replaces, file_get_contents($dir . 'ezpz_admin_override.js'));
        $this->portalContent = str_replace('<' . 'head>', '<' . 'head' . '><' . 'script>' . $override . '</' . 'script>', $this->portalContent);
    }
}