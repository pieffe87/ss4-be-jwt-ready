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

    public function jsonStandardMessage($code, $message)
    {
      return [
        'code' => $code,
        'msg' => $message
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
      $this->getResponse()->addHeader('Access-Control-Expose-Headers', '*');

      $this->getResponse()->setStatusCode($code);
      return $this->getResponse();
    }

    public function getCurrentToken()
    {
      $header = '';
      if ($this->getRequest()->getHeader('Authorization') !== null)
      {
        $header = $this->getRequest()->getHeader('Authorization');
        if (strlen($header) != 0)
        {
          list($bearer, $token) = explode(' ', $header);
          return $token;
        }
      }
      return $this->jsonResponse($this->jsonStandardMessage('KO', 'No data sent'), 200);
    }

    public function getCurrentMember()
    {
      $token = $this->getCurrentToken();
      if (!is_string($token)) return $token;

      if (JWTUtils::inst()->check($token))
      {
        $decoded = JWT::decode($token, Config::inst()->get(JWTUtils::class, 'secret'), ['HS256']);
        return Member::get()->byID($decoded->memberId);
      }
      return false;
    }

    public function getJsonData()
    {
      $json = file_get_contents('php://input');
      if (strlen($json) == 0) {
        return $this->jsonResponse($this->jsonStandardMessage('KO', 'No data sent'), 200);
      }
      return json_decode($json, true);
    }

  }

}
