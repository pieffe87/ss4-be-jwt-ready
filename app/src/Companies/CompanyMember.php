<?php

namespace {

  use SilverStripe\Security\Member;

  class CompanyMember extends Member
  {
    private static $db = [];

    private static $has_one = [];

    private static $has_many = [
      'Memberships' => CompanyMembership::class
    ];

    public function getCMSFields()
    {
      $fields = parent::getCMSFields();
      $fields->removeByName("Locale");
      $fields->removeByName("FailedLoginCount");
      $fields->removeByName("DirectGroups");
      $fields->removeByName("Permissions");
      return $fields;
    }
  }
}
