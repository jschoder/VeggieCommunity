<?php
namespace vc\component;

class I14nComponent extends AbstractComponent
{
    public function loadLocale($locale)
    {
        // Setting the locales
        if ($locale == 'de') {
            setlocale(LC_ALL, 'de_DE.utf8');
            $path = 'de_DE';
        } elseif ($locale == 'en') {
            setlocale(LC_ALL, 'en_US.utf8');
            $path = 'en_US';
        } else {
            setlocale(LC_ALL, 'en_US.utf8');
            $path = 'en_US';
            \vc\lib\ErrorHandler::error(
                'Loading invalid locale: ' . $locale,
                __FILE__,
                __LINE__
            );
        }

        $domain = 'lang' . \vc\config\Globals::VERSION;
        if (!file_exists(APP_LOCALE . '/' . $path . '/LC_MESSAGES/' . $domain . '.mo')) {
            copy(
                APP_LOCALE . '/' . $path . '/LC_MESSAGES/lang.mo',
                APP_LOCALE . '/' . $path . '/LC_MESSAGES/' . $domain . '.mo'
            );
        }
        bindtextdomain($domain, APP_LOCALE);
        textdomain($domain);
    }
}
