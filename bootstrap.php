<?php

// Basic variables
define('APP_ROOT', realpath((dirname(__FILE__))));
define('APP_SRC', APP_ROOT . '/src');
define('APP_LIB', APP_ROOT . '/lib');
define('APP_LOCALE', APP_ROOT . '/locale');
define('APP_REPORTS', APP_ROOT . '/reports');
//define('TMP_DIR', '/var/tmp/veggiecommunity');
define('TMP_DIR', sys_get_temp_dir());
define('CACHE_DIR', sys_get_temp_dir() .'/vc-cache');
define('TEMPLATE_DIR', APP_ROOT . '/template');
define('PROFILE_PIC_DIR', APP_ROOT . '/pictures');
define('GROUP_PIC_DIR', APP_ROOT . '/pictures/groups');
define('THREAD_PIC_DIR', APP_ROOT . '/pictures/thread');
define('EVENT_PIC_DIR', APP_ROOT . '/pictures/events');
define('REAL_PIC_DIR', APP_ROOT . '/pictures/real');
define('TEMP_PIC_DIR', APP_ROOT . '/pictures/temp');
define('MAX_PATH', APP_ROOT . '/web/v');

// Auto loading function
function _autoload($class)
{
    // convert namespace to full file path
    $file = APP_SRC . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once($file);
    }  else {
        vc\lib\ErrorHandler::error(
            'Can\'t find class ' . $class . ' (' . $file . ')',
            __FILE__,
            __LINE__
        );
    }
}


// Basic error handling
function customErrorHandler($errno, $errstr, $errfile, $errline)
{
    if (strpos($errstr, 'session_start()') === FALSE &&
        strpos($errstr, 'stream_socket_client(): unable to connect to tcp://127.0.0.1:6379') === FALSE &&
        strpos($errstr, 'Detected an illegal character in input string') === FALSE &&
        strpos($errstr, 'Detected an incomplete multibyte character in input string') === FALSE &&
        strpos($errstr, 'Invalid UTF-8 sequence in argument') === FALSE &&
        strpos($errstr, 'exif_read_data(') === FALSE &&
        strpos($errstr, 'Automatically populating $HTTP_RAW_POST_DATA is deprecated and will be removed in a future version.') === FALSE &&
        strpos($errstr, 'var_export does not handle circular reference') === FALSE &&
        strpos($errstr, 'gd-jpeg, libjpeg: recoverable error: ') === FALSE &&
        $errstr !== 'Only variables should be passed by reference' &&
        strpos($errfile, '/utf8_char2byte_pos.php') === FALSE &&
        strpos($errfile, '/usr/www/users/veggir/main/web/v/') === FALSE) {
	\vc\lib\ErrorHandler::getInstance()->saveReport(
            $errno,
            $errstr,
            $errfile,
            $errline,
            '-'
        );
    }
}

function fatalHandler()
{
    $error = error_get_last();
    if( $error !== NULL) {
	\vc\lib\ErrorHandler::getInstance()->saveReport(
            $error['type'],
            $error['message'],
            $error['file'],
            $error['line'],
            '-'
        );
    }
}

mb_internal_encoding('UTF-8');
spl_autoload_register('_autoload');
set_error_handler('customErrorHandler');
register_shutdown_function('fatalHandler');
ignore_user_abort(true);

date_default_timezone_set('CET');

if (!session_id()) {
    // Set the session name:
    session_name('veggiecommunity');

    // Set session cookie parameters:
    session_set_cookie_params(
        0, // The session is destroyed on logout anyway, so no use to set this
        '/',
        null,
        null
    );

    // Start the session:
    session_start();
}

//-----------------------------------------------------------------------------


// :TODO: migrator2 - get rid of these global functions
function prepareHTML($text, $replaceSmileset=true, $replaceLinks=false)
{
	$text = htmlspecialchars($text);
	// Three backslash plus '
	while(stripos($text, "\\\\") !== FALSE) {
		$text = str_replace("\\\\", "\\", $text);
	}
	 // backslash plus &quot;
	$text = str_replace("\\&quot;","&quot;", $text);
	// apostrophe
	$text = str_replace("\\'", "'", $text);
	$text = nl2br($text);
	if($replaceLinks) {
		$text = preg_replace("@[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]@", "<a href=\"\\0\" target=\"_blank\" rel=\"nofollow\">\\0</a>", $text);
	}

	if($replaceSmileset) {
//		$replace = array(':)' => 'emoticon_smile.png',
//		                 ':-)' => 'emoticon_smile.png',
//		                 ':D' => 'emoticon_grin.png',
//		                 ':-D' => 'emoticon_grin.png',
//		                 ':p' => 'emoticon_tongue.png',
//		                 ':-p' => 'emoticon_tongue.png',
//		                 ':P' => 'emoticon_tongue.png',
//		                 ':-P' => 'emoticon_tongue.png',
//		                 ':(' => 'emoticon_unhappy.png',
//		                 ':-(' => 'emoticon_unhappy.png',
//		                 ';)' => 'emoticon_wink.png',
//		                 ';-)' => 'emoticon_wink.png',
//		                 ':o' => 'emoticon_surprised.png',
//		                 ':-o' => 'emoticon_surprised.png',
//		                 ':O' => 'emoticon_surprised.png',
//		                 ':-O' => 'emoticon_surprised.png');
//		foreach ($replace as $search => $replace)
//		{
//			$text = str_replace($search,
//			                    sprintf('<img class="smile" src="%s%s" alt="%s" title="%s" />',
//			                            Registry::get('IMG'),
//			                            $replace,
//			                            $search,
//			                            $search),
//			                    $text);
//		}
	}

//    \p{Cc} or \p{Control}: an ASCII 0x00–0x1F or Latin-1 0x80–0x9F control character.
//    \p{Cf} or \p{Format}: invisible formatting indicator.
//    \p{Co} or \p{Private_Use}: any code point reserved for private use.
//    \p{Cs} or \p{Surrogate}: one half of a surrogate pair in UTF-16 encoding.
//    \p{Cn} or \p{Unassigned}: any code point to which no character has been assigned.
//
    // Stripping out all other characters (Not using Control since it also strips german ß)
//    $text = preg_replace(array('@\p{Cf}@', '@\p{Co}@','@\p{Cn}@'), '', $text);
    $text = preg_replace(array('@\p{Co}@','@\p{Cn}@'), '', $text);
	return $text;
}

function prepareURL($url) {
    return filter_var($url, FILTER_VALIDATE_URL);
}

function prepareTextDate($date)
{
	return date("D, d M o H:i:s", $date - date("Z", $date)) ." GMT";
}

function formatLongDate($date, $includeTime = false)
{
    return str_replace(
        array(
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday',
            'Saturday',
            'Sunday',
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December'
        ),
        array(
            gettext('date.weekday.Monday'),
            gettext('date.weekday.Tuesday'),
            gettext('date.weekday.Wednesday'),
            gettext('date.weekday.Thursday'),
            gettext('date.weekday.Friday'),
            gettext('date.weekday.Saturday'),
            gettext('date.weekday.Sunday'),
            gettext('date.month.January'),
            gettext('date.month.February'),
            gettext('date.month.March'),
            gettext('date.month.April'),
            gettext('date.month.May'),
            gettext('date.month.June'),
            gettext('date.month.July'),
            gettext('date.month.August'),
            gettext('date.month.September'),
            gettext('date.month.October'),
            gettext('date.month.November'),
            gettext('date.month.December'),
        ),
        date(gettext($includeTime ? 'date.day.longTime' : 'date.day.long'), $date)
    );
}

function prepareFormValue($text)
{
//	$text = prepareXML($text);
	$text = str_replace("<", "&lt;", $text);
	$text = str_replace(">", "&gt;", $text);
	$text = str_replace("\\\\", "\\", $text);
	$text = str_replace("\\\"", "\"", $text);
	$text = str_replace("\"", "&quot;", $text);
	return $text;
}

function isEmpty($id)
{
	global $_POST;
	if(array_key_exists($id, $_POST))
	{
		$value = $_POST[$id];
		return empty($value) || trim($value) == "";
	}
	else
	{
		return true;
	}
}

function isEMailValid($email)
{
    $expression1 = '/^[a-z0-9!#$%&*+-=?^_`{|}~]+(\.[a-z0-9!#$%&*+-=?^_`{|}~]+)*' .
                   '@([-a-z0-9]+\.)+([a-z]{2,3}' .
                   '|info|arpa|aero|coop|name|museum)$/ix';
    $expression2 = '/^(?!(?>(?1)"?(?>\\\[ -~]|[^"])"?(?1)){255,})(?!(?>(?1)"?(?>\\\[ -~]|[^"])"?(?1)){65,}@)' .
                   '((?>(?>(?>((?>(?>(?>\x0D\x0A)?[\t ])+|(?>[\t ]*\x0D\x0A)?[\t ]+)?)(\((?>(?2)' .
                   '(?>[\x01-\x08\x0B\x0C\x0E-\'*-\[\]-\x7F]|\\\[\x00-\x7F]|(?3)))*(?2)\)))+(?2))|(?2))?)' .
                   '([!#-\'*+\/-9=?^-~-]+|"(?>(?2)(?>[\x01-\x08\x0B\x0C\x0E-!#-\[\]-\x7F]|\\\[\x00-\x7F]))*' .
                   '(?2)")(?>(?1)\.(?1)(?4))*(?1)@(?!(?1)[a-z0-9-]{64,})(?1)(?>([a-z0-9](?>[a-z0-9-]*[a-z0-9])?)' .
                   '(?>(?1)\.(?!(?1)[a-z0-9-]{64,})(?1)(?5)){0,126}|\[(?:(?>IPv6:(?>([a-f0-9]{1,4})(?>:(?6)){7}' .
                   '|(?!(?:.*[a-f0-9][:\]]){8,})((?6)(?>:(?6)){0,6})?::(?7)?))|(?>(?>IPv6:(?>(?6)(?>:(?6)){5}:' .
                   '|(?!(?:.*[a-f0-9]:){6,})(?8)?::(?>((?6)(?>:(?6)){0,4}):)?))?(25[0-5]|2[0-4][0-9]|1[0-9]{2}' .
                   '|[1-9]?[0-9])(?>\.(?9)){3}))\])(?1)$/isD';
    return preg_match($expression1, $email) && preg_match($expression2, $email);
}

function cutMaxLength($text, $maxlength)
{
	if(strlen($text) > $maxlength)
	{
		return substr($text, 0, $maxlength - 3) . "...";
	}
	else
	{
		return $text;
	}
}

function prepareJavascript($text)
{
    $text = str_replace("\r","", $text);
    $text = str_replace("\n","\\n", $text);
    $text = str_replace ("\\\\", "\\", $text);
    $text = str_replace ("'", "\\'", $text);
    $text = str_replace ("\\\\'", "\\'", $text);
    return $text;
}

function implodeQuery(&$parameters, $excludeQ = true)
{
    $return = '';
    foreach($parameters as $tk => $tv)
    {
        if($tk !== 'inline' &&
           $tk !== 'notification' &&
           ($tk != 'q' || !$excludeQ)) {
            if(is_array($tv)) {
                foreach($tv as $subvalue) {
                    // %5B = [
                    // %5D = ]
                    $return .= '&amp;' . $tk . '%5B%5D=' . urlencode($subvalue);
                }
            } else {
                $return .= '&amp;' . $tk . '=' . urlencode($tv);
            }
        }
    }
    return substr($return, 5);
}

function arrayAppend(&$array, $append)
{
    foreach($append as $value) {
        $array[] = $value;
    }
}
