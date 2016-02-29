<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=page.edit.update.first,i18n.page.add.done,i18n.page.edit.update
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

if (cot::$cache && cot::$cfg['plugin']['l10n-state']['ttl'] >= 0 )
{
	foreach ($i18n_locales as $locale) {
		cot::$cache->db->remove('l10n-page_'.$locale.'_'.$id, 'cot');
	}

}

