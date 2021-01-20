<?php

namespace EzpizeeJoomla\Joomla;

use Exception;
use Ezpizee\Utils\Logger;
use GX2CMSJoomla\Exception\Error;
use Joomla\CMS\Factory;
use Joomla\CMS\Menu\AbstractMenu;
use Joomla\CMS\Menu\MenuItem;
use Joomla\Registry\Registry;

class Menu
{
    public static function getMenu($name = null, $options = array()): AbstractMenu
    {
        $menu = new AbstractMenu();
        try {
            $menu = Factory::getApplication()->getMenu($name, $options);
            if (!($menu instanceof AbstractMenu)) {
                new Error('Menu is not an instance of \Joomla\CMS\Menu\AbstractMenu', 500);
            }
        }
        catch (Exception $e) {
            Logger::error($e->getMessage());
            new Error($e->getMessage(), 500);
        }
        return $menu;
    }

    public static function getActiveMenu(): MenuItem
    {
        return self::getMenu()->getActive();
    }

    public static function getActiveMenuParams(): Registry
    {
        return self::getActiveMenu()->getParams();
    }

    public static function getMenuItemParams(int $id): Registry
    {
        return self::getMenu()->getItem($id)->getParams();
    }
}