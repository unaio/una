<?php defined('BX_DOL') or die('hack attempt');
/**
 * Copyright (c) UNA, Inc - https://una.io
 * MIT License - https://opensource.org/licenses/MIT
 *
 * @defgroup    BaseNotifications Base classes for Notifications like modules
 * @ingroup     UnaModules
 *
 * @{
 */

define('BX_BASE_MOD_NTFS_HANDLER_TYPE_INSERT', 'insert');
define('BX_BASE_MOD_NTFS_HANDLER_TYPE_UPDATE', 'update');
define('BX_BASE_MOD_NTFS_HANDLER_TYPE_DELETE', 'delete');

define('BX_BASE_MOD_NTFS_TYPE_OWNER', 'owner');
define('BX_BASE_MOD_NTFS_TYPE_OBJECT_OWNER', 'object_owner');
define('BX_BASE_MOD_NTFS_TYPE_CONNECTIONS', 'connections');
define('BX_BASE_MOD_NTFS_TYPE_PUBLIC', 'public');

/**
 * Base module class.
 */
class BxBaseModNotificationsModule extends BxBaseModGeneralModule
{
	public $_iOwnerId;

    function __construct(&$aModule)
    {
        parent::__construct($aModule);

        $this->_oConfig->init($this->_oDb);

        $this->_iOwnerId = 0;
    }

    /**
     * @page service Service Calls
     * @section bx_base_notifications Base Notifications
     * @subsection bx_base_notifications-other Other
     * @subsubsection bx_base_notifications-add_handlers add_handlers
     * 
     * @code bx_srv('bx_notifications', 'add_handlers', [...]); @endcode
     * 
     * Register handlers for specified module.
     *
     * @param $sModuleUri string with module URI.
     * 
     * @see BxBaseModNotificationsModule::serviceAddHandlers
     */
    /** 
     * @ref bx_base_notifications-add_handlers "add_handlers"
     */
	public function serviceAddHandlers($sModuleUri)
    {
        $this->_updateModuleData('add_handlers', $sModuleUri);
    }

    /**
     * @page service Service Calls
     * @section bx_base_notifications Base Notifications
     * @subsection bx_base_notifications-other Other
     * @subsubsection bx_base_notifications-delete_handlers delete_handlers
     * 
     * @code bx_srv('bx_notifications', 'delete_handlers', [...]); @endcode
     * 
     * Unregister handlers for specified module.
     *
     * @param $sModuleUri string with module URI.
     * 
     * @see BxBaseModNotificationsModule::serviceDeleteHandlers
     */
    /** 
     * @ref bx_base_notifications-delete_handlers "delete_handlers"
     */
    public function serviceDeleteHandlers($sModuleUri)
    {
        $this->_updateModuleData('delete_handlers', $sModuleUri);
    }

    /**
     * @page service Service Calls
     * @section bx_base_notifications Base Notifications
     * @subsection bx_base_notifications-other Other
     * @subsubsection bx_base_notifications-delete_module_events delete_module_events
     * 
     * @code bx_srv('bx_notifications', 'delete_module_events', [...]); @endcode
     * 
     * Delete all events for specified module.
     *
     * @param $sModuleUri string with module URI.
     * 
     * @see BxBaseModNotificationsModule::serviceDeleteModuleEvents
     */
    /** 
     * @ref bx_base_notifications-delete_module_events "delete_module_events"
     */
	public function serviceDeleteModuleEvents($sModuleUri)
    {
        $this->_updateModuleData('delete_module_events', $sModuleUri);
    }

    /**
     * @page service Service Calls
     * @section bx_base_notifications Base Notifications
     * @subsection bx_base_notifications-other Other
     * @subsubsection bx_base_notifications-get_actions_checklist get_actions_checklist
     * 
     * @code bx_srv('bx_notifications', 'get_actions_checklist', [...]); @endcode
     * 
     * Get available actions for module settings in Studio.
     *
     * @return an array with available actions represented as key => value pairs.
     * 
     * @see BxBaseModNotificationsModule::serviceGetActionsChecklist
     */
    /** 
     * @ref bx_base_notifications-get_actions_checklist "get_actions_checklist"
     */
    function serviceGetActionsChecklist()
    {
    	$sLangPrefix = $this->_oConfig->getPrefix('language');
        $aHandlers = $this->_oConfig->getHandlers();

        $aResults = array();
        foreach($aHandlers as $aHandler) {
            if($aHandler['type'] != BX_BASE_MOD_NTFS_HANDLER_TYPE_INSERT)
                continue;

			$_sUnit = '_' . $aHandler['alert_unit'];
			$sUnit = _t($_sUnit);
			if(strcmp($_sUnit, $sUnit) === 0)
				$sUnit = _t($sLangPrefix . '_alert_module_' . $aHandler['alert_unit']);

			$sAction = '';
            if(!empty($aHandler['alert_action'])) {
            	$_sAction = '_' . $aHandler['alert_unit'] . '_alert_action_' . $aHandler['alert_action'];
            	$sAction = _t($_sAction);
            	if(strcmp($_sAction, $sAction) === 0)
					$sAction = _t($sLangPrefix . '_alert_action_' . $aHandler['alert_action']);

            	$sAction = ' (' . $sAction . ')';
            }

            $aResults[$aHandler['id']] = $sUnit . $sAction;
        }

        asort($aResults);
        return $aResults;
    }

    public function isAllowedView($aEvent, $bPerform = false)
    {
		return true;
    }

    public function getOwnerId()
    {
    	return $this->_iOwnerId;
    }

	protected function _updateModuleData($sAction, $sModuleUri)
    {
    	$sMethod = $this->_oConfig->getHandlersMethod();

        $aModule = $this->_oDb->getModuleByUri($sModuleUri);
		if(!BxDolRequest::serviceExists($aModule, $sMethod))
        	return;

		$aData = BxDolService::call($aModule['name'], $sMethod);
		if(empty($aData) || !is_array($aData))
        	return;

		switch($sAction) {
			case 'add_handlers':
				$this->_oDb->insertData($aData);
				BxDolAlerts::cacheInvalidate();

				$this->_oDb->activateModuleEvents($aData, true);
				break;

			case 'delete_handlers':
				$this->_oDb->deleteData($aData);
				BxDolAlerts::cacheInvalidate();

				$this->_oDb->activateModuleEvents($aData, false);
				break;

			case 'delete_module_events':
				$this->_oDb->deleteModuleEvents($aData);
				break;
		}
    }
}

/** @} */
