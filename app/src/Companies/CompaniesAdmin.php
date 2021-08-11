<?php

namespace {

  use SilverStripe\Admin\ModelAdmin;

  class CompaniesAdmin extends ModelAdmin
  {

    private static $menu_icon_class = 'font-icon-home';

    private static $managed_models = [
      Company::class,
      CompanyMember::class,
      CompanyMembership::class
    ];

    private static $url_segment = 'companies';

    private static $menu_title = 'Companies';
  }

}
