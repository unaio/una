<?php

$mixCheckResult = 'Update can not be applied';

$sVer = $this->oDb->getOne("SELECT `version` FROM `sys_modules` WHERE `name` = 'system'");
if ('12.0.0.B2' == $sVer || '12.0.0-B2' == $sVer)
    $mixCheckResult = true;

return $mixCheckResult;
