<?php

defined('COT_CODE') or die('Wrong URL');

// finetune defaults ----------------------------
$tags_check_threshold = 80-log($local,10)*10;

$conform_levels = array(
	-99999 => L10N_INAPPLICABLE,
	   -25 => L10N_REFINED,
	   -10 => L10N_GOOD,
	    10 => L10N_SUFFICIENT,
	    30 => L10N_FAINT,
	    50 => L10N_INCOMPLETE,
	   500 => L10N_INAPPLICABLE
);
// ---------------------------------------------

$ru_words = cot_word_count($loc_text, 1);
$en_words = $local - $ru_words;
$complete = $ru_words / $local * 100;

$k = 1.36;
$threshold = atan(($t + 50 / ($t + 1)) / 600 + $k) * 2 / pi() * 100;
if ($complete < 25)
{
	$diff_state = L10N_NOT_TRANSLATED;
}
elseif ($orig / $ru_words > 2 || $complete < $threshold)
{
	// translation uncompleted
	$diff_state = L10N_INCOMPLETE;
}
else
{
	if ($en_words >= $orig)
	{
		$diff_state = L10N_INAPPLICABLE;
	}
	else
	{
		$orig_o = $orig - $en_words;
	}
	$text_diff = round($orig_o / ($ru_words ? $ru_words : 1) * 100 - 100);
}

// add laguage specific data to result set
$result['ru_words'] = $ru_words; // Russian words count (really not only Russian but all non Latin)
$result['ru_translated'] = $complete; // % of text translation (0-100)
// --------------------------