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
  
    static function site_menu($menu, $theme, $item_css_selector) {
        if ($theme->page_subtype != "login") {
            $menu->append(Menu::factory("link")
                    ->id("home")
                    ->label(t("Home"))
                    ->url(item::root()->url()));


      $item = $theme->item();

      if (!empty($item)) {
        $can_edit = $item && access::can("edit", $item);
        $can_add = $item && access::can("add", $item);

        if ($can_add) {
          $menu->append($add_menu = Menu::factory("submenu")
                        ->id("add_menu")
                        ->label(t("Add")));
          $is_album_writable =
            is_writable($item->is_album() ? $item->file_path() : $item->parent()->file_path());
          if ($is_album_writable) {
            $add_menu->append(Menu::factory("dialog")
                              ->id("add_photos_item")
                              ->label(t("Add photos"))
                              ->url(url::site("uploader/index/$item->id")));
            if ($item->is_album()) {
              $add_menu->append(Menu::factory("dialog")
                                ->id("add_album_item")
                                ->label(t("Add an album"))
                                ->url(url::site("form/add/albums/$item->id?type=album")));
            }
          } else {
            message::warning(t("The album '%album_name' is not writable.",
                               array("album_name" => $item->title)));
          }
        }

        switch ($item->type) {
        case "album":
          $option_text = t("Album options");
          $edit_text = t("Edit album");
          $delete_text = t("Delete album");
          $css_class = "normal";
          break;
        case "movie":
          $option_text = t("Movie options");
          $edit_text = t("Edit movie");
          $delete_text = t("Delete movie");
          $css_class = "normal";
          break;
        default:
          $option_text = t("Photo options");
          $edit_text = t("Edit photo");
          $delete_text = t("Delete photo");
          $css_class = "context";
        }

        $menu->append($options_menu = Menu::factory("submenu")
                      ->id("options_menu")
                      ->css_class($css_class)
                      ->label($option_text));
        if ($item && ($can_edit || $can_add)) {
          if ($can_edit) {
            $options_menu->append(Menu::factory("dialog")
                                  ->id("edit_item")
                                  ->label($edit_text)
                                  ->url(url::site("form/edit/{$item->type}s/$item->id?from_id={$item->id}")));
          }

          if ($item->is_album()) {
            if ($can_edit) {
              $options_menu->append(Menu::factory("dialog")
                                    ->id("edit_permissions")
                                    ->label(t("Edit permissions"))
                                    ->url(url::site("permissions/browse/$item->id")));
            }
          }
        }

        $csrf = access::csrf_token();
        $page_type = $theme->page_type();
        if ($can_edit && $item->is_photo() && graphics::can("rotate")) {
          $options_menu
            ->append(
              Menu::factory("ajax_link")
              ->id("rotate_ccw")
              ->label(t("Rotate 90° counter clockwise"))
              ->css_class("ui-icon-rotate-ccw")
              ->ajax_handler("function(data) { " .
                             "\$.gallery_replace_image(data, \$('$item_css_selector')) }")
              ->url(url::site("quick/rotate/$item->id/ccw?csrf=$csrf&amp;from_id={$item->id}&amp;page_type=$page_type")))
            ->append(
              Menu::factory("ajax_link")
              ->id("rotate_cw")
              ->label(t("Rotate 90° clockwise"))
              ->css_class("ui-icon-rotate-cw")
              ->ajax_handler("function(data) { " .
                             "\$.gallery_replace_image(data, \$('$item_css_selector')) }")
              ->url(url::site("quick/rotate/$item->id/cw?csrf=$csrf&amp;from_id={$item->id}&amp;page_type=$page_type")));
        }

        if ($item->id != item::root()->id) {
          $parent = $item->parent();
          if (access::can("edit", $parent)) {
            // We can't make this item the highlight if it's an album with no album cover, or if it's
            // already the album cover.
            if (($item->type == "album" && empty($item->album_cover_item_id)) ||
                ($item->type == "album" && $parent->album_cover_item_id == $item->album_cover_item_id) ||
                $parent->album_cover_item_id == $item->id) {
              $disabledState = " ui-state-disabled";
            } else {
              $disabledState = " ";
            }

            if ($item->parent()->id != 1) {
              $options_menu
                ->append(
                  Menu::factory("ajax_link")
                  ->id("make_album_cover")
                  ->label(t("Choose as the album cover"))
                  ->css_class("ui-icon-star")
                  ->ajax_handler("function(data) { window.location.reload() }")
                  ->url(url::site("quick/make_album_cover/$item->id?csrf=$csrf")));
            }
            $options_menu
              ->append(
                Menu::factory("dialog")
                ->id("delete")
                ->label($delete_text)
                ->css_class("ui-icon-trash")
                ->css_class("g-quick-delete")
                ->url(url::site("quick/form_delete/$item->id?csrf=$csrf&amp;from_id={$item->id}&amp;page_type=$page_type")));
          }
        }
      }

      if (identity::active_user()->admin) {
        $menu->append($admin_menu = Menu::factory("submenu")
                ->id("admin_menu")
                ->label(t("Admin")));
        module::event("admin_menu", $admin_menu, $theme);

        $settings_menu = $admin_menu->get("settings_menu");
        uasort($settings_menu->elements, array("Menu", "title_comparator"));
      }
    }
  }      
}