<?php

namespace {

  use \Firebase\JWT\JWT as JWT;
  use Level51\JWTUtils\JWTUtils;
  use Level51\JWTUtils\JWTUtilsException;
  use SilverStripe\Core\Environment;
  use SilverStripe\Security\Member;
  use SilverStripe\Security\Group;

  class AuthController extends RestController
  {
    private static $allowed_actions = [
      'login',
      'register',
      'refresh',
      'user'
    ];

    private static $url_handlers = [
      'login' => 'login',
      'register' => 'register',
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
              'code' => 'OK'
            ];
            return $this->jsonResponse($data, 200, $payload['token']);
          }
        }
        catch(JWTUtilsException $e) {
          return $this->jsonResponse($this->jsonStandardMessage('KO', $e->getMessage()), 200);
        }
      }
    }

    public function register()
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
      if (isset($data['password_confirm']) && $data['password_confirm'] != $data['password']) {
        $data = [
          'errors' => [
            [
              'field' => 'password_confirm',
              'msg' => 'Le password non coincidono'
            ]
          ]
        ];
        return $this->jsonResponse($data, 422);
      }
      if(!isset($data['privacy']) || $data['privacy'] == false) {
        $data = [
          'errors' => [
            [
              'field' => 'privacy',
              'msg' => 'L\'accettazione delle condizioni è obbligatoria'
            ]
          ]
        ];
        return $this->jsonResponse($data, 422);
      }
      try {
        $member = Member::get()
          ->filter('Email', $data['email'])
          ->first();

        if (isset($member->ID)) {
          $data = [
            'errors' => [
              [
                'field' => 'email',
                'msg' => 'Esiste già un utente con questo indirizzo email'
              ]
            ]
          ];
          return $this->jsonResponse($data, 422);
        }

        $member = Member::create();
        $member->FirstName = isset($data['first_name']) && strlen($data['first_name']) > 0 ? $data['first_name'] : '';
        $member->Surname = isset($data['surname']) && strlen($data['surname']) > 0 ? $data['surname'] : '';
        $member->Email = $data['email'];
        $member->write();
        $member->changePassword($data['password']);

        if (Environment::getEnv('MEMBER_REGISTER_DEFAULT_GROUP')) {
          $groupCode = Environment::getEnv('MEMBER_REGISTER_DEFAULT_GROUP');
          $group = Group::get()->filter('Code', $groupCode)->first();
          if (!$group) {
              $group = new Group();
              $group->Code = $groupCode;
              $group->Title = $groupCode;
              $group->write();
          }
          $member->Groups()->add($group);
        }

        if(isset($member) && isset($member->ID)) {
          // TODO: Mail di conferma registrazione

          $data = [
            'code' => 'OK'
          ];
          return $this->jsonResponse($data, 200);
        }
      }
      catch(JWTUtilsException $e) {
        return $this->jsonResponse($this->jsonStandardMessage('KO', $e->getMessage()), 200);
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
          'code' => 'OK',
          'data' => [
            'email' => $member->Email,
            'firstname' => $member->FirstName,
            'surname' => $member->Surname,
            'type' => $member->Groups()->Count() > 0 ? $member->Groups()->First()->Code : 'Managers'
          ]
        ];
        return $this->jsonResponse($data, 200);
      }
    }

  }

}
