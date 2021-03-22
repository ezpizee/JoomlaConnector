<?php

defined('_JEXEC') or die;

use Joomla\CMS\Component\Router\RouterInterface;
use Joomla\CMS\Language\Multilanguage;

class EzpzRouter implements RouterInterface
{
    public function build(&$query)
    {
        $segments = array();

        if (!Multilanguage::isEnabled() || !isset($query['view']))
        {
            return $segments;
        }

        //$lang = JFactory::getLanguage()->getTag();
        //$app  = JFactory::getApplication();

        // get the menu item that this call to build() relates to
        if (!isset($query['Itemid']))
        {
            return $segments;
        }

        //$sitemenu = $app->getMenu();
        //if ($sitemenu instanceof AbstractMenu) {$thisMenuitem = $sitemenu->getItem($query['Itemid']);}

        if ($query['view'] == "ezpz" && isset($query['catid']) && isset($query['id']))
        {
            // set this part of the url to be of the form /subcat1/subcat2/.../ezpz
            $pathSegments = $this->getCategorySegments($query['catid']);
            if ($pathSegments)
            {
                $segments = $pathSegments;
            }

            $segments[] = $query['id'];

            unset($query['id']);
            unset($query['catid']);
        }

        unset($query['view']);
        return $segments;
    }

    /*
     * This function take a category id and finds the path from that category to the root of the category tree
     * The path returned from getPath() is an associative array of key = category id, value = id:alias
     * If no valid category is found from the passed-in category id then null is returned.
     */

    private function getCategorySegments($catid)
    {
        return isset($catid) ? '' : null;
    }

    public function parse(&$segments)
    {
        $vars = array();
        return $vars;
    }

    public function preprocess($query)
    {
        return $query;
    }
}