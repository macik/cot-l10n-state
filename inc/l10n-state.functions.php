<?php

defined('COT_CODE') or die('Wrong URL');

define('L10N_UNCHECKED', 	0);
define('L10N_REFINED', 		1);
define('L10N_GOOD', 		2);
define('L10N_SUFFICIENT', 	3);
define('L10N_FAINT', 		4);
define('L10N_INCOMPLETE', 	5);
define('L10N_BAD_STRUCTURE',	6);
define('L10N_INAPPLICABLE', 7);
define('L10N_NOT_FINISHED', 8);
define('L10N_NOT_TRANSLATED', 9);
define('L10N_OUTDATED', 10);

$conform_states = array('unchecked','refined', 'good', 'sufficient', 'faint', 'incomplete', 'bad_structure','inapplicable','not_finished','not_translated','outdated');

/**
 * Counts number of «words» in given string
 * @param string $string Source string with text
 * @param bool $skip_latin Leaves only words with non-latin (a-z) characters
 * @param number $min_word_len Skips words with length less than defined here
 * @return number
 */
function cot_word_count($string, $skip_latin = false, $min_word_len = 3)
{
	$wl = $min_word_len - 1;
	$string = strip_tags($string);
	$string = str_replace("&#039;", "'", $string);
	$string = preg_replace('/[^\w\s]/u', ' ', $string);
	if ($skip_latin)
	{
		$string = preg_replace('/\b[a-z]+\b/i', '', $string);
	}
	$string = preg_replace('/(\b\S{1,' . $wl . '}\b)/u', '', $string);
	$num = preg_match_all('/\s+/', trim($string));
	return $num;
}

/**
 * Cuts code tags from HTML (e.g. «pre», «code», «var», «samp», etc )
 * for further text compare
 * @param string $string
 * @return string
 */
function cot_strip_code($string)
{
	$droped_tags = array('pre', 'code', 'var', 'samp', 'kbd');
	foreach ($droped_tags as $tag)
	{
		$string = preg_replace('`<(' . $tag . ')[^>]*?>.*?<\/\1>`si', '', $string);
	}
	return $string;
}

/**
 * Returns correlation coefficient for certain Tags presented in source and localized texts
 * @param array $pag_data
 * @param string $tag_re RegEpx for tag search
 * @param number $influence Influence rate
 * @param number $min_tag_count Minimal count of tags to test
 * @param float $ext
 */
function tag_conform(&$pag_data, $tag_re, $influence = 1, $min_tag_count = 2,$ext=false)
{
	$conform = 1;
	if (!$min_tag_count || $min_tag_count<1) $min_tag_count =1;
	if ($influence)
	{
		$re = '`<(' . $tag_re . ')[^>]*?>.*?<\/\1>`si';
		$orig = preg_match_all($re, $pag_data['page_text']) + 1 ;
		$local = preg_match_all($re, $pag_data['ipage_text']) + 1;
		$cmp = ( $orig - $local ) / $orig;
		if (abs($cmp) > 0.5) return 2;
		if ($orig < $min_tag_count+1) return $conform;

		if (!$local) $influence = 1;
		$conform = (1 + $cmp / $influence);
	}
	if ($ext && $conform<1) $conform = abs($conform-1)+1;
	return $conform;
}

/**
 * Calculates rate of localization conform source text.
 * Based on comparison text structure (heading and PRE tags) and words counts.
 * Can be fine tune for certain language, see `inc/l10n-state.ru.php` file
 *
 * @param array $pag_data Array of page data as get in pagelist or single page (see $pag variable)
 * @param string $details Name of certain field of result set. Set 'all' to return all result data set
 * @return mixed Result set or single result set field
 */
function l10n_check_common(&$pag_data, $details = null)
{
	global $conform_states;

	$text_diff = 0;
	$conform_kf = $tag_diff = 1;
	$diff_state = null;
	$result = array();

	// common defaults
	$conform_levels = array(
		-99999 => L10N_INAPPLICABLE,
		   -30 => L10N_GOOD,
		    30 => L10N_SUFFICIENT,
		    50 => L10N_FAINT,
		   100 => L10N_INCOMPLETE,
		   500 => L10N_INAPPLICABLE
	);

	$loc_text = cot_strip_code($pag_data['ipage_text']);
	$local = cot_word_count($loc_text);
	if (!$local)
	{
		$diff_state = L10N_INAPPLICABLE;
	}
	else
	{
		$tags_check_threshold = 75;
		$orig = cot_word_count(cot_strip_code($pag_data['page_text']));
		if (abs(($orig - $local) / $orig) > 0.4)
		{
			$diff_state = L10N_INAPPLICABLE;
		}
		else
		{
			$l10n_lang = $pag_data[ipage_locale];
			$lang_specific_file = cot_incfile('l10n-state', 'plug', $l10n_lang);
			if ($l10n_lang && is_file($lang_specific_file))
			{
				require_once $lang_specific_file;
			}
			else
			{
				$text_diff = round($orig / $local * 100 - 100);
			}
		}

		if (!$diff_state && $text_diff < $tags_check_threshold)
		{
			// addition check for structure
			$tag_check = array(
				array('pre',1.5,3),
				array('h1',0.33,1),
				array('h2',1.5,2),
				array('h3',1,3)
			);
			$tags_kf = array();
			foreach ($tag_check as $param)
			{
				$tags_kf[] = tag_conform($pag_data, $param[0], $param[1], $param[2], $text_diff < 10);
			}
			$tag_diff = array_product($tags_kf);
			if ($tag_diff == 1) $conform_kf *= 2;
			if ($tag_diff > 1.7) $diff_state = L10N_BAD_STRUCTURE;
		}
	}

	$total_conform = $text_diff;
	if (!$diff_state)
	{
		$total_conform = $text_diff * $tag_diff / $conform_kf;
		$diff_state = L10N_UNCHECKED;
		foreach ($conform_levels as $val => $state) {
			if ($total_conform > $val) $diff_state = $state;
		}
	}

	$result['original'] = $orig; // original text words count
	$result['local'] = $local; // localized text words count
	$result['difference'] =  $text_diff; // difference coefficient
							// (0 - better conform, > 0 — source is more complex, < 0 — localization is more complex )
	$result['tag_diff'] = $tag_diff; // tags difference coeff
	$result['tags_kf']= print_r($tags_kf,1); // array with detailed tags difference data
	$result['total'] = $total_conform; // result difference coefficient
	$result['state'] = $diff_state; // difference type — see defined constants `L10N_*` and $conform_states
	$result['msg_id'] = $conform_states[$diff_state]; // string id to get message with `cot_rc` function
	// see language specific files for other data returned with result set

	if ($details) {
		return (array_key_exists($details, $result)) ? $result[$details] : $result;
	}
	else
	{
		return $result['state'];
	}
}


/**
 * Return data describes translation state and its «quality».
 * Based on comparison text structure (heading and PRE tags) and words counts.
 *
 * @param array $pag_data Array of page data as get in pagelist or single page (see $pag variable)
 * @param string $details Name of certain field of result set. Set 'all' to return all result data set
 * @return mixed Result set or single result set field
 *
 * @see l10n_check_common()
 */
function l10n_check(&$pag_data, $details=null)
{
	$cache_ttl = cot::$cfg['plugin']['l10n-state']['ttl'] * 3600;
	$check = array();

	$l10n_lang = $pag_data['ipage_locale'];
	$cache_loaded = false;
	if (cot::$cache && $cache_ttl >= 0)
	{
		$cache_name = 'l10n-page_' . $l10n_lang . '_' . $pag_data['page_id'];
		global $$cache_name;
		if ($$cache_name)
		{
			$check = $$cache_name;
			$cache_loaded = true;
		}
	}

	if (!$cache_loaded)
	{
		if (!$pag_data['ipage_text'] || preg_match('`^(redir|inlude):`', $pag_data['page_text']))
		{
			$check['state'] = L10N_NOT_TRANSLATED;
		}
		else
		{
			if ($pag_data['page_updated'] > $pag_data['ipage_date'])
			{
				$check['state'] = L10N_OUTDATED;
			}
			else
			{
				$check = l10n_check_common($pag_data, 'all');
			}
		}
		if (cot::$cache && $cache_ttl >= 0)
		{
			// random seed ±15% for case of call from page list
			// to prevent mass cache renew next time
			$ttl_random_seed = (mt_rand(0, 30)-15)/100;
			cot::$cache->db->store($cache_name, $check, 'cot', round($cache_ttl * $ttl_random_seed));
		}
	}

	if ($details)
	{
		return (array_key_exists($details, $check)) ? $check[$details] : $check;
	}
	else
	{
		return $check['state'];
	}
}

/**
 * Returns human readable message based on conform level checked with `l10n_check()` function
 *
 * @param array $pag_data Array of page data as get in pagelist or single page (see $pag variable)
 * @param bool $all_msg Show all messages or only warnings
 * @return string
 */
function l10n_msg($pag_data, $all_msg = false){
	global $conform_states;
	$message = '';
	$state = l10n_check($pag_data,'all');
	$msg_id = $conform_states[$state['state']];
	switch ($state['state']) {
		case L10N_GOOD:
		case L10N_SUFFICIENT:
		case L10N_NOT_TRANSLATED:
			if ($all_msg) $message = cot_rc("l10n_state_$msg_id");
			break;
		case L10N_OUTDATED:
			$time = (time() - $pag_data['page_updated']) / (24*3600);
			$ago = ($time > 1) ? cot_declension(floor($time), 'Days') : cot_declension(ceil($time*24), 'Hours');
			$message = cot_rc('l10n_state_'.$msg_id,
							array(
								'date'=> ' '.cot_date('date_text', $pag_data['page_updated']),
								'ago' => $ago
							)
						);
			break;
		default:
			$message = cot_rc('l10n_state_'.$msg_id);
	}
	return $message;
}

/**
 * Generate message box with checking results to display in templates
 *
 * @param array $pag_data Array of page data as get in pagelist or single page (see $pag variable)
 * @param bool $all_msg Show all messages or only warnings
 * @return HTML generated with template
 */
function l10n_state_widget($pag_data, $all_msg = false){
	static $l10n_tpl = null;
	$prefix = 'L10N_STATE_';
	if (!$l10n_tpl) $l10n_tpl = new XTemplate(cot_tplfile('l10n-state','plug'));

	$state = l10n_check($pag_data, 'all');
	$message = l10n_msg($pag_data, $all_msg);
	if (!$message) return '';
	$class = is_array(cot::$R['l10n_state_class']) ? cot::$R['l10n_state_class'][$state['state']] : cot::$R['l10n_state_class'];
	$state = array_change_key_case($state,CASE_UPPER);
	$l10n_tpl->assign($state, null, $prefix);
	$l10n_tpl->assign(array(
		'MESSAGE' => $message,
		'CLASS' => $class
	), null, $prefix);
	return $l10n_tpl->parse()->text();
}