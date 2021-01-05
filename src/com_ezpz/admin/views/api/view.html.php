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
use Ezpizee\Utils\Request;
use Ezpizee\Utils\ResponseCodes;
use Ezpizee\Utils\StringUtil;
use EzpizeeJoomla\ContextProcessors\User\Profile\ContextProcessor;
use Joomla\CMS\Factory;
use Joomla\CMS\Version;
use Joomla\CMS\MVC\View\HtmlView;
use Unirest\Request\Body;

/**
 * Api View
 *
 * @since  0.0.1
 */
class EzpzViewApi extends HtmlView
{
    /**
     * @var ListModel
     */
    private $ezpzConfig;
    /**
     * @var Client
     */
    private $client;
    /**
     * @var Request
     */
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
            $response = $this->restApiClient();
            header('Content-Type: application/json');
            http_response_code($response->getCode());
            die($response);
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
            $this->request = new Request();
            if ($env === 'local') {
                Client::setIgnorePeerValidation(true);
            }
            $tokenHandler = 'EzpizeeJoomla\TokenHandler';
            $this->client = new Client(Client::apiSchema($env), Client::apiHost($env), $microserviceConfig, $tokenHandler);
            $this->client->setPlatform('joomla');
            $this->client->setPlatformVersion(Version::MAJOR_VERSION.'.'.Version::MINOR_VERSION.'.'.Version::PATCH_VERSION);
            $this->addHeaderRequest('user_id', Factory::getUser()->id);
            $this->uri = $this->request->getRequestParam('endpoint');
            if ($this->uri && $this->uri[0] !== '/') {
                $this->uri = str_replace('//', '/', '/'.$this->uri);
            }
            $this->method = $this->request->method();
            $this->contentType = $this->request->contentType();
            $this->body = !empty($this->request->getRequestParamsAsArray()) ? $this->request->getRequestParamsAsArray() : [];
        }
    }

    protected function restApiClient(): Response
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
        $api = new \EzpizeeJoomla\ApiClient($this->client);
        return new Response($api->load($this->uri));
    }

    private function requestToMicroServices(): Response
    {
        $response = new Response([
            'status'=>'ERROR',
            'code'=>ResponseCodes::CODE_METHOD_NOT_ALLOWED,
            'data'=>null,
            'message'=>ResponseCodes::MESSAGE_ERROR_INVALID_METHOD
        ]);
        if ($this->method === 'GET') {
            $response = $this->client->get($this->uri);
            $res = json_decode($response, true);
            if (isset($res['data']) && isset($res['data']['created_by'])) {
                $userProfileCP = new ContextProcessor();
                $userProfileCP->setMicroServiceClient($this->client);
                $userProfileCP->setRequest($this->request);
                $userInfo = $userProfileCP->getUserInfoById((int)$res['data']['created_by']);
                $res['data']['created_by'] = $userInfo;
                $res['data']['modified_by'] = $userInfo;
            }
        }
        else if ($this->method === 'POST') {
            if (isset($this->contentType) && $this->contentType === 'application/json') {
                $response = $this->client->post($this->uri, $this->body);
            }
            else if (isset($this->contentType) && strpos($this->contentType, 'multipart/form-data;') !== false) {
                if ($this->hasFileUploaded()) {
                    $response = $this->submitFormDataWithFile();
                }
                else {
                    $response = $this->submitFormData();
                }
            }
            else {
                $response->setCode(ResponseCodes::CODE_ERROR_INVALID_FIELD);
                $response->setMessage('INVALID_CONTENT_TYPE');
            }
        }
        else if ($this->method === 'PUT') {
            if (isset($this->contentType) && $this->contentType === 'application/json') {
                $response = $this->client->put($this->uri, $this->body);
            }
            else {
                $response->setCode(ResponseCodes::CODE_ERROR_INVALID_FIELD);
                $response->setMessage('INVALID_CONTENT_TYPE');
            }
        }
        else if ($this->method === 'DELETE') {
            $response = $this->client->delete($this->uri, $this->body);
        }
        else if ($this->method === 'PATCH') {
            $response = $this->client->patch($this->uri, $this->body);
        }
        return $response;
    }

    private function submitFormDataWithFile(): Response
    {
        $fileUploaded = $this->uploadFile();
        $this->body[$fileUploaded['fileFieldName']] = Body::file($fileUploaded['filename'], $fileUploaded['mimetype'], $fileUploaded['postname']);
        $response = $this->client->postFormData($this->uri, $this->body);
        return $response;
    }

    private function submitFormData(): Response
    {
        $response = $this->client->postFormData($this->uri, $this->body);
        return $response;
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
        $files = $this->request->getFiles();
        if (empty($files)) {
            throw new RuntimeException('Invalid file', ResponseCodes::CODE_ERROR_INVALID_FIELD);
        }
        $keys = array_keys($files);
        $fileFieldName = $keys[0];
        if (isset($_FILES) && !empty($_FILES) && !isset($_FILES[$fileFieldName])) {
            throw new RuntimeException('File name not found', ResponseCodes::CODE_ERROR_INVALID_FIELD);
        }
        if (isset($_FILES) && !empty($_FILES) && !isset($_FILES[$fileFieldName]) && $_FILES[$fileFieldName]['error'] > 0) {
            throw new RuntimeException('File could not be processed', ResponseCodes::CODE_ERROR_INVALID_FIELD);
        }
        return [
            'fileFieldName' => $fileFieldName,
            'filename' => $_FILES[$fileFieldName]['tmp_name'],
            'mimetype' => $_FILES[$fileFieldName]['type'],
            'postname' => $_FILES[$fileFieldName]['name']
        ];
    }
}