<?php

namespace {

  use SilverStripe\CMS\Controllers\ContentController;
  use \Firebase\JWT\JWT as JWT;
  use Level51\JWTUtils\JWTUtils;
  use SilverStripe\Core\Config\Config;
  use SilverStripe\Security\Member;

  class RestController extends ContentController
  {
    private static $allowed_actions = [
    ];

    public function jsonStandardSuccessData($message)
    {
      return [
        'code' => 'OK',
        'meta' => [
          'message' => $message
        ]
      ];
    }

    public function jsonStandardErrorData($message)
    {
      return [
        'code' => 'KO',
        'meta' => [
          'message' => $message
        ]
      ];
    }

    protected function jsonResponse(array $msg, int $code = 200, $token = null)
    {
      $this->getResponse()->setBody(json_encode($msg));
      $this->getResponse()->addHeader("Content-type", "application/json");
      if($token)
          $this->getResponse()->addHeader('Authorization','Bearer '.$token);

      $this->getResponse()->addHeader('Access-Control-Allow-Origin', '*');
      $this->getResponse()->addHeader('Access-Control-Allow-Methods', 'PUT, GET, POST, DELETE, OPTIONS');
      $this->getResponse()->addHeader('Access-Control-Allow-Headers', '*');

      $this->getResponse()->setStatusCode($code);
      return $this->getResponse();
    }

    public function getCurrentMember() {
      if ($this->getRequest()->getHeader('Authorization') !== null) {
        $header = $this->getRequest()->getHeader('Authorization');
        list($bearer, $token) = explode(' ',$header);
        if(JWTUtils::inst()->check($token)) {
          $decoded = JWT::decode($token, Config::inst()->get(JWTUtils::class, 'secret'), ['HS256']);
          return Member::get()->byID($decoded->memberId);
        }
      }
      return false;
    }

  }

}
