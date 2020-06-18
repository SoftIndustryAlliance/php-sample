<?php

namespace Admin\Service\OAuth;

class UserInfoListener {
    public static function userCallback($token, $rawUserInfo, $service)
    {
        if (!is_array($rawUserInfo)) {
            return;
        }

        $serviceName = strtolower($token->getService());
        $userInfo = array();
        $fieldMap = array(
            'id' => array('id'),
            'name' => array('name', 'username', 'screen_name', 'display_name', 'login'),
            'email' => array('email')
        );

        foreach ($fieldMap as $key => $fields) {
            $userInfo[$key] = null;
            foreach ($fields as $field) {
                if (is_callable($field)) {
                    $userInfo[$key] = $field($userInfo['id'], $serviceName);
                    break;
                }
                if (isset($rawUserInfo[$field])) {
                    $userInfo[$key] = $rawUserInfo[$field];
                    break;
                }
            }
        }

        switch ($serviceName) {
            case 'microsoft':
                $userInfo['email'] = $rawUserInfo['emails']['account'];
                break;
        }

        $token->setUser($userInfo['name']);
        $token->setEmail($userInfo['email']);
        $token->setUid($userInfo['id']);
    }
}