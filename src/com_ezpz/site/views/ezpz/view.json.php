<?php
/**
 * View file for responding to Ajax request for performing Search Here on the map
 *
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

class EzpzViewEzpz extends JViewLegacy
{
    /**
     * This display function returns in json format the Ezpz greetings
     *   found within the latitude and longitude boundaries of the map.
     * These bounds are provided in the parameters
     *   minlat, minlng, maxlat, maxlng
     */

    function display($tpl = null)
    {
        echo new \Joomla\CMS\Response\JsonResponse(['TODO']);
    }
}