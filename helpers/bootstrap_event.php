<?php

class bootstrap_event extends gallery_event_Core
{
  static function user_menu($menu, $theme) {
    if ($theme->page_subtype != "login") {
      $user = identity::active_user();
      if ($user->guest) {
        $menu->append(Menu::factory("dialog")
                      ->id("user_menu_login")
                      ->css_id("g-login-link")
                      ->css_class("btn")
                      ->url(url::site("login/ajax"))
                      ->label(t("Login")));
      } else {
        $csrf = access::csrf_token();
        $menu->append(Menu::factory("link")
                      ->id("user_menu_edit_profile")
                      ->css_id("g-user-profile-link")
                      ->view("login_current_user.html")
                      ->url(user_profile::url($user->id))
                      ->label($user->display_name()));

        if (Router::$controller == "admin") {
          $continue_url = url::abs_site("");
        } else if ($item = $theme->item()) {
          if (access::user_can(identity::guest(), "view", $theme->item)) {
            $continue_url = $item->abs_url();
          } else {
            $continue_url = item::root()->abs_url();
          }
        } else {
          $continue_url = url::abs_current();
        }

        $menu->append(Menu::factory("link")
                      ->id("user_menu_logout")
                      ->css_id("g-logout-link")
                      ->css_class("btn btn-danger")                      
                      ->url(url::site("logout?csrf=$csrf&amp;continue_url=" . urlencode($continue_url)))
                      ->label(t("Logout")));
      }
    }
  }    
}