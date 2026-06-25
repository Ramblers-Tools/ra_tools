<?php

/**
 * @version    5.0.2
 * @component  com_ra_tools
 * @author     Charlie Bigley <webmaster@bigley.me.uk>
 * @copyright  2023 Charlie Bigley
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * 29/02/24 CB Created
 * 30/08/24 B use DatabaseDriver
 * 02/08/24 CB show modified_by
 * 06/09/24 CB include created_by_alias
 * 07/09/24 CB save criteria for subsequent use when displaying a single article
 */

namespace Ramblers\Component\Ra_tools\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\DatabaseFactory;
use Ramblers\Component\Ra_tools\Site\Helpers\ToolsHelper;

class ArticlesModel extends ListModel {

    protected $filter_fields;
    private $params;
    private $cat_id;

    public function __construct($config = []) {
        $menu = Factory::getApplication()->getMenu()->getActive();
        // Restrict search the category specified by the menu
        if (is_null($menu)) {
            Factory::getApplication()->enqueueMessage('Category not specified', 'notice');
        } else {
            $params = $menu->getParams();
        }
//        echo $params . '<br>';
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'a.id',
                'a.title',
                'created.name',
                'updated.name',
                'a.created',
                'a.modified',
            );
            $this->filter_fields = $config['filter_fields'];
        }
        parent::__construct($config);
        if ($params->get('site') == 'remote') {
            Factory::getApplication()->enqueueMessage('Showing Articles from ' . $params->get('remote_url'), 'notice');
            $this->cat_id = $params->get('remote_category_id', 1);
            $options = array();
            $options['driver'] = 'mysqli'; // Database driver name
            $options['host'] = $params->get('server');       // Database host name
            $options['database'] = $params->get('database'); // Database name
            $options['user'] = $params->get('user');         // User for database authentication
            $options['password'] = $params->get('password'); // Password for database authentication
            $options['prefix'] = $params->get('prefix');     // Database prefix
            // external database connection
            $dbFactory = new DatabaseFactory();
            try {
                $dbDriver = $dbFactory->getDriver('mysqli', $options);
                $this->setDatabase($dbDriver);
            } catch (\RuntimeException $exception) {
                Factory::getApplication()->enqueueMessage(
                        Text::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()),
                        'warning'
                );
            }
            if (empty($dbDriver)) {
                throw new \RuntimeException("Joomla did not return a database object.");
            }
        } else {
            $this->cat_id = $params->get('category_id', 1);
        }
    }

    /**
     * Build the criteria for search from the search columns (could be added to ToolsHelper)
     *
     * @param    string        $searchWord        Search for this text

     * @param    string        $searchColumns    The columns in the DB to search for
     */
    protected function buildWhereClause($searchWord, $searchColumns) {

        $criteria = '';
        foreach ($searchColumns as $i => $searchColumn) {
            if ($criteria == '') {
                $criteria .= 'WHERE (';
            } else {
                $criteria .= 'OR ';
            }
            $criteria .= $this->_db->qn($searchColumn) . ' LIKE ' . $this->_db->q('%' . $this->_db->escape($searchWord, true) . '%');
        }
        $criteria .= ') ';
        return $criteria;
    }

    protected function getListQuery() {

        $query = $this->_db->getQuery(true);
        $query->select('a.id, a.title, a.created,created_by_alias,a.modified');
        $query->select("created.name AS `created_by`, updated.name AS `modified_by`");
        $query->from($this->_db->quoteName('#__content', 'a'));
        $query->leftJoin('#__users AS created ON created.id = a.created_by');
        $query->leftJoin('#__users AS updated ON updated.id = a.modified_by');
        $query->where('a.state = 1');
        $query->where('a.catid = ' . $this->cat_id);

        // Filter by search
        $search = $this->getState('filter.search');
        $search_fields = array(
            'a.title',
            'created.name',
            'updated.name',
            'a.created_by_alias',
        );

        if (empty($search)) {
            $criteria = '';
        } else {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int) substr($search, 3));
                $criteria = 'WHERE a.id = ' . (int) substr($search, 3);
            } else {
                $query = ToolsHelper::buildSearchQuery($search, $search_fields, $query);
                $criteria = $this->buildWhereClause($search, $search_fields);
            }
        }
        if ($criteria == '') {
            $criteria = 'WHERE ';
        } else {
            $criteria .= 'AND ';
        }
        $criteria .= ' a.state=1 AND a.catid = ' . $this->cat_id;
        // Filter by year
        $filter_year = $this->state->get("filter.year");
        if ($filter_year) {
            $query->where('a.created >="' . $filter_year . '/01/01" AND a.created <="' . $filter_year . '/12/31"');

            $criteria .= ' AND a.created >="' . $filter_year . '/01/01" AND a.created <="' . $filter_year . '/12/31"';
        }

        // Add the list ordering clause, default to title ASC
        $orderCol = $this->state->get('list.ordering', 'a.title');
        $orderDirn = $this->state->get('list.direction', 'asc');

        if ($orderCol == 'created.name') {
            $orderCol = $this->_db->quoteName('created.name') . ' ' . $orderDirn . ', ' . $this->_db->quoteName('a.title');
        } elseif ($orderCol == 'updated.name') {
            $orderCol = $this->_db->quoteName('updated.name') . ' ' . $orderDirn . ', ' . $this->_db->quoteName('a.title');
        }
        $query->order($this->_db->escape($orderCol . ' ' . $orderDirn));

        // save criteria for subsequent use when displaying a single article
        Factory::getApplication()->setUserState('com_ra_tools.articles_criteria', $criteria);
        Factory::getApplication()->setUserState('com_ra_tools.articles_sort', $orderCol);
        if (JDEBUG) {
            Factory::getApplication()->enqueueMessage('sql = ' . $this->_db->replacePrefix($query), 'notice');
        }
        return $query;
    }

    protected function populateState($ordering = 'a.created', $direction = 'asc') {
        // List state information.
        parent::populateState($ordering, $direction);
    }

}
