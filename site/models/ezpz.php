<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_ezpz
 *
 * @copyright   Copyright (C) 2020 - 2021 Ezpizee Co., Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

JLoader::register('EzpzHelperRoute', JPATH_ROOT . '/components/com_ezpz/helpers/route.php');

/**
 * Ezpz Model
 *
 * @since  0.0.1
 */
class EzpzModelEzpz extends JModelItem
{
    /**
     * @var object item
     */
    protected $item;

    /**
     * Method to auto-populate the model state.
     *
     * This method should only be called once per instantiation and is designed
     * to be called on the first call to the getState() method unless the model
     * configuration flag to ignore the request is set.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @return	void
     * @since	2.5
     */
    protected function populateState()
    {
        // Get the message id
        $jinput = JFactory::getApplication()->input;
        $id     = $jinput->get('id', 1, 'INT');
        $this->setState('message.id', $id);

        // Load the parameters.
        $this->setState('params', JFactory::getApplication()->getParams());
        parent::populateState();
    }

    /**
     * Method to get a table object, load it if necessary.
     *
     * @param   string  $type    The table name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  JTable  A JTable object
     *
     * @since   1.6
     */
    public function getTable($type = 'Ezpz', $prefix = 'EzpzTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Get the message
     * @return object The message to be displayed to the user
     */
    public function getItem($id = null)
    {
        if (!isset($this->item) || !is_null($id))
        {
            $id    = is_null($id) ? $this->getState('message.id') : $id;
            $db    = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->select('h.*')
                ->from('#__ezpz as h')
                ->where('h.id=' . (int)$id);

            $db->setQuery((string)$query);

            if ($this->item = $db->loadObject())
            {
                // Load the JSON string
                $params = new JRegistry;
                $params->loadString($this->item->params, 'JSON');
                $this->item->params = $params;

                // Merge global params with item params
                $params = clone $this->getState('params');
                $params->merge($this->item->params);
                $this->item->params = $params;

                // Convert the JSON-encoded image info into an array
                $image = new JRegistry;
                $image->loadString($this->item->image, 'JSON');
                $this->item->imageDetails = $image;

                // Check if the user can access this record (and category)
                $user = JFactory::getUser();
                $userAccessLevels = $user->getAuthorisedViewLevels();
                if ($user->authorise('core.admin')) // ie superuser
                {
                    $this->item->canAccess = true;
                }
                else
                {
                    if ($this->item->catid == 0)
                    {
                        $this->item->canAccess = in_array($this->item->access, $userAccessLevels);
                    }
                    else
                    {
                        $this->item->canAccess = in_array($this->item->access, $userAccessLevels) && in_array($this->item->catAccess, $userAccessLevels);
                    }
                }
            }
            else
            {
                throw new Exception('Ezpz id not found', 404);
            }
        }
        return $this->item;
    }

    public function getMapParams()
    {
        if ($this->item)
        {
            return [];
        }
        else
        {
            throw new Exception('No ezpz details available for map', 500);
        }
    }

    public function getMapSearchResults($mapbounds)
    {
        return $this->_getMapSearchResults($mapbounds);
    }

    public function _getMapSearchResults($mapbounds)
    {
        try
        {
            $db    = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->select('h.*')
                ->from('#__ezpz as h')
                ->where('1');

            $db->setQuery($query);
            $results = $db->loadObjectList();
        }
        catch (Exception $e)
        {
            $msg = $e->getMessage();
            JFactory::getApplication()->enqueueMessage($msg, 'error');
            $results = null;
        }

        for ($i = 0; $i < count($results); $i++)
        {
            $results[$i]->url = JRoute::_('index.php?option=com_ezpz&view=ezpz&id=' . $results[$i]->id .
                ":" . $results[$i]->alias . '&catid=' . $results[$i]->catid);
        }

        return $results;
    }

    public function getChildren($id)
    {
        $table = $this->getTable();
        $children = $table->getTree($id);
        return $children;
    }
}