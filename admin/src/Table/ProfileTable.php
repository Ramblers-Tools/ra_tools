<?php

/**
 * @package     com_ra_tools.Administrator
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
  21/12/24 CB created from site/ra_profiles.php
 */

namespace Ramblers\Component\Ra_tools\Administrator\Table;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

/**
 * profiles table
 *
 * @since  1.5
 */
class ProfilesTable extends Table {

    /**
     * Constructor
     *
     * @param   DatabaseDriver  $db  Database connector object
     *
     * @since   1.0
     */
    public function __construct(DatabaseDriver $db) {
        parent::__construct('#__ra_profiles', 'id', $db);
        die('construct Profile');
    }

    public function store($updateNulls = true) {
        if ($this->id > 0) {
            $this->modified_by = Factory::getApplication()->getSession()->get('user')->id;
            $this->modified = Factory::getDate('now', Factory::getConfig()->get('offset'))->toSql(true);
        }
        return parent::store($updateNulls);
    }

}
