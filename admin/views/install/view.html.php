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
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Session\Session;

/**
 * Install View
 *
 * @since  0.0.1
 */
class EzpzViewInstall extends HtmlView
{
    private $formData = [];
    private $values = [];

    /**
     * @param null $tpl
     *
     * @throws Exception
     */
    public function display($tpl = null)
    {
        $app = Factory::getApplication();
        $input = $app->input;

        if ($input->getMethod() === 'POST') {

            if (!Session::checkToken()) {
                die('Invalid request');
            }

            $hasError = false;
            $dbo = Factory::getDbo();

            foreach (Constants::API_CONFIG_KEYS as $key) {
                if ($input->get($key)) {
                    $this->formData[$key] = $input->getString($key);
                    $this->values[] = '('.
                        $dbo->quote(md5($key)).','.
                        $dbo->quote($key).','.
                        $dbo->quote($this->formData[$key]).
                        ')';
                }
                else {
                    $hasError = true;
                    $app->enqueueMessage(
                        sprintf(Text::_('COM_EZPZ_REQUIRED_BUT_MISSING'), Text::_('COM_EZPZ_'.strtoupper($key))),
                        'error'
                    );
                }
            }

            if (!$hasError) {
                $this->install();
            }
        }

        parent::display($tpl);
    }

    protected function getFormData(string $key, $default) {
        return isset($this->formData[$key]) ? $this->formData[$key] : $default;
    }

    private function install(): void {

        $app = Factory::getApplication();
        $response = Client::install(Client::DEFAULT_ACCESS_TOKEN_KEY, $this->formData);

        if (!empty($response)) {
            if (isset($response['code']) && (int)$response['code'] !== 200) {
                if ($response['message']==='ITEM_ALREADY_EXISTS') {
                    $app->enqueueMessage(Text::_('COM_EZPZ_INSTALL_ERROR_ALREADY_EXISTS'), 'error');
                }
                else {
                    $app->enqueueMessage($response['message'], 'error');
                }
            }
            else {
                $sql = 'INSERT INTO '.Constants::DB_TB_EZPZ.'(config_key_md5,config_key,config_value)'.
                    ' VALUES'.implode(',', $this->values);
                Factory::getDbo()->setQuery($sql)->execute();
                $app->redirect('/administrator/index.php?option=com_ezpz');
            }
        }
        else {
            $app->enqueueMessage(Text::_('COM_EZPZ_INSTALL_ERROR_FAILED_TO_INSTALL'));
        }
    }
}