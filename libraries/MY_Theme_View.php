<?php
class Theme_View extends Theme_View_Core 
{
    public function user_menu() {
        $menu = Menu::factory("root")
            ->css_id("g-login-menu")
            ->css_class("g-inline ui-helper-clear-fix");
        module::event("user_menu", $menu, $this);
        return $menu->render();
    }

    public function site_menu($item_css_selector) {
        $menu = Menu::factory("root");            
        module::event("site_menu", $menu, $this, $item_css_selector);
        return $menu->render();
    }

    public function album_menu() {
        $menu = Menu::factory("root")
            ->css_id("g-album-menu");
        module::event("album_menu", $menu, $this);
        return $menu->render();
    }

    public function tag_menu() {
        $menu = Menu::factory("root")
            ->css_id("g-tag-menu");
        module::event("tag_menu", $menu, $this);
        return $menu->render();
    }

    public function photo_menu() {
        $menu = Menu::factory("root");
        if (access::can("view_full", $this->item())) {
            $menu->append(Menu::factory("link")
                    ->id("fullsize")
                    ->label(t("View full size"))
                    ->url($this->item()->file_url())
                    ->css_class("g-fullsize-link"));
        }

        module::event("photo_menu", $menu, $this);
        return $menu->render();
    }

    public function movie_menu() {
        $menu = Menu::factory("root");
        module::event("movie_menu", $menu, $this);
        return $menu->render();
    }

    public function context_menu($item, $thumbnail_css_selector) {        
        $menu = Menu::factory("root")                        
            ->label(t("Options"))
            ->css_class("g-context-menu");
        
        module::event("context_menu", $menu, $this, $item, $thumbnail_css_selector);        
        return $menu->render();
    }    
}