<?php
/**
 * Copyright (c) 2019 TopLiner, Scheme of constructs
 * 6.12.2019 22:51 Volkhin Nikolay
 */

const LOCAL = 'local';
$_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__) . '/..';

require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');
require_once($_SERVER['DOCUMENT_ROOT']
    . '/bitrix/modules/main/include/prolog_before.php');
define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_STATISTIC', true);

$isLocal = getenv(LOCAL, true);

return $isLocal;
