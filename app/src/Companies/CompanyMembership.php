<?php

namespace {

  use SilverStripe\ORM\ArrayList;
  use SilverStripe\ORM\DataObject;
  use SilverStripe\Security\Group;
  use SilverStripe\Forms\DropdownField;
  use SilverStripe\Forms\FieldList;

  class CompanyMembership extends DataObject
  {
    private static $db = [];

    private static $has_one = [
      'Company' => Company::class,
      'CompanyMember' => CompanyMember::class,
      'Group' => Group::class
    ];

    private function getDecodedCompanies()
    {
      $list = Company::get();
      $items = ArrayList::create();
      foreach ($list as $c) {
        $items->push(['ID' => $c->ID, 'Title' => $c->Name]);
      }
      return $items;
    }

    private function getDecodedCompanyMembers()
    {
      $list = CompanyMember::get();
      $items = ArrayList::create();
      foreach ($list as $c) {
        $items->push(['ID' => $c->ID, 'Title' => $c->Email]);
      }
      return $items;
    }

    private function getDecodedGroups()
    {
      $list = Group::get();
      $items = ArrayList::create();
      foreach ($list as $c) {
        $items->push(['ID' => $c->ID, 'Title' => $c->Title]);
      }
      return $items;
    }

    public function getCMSFields()
    {
      $fields = FieldList::create(
        DropdownField::create(
          'CompanyID',
          'Company',
          $this->getDecodedCompanies()
        )->setEmptyString('Seleziona una company ...'),
        DropdownField::create(
          'CompanyMemberID',
          'CompanyMember',
          $this->getDecodedCompanyMembers()
        )->setEmptyString('Seleziona un company member ...'),
        DropdownField::create(
          'GroupID',
          'Gruppo',
          $this->getDecodedGroups()
        )->setEmptyString('Seleziona un company member ...')
      );
      $this->extend('updateCMSFields', $fields);
      return $fields;
    }

    public function getTitle()
    {
      $company = $this->Company();
      $member = $this->CompanyMember();
      if (isset($company->ID) && isset($member->ID)) {
        return $company->Name.' - '.$member->Email;
      }
    }

    private static $summary_fields = [
      'Company.Name' => 'Company',
      'CompanyMember.Email' => 'Company Member',
      'Group.Title' => 'Group'
    ];
  }
}
