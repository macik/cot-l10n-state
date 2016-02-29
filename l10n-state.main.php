<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=page.list.first,page.tags
[END_COT_EXT]
==================== */

/**
 * Pages l10n state check
 *
 * @package l10n-state
 * @author Andrey Matsovkin
 * @copyright Copyright (c) 2011-2012
 * @license Distributed under BSD license.
 */

defined('COT_CODE') or die('Wrong URL');

if ($i18n_notmain) {
	$l10n_state = $i18n_notmain;
	require_once cot_incfile('l10n-state','plug');
	require_once cot_incfile('l10n-state','plug','rc');
	$langfile = cot_langfile('l10n-state','plug');
	if ($langfile) require_once cot_langfile('l10n-state','plug');
}