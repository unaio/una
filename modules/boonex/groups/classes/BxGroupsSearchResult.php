<?php defined('BX_DOL') or die('hack attempt');
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 *
 * @defgroup    Groups Groups
 * @ingroup     TridentModules
 *
 * @{
 */

class BxGroupsSearchResult extends BxBaseModProfileSearchResult
{
    function __construct($sMode = '', $aParams = false)
    {
        parent::__construct($sMode, $aParams);

        $this->aCurrent =  array(
            'name' => 'bx_groups',
            'module_name' => 'bx_groups',
            'object_metatags' => 'bx_groups',
            'title' => _t('_bx_groups_page_title_browse'),
            'table' => 'sys_profiles',
            'tableSearch' => 'bx_groups_data',
            'ownFields' => array(),
            'searchFields' => array('group_name', 'group_desc'),
            'restriction' => array(
        		'account_id' => array('value' => '', 'field' => 'account_id', 'operator' => '='),
                'perofileStatus' => array('value' => 'active', 'field' => 'status', 'operator' => '='),
                'perofileType' => array('value' => 'bx_groups', 'field' => 'type', 'operator' => '='),
                'owner' => array('value' => '', 'field' => 'author', 'operator' => '=', 'table' => 'bx_groups_data'),
            ),
            'join' => array (
                'profile' => array(
                    'type' => 'INNER',
                    'table' => 'bx_groups_data',
                    'mainField' => 'content_id',
                    'onField' => 'id',
                    'joinFields' => array('id', 'group_name', 'picture', 'added'),
                ),
                'account' => array(
                    'type' => 'INNER',
                    'table' => 'sys_accounts',
                    'mainField' => 'account_id',
                    'onField' => 'id',
                    'joinFields' => array(),
                ),
            ),
            'paginate' => array('perPage' => 20, 'start' => 0),
            'sorting' => 'none',
            'rss' => array(
                'title' => '',
                'link' => '',
                'image' => '',
                'profile' => 0,
                'fields' => array (
                    'Guid' => 'link',
                    'Link' => 'link',
                    'Title' => 'group_name',
                    'DateTimeUTS' => 'added',
                    'Desc' => 'group_name',
                    'Picture' => 'picture',
                ),
            ),
            'ident' => 'id'
        );

        $this->sFilterName = 'bx_groups_data_filter';
        $this->oModule = $this->getMain();

        switch ($sMode) {

            case 'connections':
                if ($this->_setConnectionsConditions($aParams)) {
                    $oProfile = BxDolProfile::getInstance($aParams['profile']);
                    $oProfile2 = isset($aParams['profile2']) ? BxDolProfile::getInstance($aParams['profile2']) : null;

                    if (isset($aParams['type']) && $aParams['type'] == 'common' && $oProfile && $oProfile2)
                        $this->aCurrent['title'] = _t('_bx_groups_page_title_browse_connections_mutual', $oProfile->getDisplayName(), $oProfile2->getDisplayName());
                    elseif ((!isset($aParams['type']) || $aParams['type'] != 'common') && $oProfile)
                        $this->aCurrent['title'] = _t('_bx_groups_page_title_browse_connections', $oProfile->getDisplayName());

                    $this->aCurrent['rss']['link'] = 'modules/?r=groups/rss/' . $sMode . '/' . $aParams['object'] . '/' . $aParams['type'] . '/' . (int)$aParams['profile'] . '/' . (int)$aParams['profile2'] . '/' . (int)$aParams['mutual'];
                }
                break;

            case 'recent':
                $this->aCurrent['rss']['link'] = 'modules/?r=groups/rss/' . $sMode;
                $this->aCurrent['title'] = _t('_bx_groups_page_title_browse_recent');
                $this->aCurrent['sorting'] = 'last';
                $this->sBrowseUrl = 'page.php?i=groups-home';
                break;

            case 'active':
                $this->aCurrent['rss']['link'] = 'modules/?r=groups/rss/' . $sMode;
                $this->aCurrent['title'] = _t('_bx_groups_page_title_browse_active');
                $this->aCurrent['sorting'] = 'active';
                $this->sBrowseUrl = 'page.php?i=groups-active';
                break;

            case '': // search results
                $this->sBrowseUrl = BX_DOL_SEARCH_KEYWORD_PAGE;
                $this->aCurrent['title'] = _t('_bx_groups');
                $this->aCurrent['paginate']['perPage'] = 5;
                unset($this->aCurrent['rss']);
                break;

            default:
                $this->isError = true;
        }

        parent::__construct();
    }

    function getAlterOrder()
    {
        switch ($this->aCurrent['sorting']) {
        case 'none':
            return array('order' => ' ORDER BY `sys_accounts`.`logged` DESC ');
        case 'last':
        default:                        
            return array('order' => ' ORDER BY `bx_groups_data`.`added` DESC ');
        }
    }

    function _getPseud ()
    {
        return array(
            'id' => 'id',
            'group_name' => 'group_name',
            'added' => 'added',
            'author' => 'author',
            'picture' => 'picture',
        );
    }
}

/** @} */
