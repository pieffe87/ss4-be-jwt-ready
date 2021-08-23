<?php

namespace {

  use \Firebase\JWT\JWT as JWT;
  use Level51\JWTUtils\JWTUtils;
  use Level51\JWTUtils\JWTUtilsException;

  class AuthController extends RestController
  {
    private static $allowed_actions = [
      'login',
      'refresh',
      'user'
    ];

    private static $url_handlers = [
      'login' => 'login',
      'refresh' => 'refresh',
      'user' => 'user'
    ];

    public function login()
    {
      $data = $this->getJsonData();
      if (!is_array($data)) return $data;

      if(!isset($data['email']) || $data['email'] == '') {
        $data = [
          'errors' => [
            [
              'field' => 'email',
              'msg' => 'Campo email non valido'
            ]
          ]
        ];
        return $this->jsonResponse($data, 422);
      }
      if(!isset($data['password']) || $data['password'] == '') {
        $data = [
          'errors' => [
            [
              'field' => 'password',
              'msg' => 'Campo password non valido'
            ]
          ]
        ];
        return $this->jsonResponse($data, 422);
      }
      if(isset($data['email']) && $data['email'] != '' && isset($data['password']) && $data['password'] != '') {
        $this->getRequest()->addHeader('PHP_AUTH_USER', $data['email']);
        $this->getRequest()->addHeader('PHP_AUTH_PW', $data['password']);

        try {
          $payload = JWTUtils::inst()->byBasicAuth($this->getRequest());

          if(is_array($payload) && array_key_exists('token', $payload)) {
            $data = [
              'status' => 'OK'
            ];
            return $this->jsonResponse($data, 200, $payload['token']);
          }
        }
        catch(JWTUtilsException $e) {
          return $this->jsonResponse($this->jsonStandardMessage('KO', $e->getMessage()), 200);
        }
      }
    }

    public function refresh()
    {
      $firstToken = $this->getCurrentToken();
      if (!is_string($firstToken)) return $firstToken;

      try {
        $renewedToken = JWTUtils::inst()->renew($firstToken);
      } catch(JWTUtilsException $e) {
        return $this->jsonResponse($this->jsonStandardMessage('KO', 'Unauthorized.'), 401);
      }
      if($renewedToken != $firstToken) {
        return $this->jsonResponse($this->jsonStandardMessage('OK', 'Auth token renewed.'), 200, $renewedToken);
      }
      return $this->jsonResponse($this->jsonStandardMessage('OK', 'Auth token still valid. Nothing to do.'), 200);
    }

    public function user()
    {
      $member = $this->getCurrentMember();
      if (!isset($member->ID)) return $member;

      if ($member) {
        $data = [
          'status' => 'OK',
          'data' => [
            'email' => $member->Email,
            'firstname' => $member->FirstName,
            'surname' => $member->Surname,
            'type' => 'admin'
          ]
        ];
        return $this->jsonResponse($data, 200);
      }
    }

  }

}
