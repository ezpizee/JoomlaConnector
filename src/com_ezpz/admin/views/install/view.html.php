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
use Ezpizee\Utils\Logger;
use Ezpizee\Utils\ResponseCodes;
use EzpzJoomla\EzpizeeSanitizer;
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
                    EzpizeeSanitizer::sanitize($this->formData[$key], true);
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
        else {
            $this->loadData();
        }

        parent::display($tpl);
    }

    protected function getFormData(string $key, $default=null) {
        return isset($this->formData[$key]) ? $this->formData[$key] : $default;
    }

    private function install(): void {

        try {
            $app = Factory::getApplication();
            $tokenHandler = 'Ezpizee\\SupportedCMS\\Joomla\\TokenHandler';
            $response = Client::install(
                Client::DEFAULT_ACCESS_TOKEN_KEY, $this->formData, $tokenHandler);

            if (!empty($response)) {
                if (isset($response['code']) && (int)$response['code'] !== 200) {
                    if ($response['message']==='ITEM_ALREADY_EXISTS') {
                        $app->enqueueMessage(Text::_('COM_EZPZ_INSTALL_ERROR_ALREADY_EXISTS'), 'error');
                        $this->saveConfigData();
                    }
                    else {
                        $app->enqueueMessage($response['message'], 'error');
                    }
                }
                else {
                    $this->saveConfigData();
                    $app->redirect('/administrator/index.php?option=com_ezpz&view=ezpz');
                }
            }
            else {
                $app->enqueueMessage(Text::_('COM_EZPZ_INSTALL_ERROR_FAILED_TO_INSTALL'));
            }
        }
        catch (Exception $e) {
            Logger::error($e->getMessage());
            throw new RuntimeException($e->getMessage(), ResponseCodes::CODE_ERROR_INTERNAL_SERVER);
        }
    }

    private function saveConfigData() {
        $dbo = Factory::getDbo();
        foreach (Constants::API_CONFIG_KEYS as $key) {
            $cond = 'config_key_md5='.$dbo->quote(md5($key));
            $sql = 'SELECT config_key'.' FROM '.Constants::DB_TB_EZPZ.' WHERE '.$cond;
            $row = $dbo->setQuery($sql)->loadAssoc();
            if (empty($row)) {
                $sql = 'INSERT'.' INTO '.Constants::DB_TB_EZPZ.'(config_key_md5,config_key,config_value)
                VALUES('.$dbo->quote(md5($key)).','.$dbo->quote($key).','.$dbo->quote($this->formData[$key]).')';
            }
            else {
                $sql = 'UPDATE '.Constants::DB_TB_EZPZ.'
                SET config_value='.$dbo->quote($this->formData[$key]).'
                WHERE '.$cond;
            }
            $dbo->setQuery($sql)->execute();
        }
    }

    private function loadData(): void {
        $keys = [
            md5(Client::KEY_CLIENT_ID),
            md5(Client::KEY_CLIENT_SECRET),
            md5(Client::KEY_APP_NAME),
            md5(Constants::KEY_SCHEMA),
            md5(Client::KEY_ENV)
        ];
        $sql = 'SELECT *'.' FROM '.Constants::DB_TB_EZPZ.
            ' WHERE config_key_md5 IN("'.implode('","', $keys).'")';
        $rows = Factory::getDbo()->setQuery($sql)->loadAssocList();
        if (!empty($rows)) {
            foreach ($rows as $row) {
                $this->formData[$row['config_key']] = $row['config_value'];
            }
        }
    }
}