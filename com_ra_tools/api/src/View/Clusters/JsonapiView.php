<?php
/**
 * @version    3.5.5
 * @package    com_ra_tools
 * @author     Charlie Bigley <charlie@bigley.me.uk>
 * @copyright  2026 Charlie Bigley
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Ramblers\Component\Ra_tools\Api\View\Clusters;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;

/**
 * JSON API view for clusters
 *
 * @since  1.0.0
 */
class JsonapiView extends BaseApiView
{
    /**
     * Fields to render when fetching a single record.
     * Includes fields from joined tables (read-only).
     *
     * @var array
     * @since 1.0.0
     */
    protected $fieldsToRenderItem = [
        'id',
        'code',
        'name',
        'area_list',
        'website',
        'contact_id',
        'created',
        'modified',
        'preferred_name',
        'email',
    ];

    /**
     * Fields to render when fetching multiple records.
     * Can include calculated/joined fields.
     *
     * @var array
     * @since 1.0.0
     */
    protected $fieldsToRenderList = [
        'id',
        'code',
        'name',
        'area_list',
        'website',
        'contact_id',
        'created',
        'modified',
        'preferred_name',
        'email',
    ];
}
