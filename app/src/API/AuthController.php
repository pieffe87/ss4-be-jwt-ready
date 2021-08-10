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
      $json = file_get_contents('php://input');
      $data = json_decode($json);
      if(isset($data->email) && $data->email != '' && isset($data->password) && $data->password != '') {
        $this->getRequest()->addHeader('PHP_AUTH_USER', $data->email);
        $this->getRequest()->addHeader('PHP_AUTH_PW', $data->password);

        try {
          $payload = JWTUtils::inst()->byBasicAuth($this->getRequest());

          if(is_array($payload) && array_key_exists('token', $payload)) {
            $data = [
              'code' => 'OK',
              'access_token' => $payload['token'],
              'token_type' => 'bearer',
              'data' => [
                'user' => [
                  'email' => $payload['member']['email'],
                  'firstname' => $payload['member']['firstName'],
                  'surname' => $payload['member']['surname']
                ]
              ],
              'meta' => [
                'message' => 'Accesso effettuato correttamente',
              ]
            ];
            return $this->jsonResponse($data, 200, $payload['token']);
          }
        }
        catch(JWTUtilsException $e) {
          return $this->jsonResponse($this->jsonStandardErrorData($e->getMessage()), 200);
        }
      }
      return $this->jsonResponse($this->jsonStandardErrorData('Dati mancanti'), 400);
    }

    public function refresh()
    {
      if ($this->getRequest()->getHeader('Authorization') !== null) {
        $header = $this->getRequest()->getHeader('Authorization');
        list($bearer, $firstToken) = explode(' ',$header);
        try {
          $renewedToken = JWTUtils::inst()->renew($firstToken);
        } catch(JWTUtilsException $e) {
          return $this->jsonResponse(['msg' => 'Unauthorized'], 401);
        }
        if($renewedToken != $firstToken) {
          return $this->jsonResponse($this->jsonStandardSuccessData('Renewed'), 200, $renewedToken);
        }
        return $this->jsonResponse($this->jsonStandardSuccessData('Nothing to do'), 200);
      }
      return $this->jsonResponse($this->jsonStandardErrorData('Dati mancanti'), 400);
    }

    public function user()
    {
      $member = $this->getCurrentMember();
      if ($member) {
        $data = [
          'code' => 'OK',
          'data' => [
            'user' => [
              'email' => $member->Email,
              'firstname' => $member->FirstName,
              'surname' => $member->Surname
            ]
          ]
        ];
        return $this->jsonResponse($data, 200);
      }

      return $this->jsonResponse($this->jsonStandardErrorData('Dati mancanti'), 400);
    }

  }

}
