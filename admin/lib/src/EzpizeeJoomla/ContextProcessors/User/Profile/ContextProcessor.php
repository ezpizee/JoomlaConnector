<?php

namespace EzpizeeJoomla\ContextProcessors\User\Profile;

use EzpizeeJoomla\ContextProcessors\BaseContextProcessor;
use Joomla\CMS\Factory;

class ContextProcessor extends BaseContextProcessor
{
    protected function requiredAccessToken(): bool {return false;}

    protected function allowedMethods(): array {return ['GET'];}

    protected function validRequiredParams(): bool {return true;}

    public function processContext(): void {
        $this->setContextData($this->getUserInfoById(Factory::getUser()->id));
    }

    public function getUserInfoById(int $id) {
        $user = json_decode(json_encode(Factory::getUser($id)), true);
        if (!empty($this->microserviceClient) && !empty($user)) {
            $res = $this->microserviceClient->get('/api/user/me');
            $data = $res->get('data', []);
            return [
                "uid" => $user['id'],
                "displayName" => $user['name'],
                "profile" => [
                    'profile_picture' => '',
                    'first_name' => isset($user['field_first_name'])?$user['field_first_name']:'',
                    'last_name' => isset($user['field_last_name'])?$user['field_last_name']:'',
                    'email' => $user['email'],
                    'phone' => isset($user['field_phone'])?$user['field_phone']:''
                ],
                "role" => isset($user['groups']) ? $user['groups'] : [],
                "partnerInfo" => isset($data['partnerInfo']) ? $data['partnerInfo'] : []
            ];
        }
        else if (!empty($user)) {
            return [
                "uid" => $user['id'],
                "displayName" => $user['name'],
                "profile" => [
                    'profile_picture' => '',
                    'first_name' => isset($user['field_first_name'])?$user['field_first_name']:'',
                    'last_name' => isset($user['field_last_name'])?$user['field_last_name']:'',
                    'email' => $user['email'],
                    'phone' => isset($user['field_phone'])?$user['field_phone']:''
                ],
                "role" => isset($user['groups']) ? $user['groups'] : [],
                "partnerInfo" => []
            ];
        }
        return [];
    }
}