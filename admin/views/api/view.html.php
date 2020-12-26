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
use Ezpizee\MicroservicesClient\Config;
use Ezpizee\MicroservicesClient\Response;
use Ezpizee\Utils\ListModel;
use Ezpizee\Utils\StringUtil;
use Joomla\CMS\Factory;
use Joomla\CMS\Version;
use Unirest\Request\Body;

/**
 * Api View
 *
 * @since  0.0.1
 */
class EzpzViewApi extends JViewLegacy
{
    protected $portalData = '';
    /**
     * @var ListModel
     */
    private $ezpzConfig;
    /**
     * @var Client
     */
    private $client;
    private $request;
    private $method;
    private $contentType;
    private $body;
    private $uri;

    /**
     * @param null $tpl
     *
     * @throws Exception
     */
    public function display($tpl = null)
    {
        $this->ezpzConfig = new ListModel(EzpzAdminHelper::loadConfigData());

        if (!empty($this->ezpzConfig)) {
            $app = Factory::getApplication();
            $this->ezpzConfig->set('endpoint', $app->input->getString('endpoint'));
            $this->prepare();
            $this->portalData = json_encode($this->restApiClient());
            // Display the template
            parent::display($tpl);
        }
        else {
            Factory::getApplication()->redirect('/administrator/index.php?option=com_ezpz&view=install');
        }
    }

    private function prepare(): void {
        if ($this->ezpzConfig->get(Client::KEY_CLIENT_ID) &&
            $this->ezpzConfig->get(Client::KEY_CLIENT_SECRET) &&
            $this->ezpzConfig->get(Client::KEY_APP_NAME) &&
            $this->ezpzConfig->get(Client::KEY_ENV)) {
            $env = $this->ezpzConfig->get(Client::KEY_ENV);
            $microserviceConfig = new Config([
                Client::KEY_CLIENT_ID => $this->ezpzConfig->get(Client::KEY_CLIENT_ID),
                Client::KEY_CLIENT_SECRET => $this->ezpzConfig->get(Client::KEY_CLIENT_SECRET),
                Client::KEY_TOKEN_URI => Client::getTokenUri(),
                Client::KEY_APP_NAME => $this->ezpzConfig->get(Client::KEY_APP_NAME),
                Client::KEY_ENV => $env,
                Client::KEY_ACCESS_TOKEN => Client::DEFAULT_ACCESS_TOKEN_KEY
            ]);
            $this->request = Factory::getApplication()->input;
            $this->client = new Client(Client::apiSchema($env), Client::apiHost($env), $microserviceConfig);
            $this->client->setPlatform('joomla');
            $this->client->setPlatformVersion(Version::MAJOR_VERSION.'.'.Version::MINOR_VERSION.'.'.Version::PATCH_VERSION);
            $this->addHeaderRequest('user_id', Factory::getUser()->id);
            if ($env === 'local') {
                $this->client->verifyPeer(false);
            }
            $this->uri = $this->request->getString('endpoint');
            if ($this->uri && $this->uri[0] !== '/') {
                $this->uri = str_replace('//', '/', '/'.$this->uri);
            }
            $this->method = $this->request->getMethod();
            $this->contentType = $this->request->server->get('Content-Type');
            $this->body = json_decode(!empty($this->request->serialize())?$this->request->serialize():'[]', true);
            if (empty($this->body))  {
                $this->body = [];
            }
        }
    }

    public function restApiClient(): Response
    {
        if (!empty($this->uri)) {
            if (StringUtil::startsWith($this->uri, "/api/v1/joomla/")) {
                return $this->requestToCMS();
            }
            return $this->requestToMicroServices();
        }
        else {
            return new Response(
                ['status'=>'error','code'=>500,'message'=>'Missing Ezpizee endpoint']
            );
        }
    }

    private function requestToCMS(): Response
    {
        //$drupalApi = new EzpizeeAPIClientDrupalApiController($this->client);
        //$res = $drupalApi->load($this->method, $this->uri);
        return new Response(['code'=>200, 'message'=>'TODO: Context Processing', 'data'=>[]]);
    }

    private function requestToMicroServices(): Response
    {
        if ($this->method === 'GET') {
            return $this->client->get($this->uri);
        }
        else if ($this->method === 'POST') {
            if (isset($this->contentType) && $this->contentType === 'application/json') {
                return $this->client->post($this->uri, $this->body);
            }
            else if (isset($this->contentType) && strpos($this->contentType, 'multipart/form-data;') !== false) {
                if ($this->hasFileUploaded()) {
                    return $this->submitFormDataWithFile();
                }
                else {
                    return $this->submitFormData();
                }
            }
            else {
                return new Response(['code'=>422,'message'=>'Unprocessable data']);
            }
        }
        else if ($this->method === 'PUT') {
            if (isset($this->contentType) && $this->contentType === 'application/json') {
                return $this->client->put($this->uri, $this->body);
            }
            else {
                return new Response(['code'=>422,'message'=>'Unprocessable data']);
            }
        }
        else if ($this->method === 'DELETE') {
            return $this->client->delete($this->uri, $this->body);
        }
        else if ($this->method === 'PATCH') {
            return $this->client->patch($this->uri, $this->body);
        }
        else {
            return new Response(['code'=>422,'message'=>'Unprocessable data']);
        }
    }

    private function submitFormDataWithFile(): Response
    {
        $fileUploaded = $this->uploadFile();
        $this->body[$fileUploaded['fileFieldName']] = Body::file($fileUploaded['filename'], $fileUploaded['mimetype'], $fileUploaded['postname']);
        return $this->client->postFormData($this->uri, $this->body);
    }

    private function submitFormData(): Response
    {
        return $this->client->postFormData($this->uri, $this->body);
    }

    private function hasFileUploaded(): bool
    {
        return isset($_FILES) && !empty($_FILES);
    }

    private function addHeaderRequest(string $key, string $value): void
    {
        $this->client->addHeader($key, $value);
    }

    private function uploadFile(): array
    {
        // TODO
        $files = $this->request->files;
        $keys = array_keys($files);
        $fileFieldName = $keys[0];
        if (isset($_FILES) && !empty($_FILES) && !isset($_FILES[$fileFieldName])) {
            throw new RuntimeException('File name not found', 400);
        }
        if (isset($_FILES) && !empty($_FILES) && !isset($_FILES[$fileFieldName]) && $_FILES[$fileFieldName]['error'] > 0) {
            throw new RuntimeException('File could not be processed', 400);
        }
        return [
            'fileFieldName' => $fileFieldName,
            'filename' => $_FILES[$fileFieldName]['tmp_name'],
            'mimetype' => $_FILES[$fileFieldName]['type'],
            'postname' => $_FILES[$fileFieldName]['name']
        ];
    }
}