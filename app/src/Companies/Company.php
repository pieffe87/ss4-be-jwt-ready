<?php

namespace {

  use SilverStripe\ORM\DataObject;
  use SilverStripe\Forms\FieldList;
  use SilverStripe\Forms\TextField;
  use SilverStripe\Security\RandomGenerator;

  class Company extends DataObject
  {
    private static $db = [
      "Name" => "Varchar(255)",
      "LinkToken" => "Varchar(255)"
    ];

    private static $has_one = [];

    private static $has_many = [
      'Memberships' => CompanyMembership::class
    ];

    public function getCMSFields()
    {
      $fields = FieldList::create(
        new TextField("Name", "Nome dell'azienda")
      );
      $this->extend('updateCMSFields', $fields);
      return $fields;
    }

    public function generateLinkId()
    {
      if (strlen($this->LinkToken) === 0) {
        $generator = new RandomGenerator();
        $this->LinkToken = substr($generator->randomToken('sha1'), 0, 20);
      }
    }

    public function onBeforeWrite()
    {
      $this->generateLinkId();
      parent::onBeforeWrite();
    }

    private static $summary_fields = [
      'Name' => 'Nome',
      'LinkToken' => 'LinkToken'
    ];

  }
}
