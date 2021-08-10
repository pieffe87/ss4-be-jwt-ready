<?php

namespace {

  use \Firebase\JWT\JWT as JWT;
  use Level51\JWTUtils\JWTUtils;
  use Level51\JWTUtils\JWTUtilsException;
  use SilverStripe\Core\Config\Config;
  use SilverStripe\Security\Member;

  class ClosedController extends RestController
  {
    private static $allowed_actions = [
    ];

    public function init()
    {
      parent::init();
      if ($this->getRequest()->getHeader('Authorization') !== null) {
        $header = $this->getRequest()->getHeader('Authorization');
        $parts = explode(' ',$header);
        if(isset($parts[1])) {
          if( ! JWTUtils::inst()->check($parts[1])) {
            return $this->jsonResponse(['msg' => 'Unauthorized'], 401);
          }
        } else {
          return $this->jsonResponse(['msg' => 'Unauthorized'], 401);
        }
      }
      return $this->jsonResponse(['msg' => 'Token error'], 400);
    }

  }

}
