<?php
/**
 * English Language File for l10n-state plugin
 *
 * @package l10n-state
 * @license BSD
 */

defined('COT_CODE') or die('Wrong URL.');

/**
 * Plugin Config
 */

$L['cfg_ttl'] =  'Cache TTL, in hours';

$L['info_desc'] = 'Page localization conformness checker';

$L['l10n_state_unchecked'] = 'Translation not checked';
$L['l10n_state_refined'] = 'Translated page is extensive compared to original. Check original page for sure.';
$L['l10n_state_good'] = 'Translation exists (size/structure conformed).';
$L['l10n_state_sufficient'] = 'Translation is done in sufficient way.';
$L['l10n_state_faint'] = 'Translation of this page could be insufficient. Look for original page if needed.';
$L['l10n_state_incomplete'] = 'Translation of this page may be incomplete. See also original page.';
$L['l10n_state_bad_structure'] = 'Note! Structure of this page is differ from original article, so translation could be wrong or not full.';
$L['l10n_state_inapplicable'] = 'Translation of this page does not match with original text. Content may be outdated or unfinished.';
$L['l10n_state_not_finished'] = 'Translation of this page is still in progress. Please read original article instead.';
$L['l10n_state_not_translated'] = 'Page is not translated yet.';
$L['l10n_state_outdated'] = 'Original article had been updated{$date}. Translation is outdated for {$ago}.';

