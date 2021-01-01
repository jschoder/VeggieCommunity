<?php
namespace vc\config;

class Support
{
    public static function getMessage($language, $default)
    {
        if ($language === 'de') {
            return "Hallo %NICKNAME%,\n" .
                   "\n" .
                   $default . "\n".
                   "\n" .
                   "Liebe Grüße\n" .
                   "Joachim\n" .
                   "[VeggieCommunity.org - Vegetarisch, Vegan, Roh]";
        } else {
            return "Hi %NICKNAME%,\n" .
                   "\n" .
                   $default . "\n".
                   "\n" .
                   "Kind regards\n" .
                   "Joachim\n" .
                   "[VeggieCommunity.org - Vegetarian, Vegan, Raw]";
        }
    }

    public static function getMessageFooter($language)
    {
        if ($language === 'de') {
            return "Joachim Schoder\n" .
                   "Kienestrasse 1\n" .
                   "80933 München\n" .
                   "Deutschland\n" .
                   "\n" .
                   "########################################\n" .
                   "\n" .
                   "Ticket erstellt am %CREATED_AT%:\n";
        } else {
            return "Joachim Schoder\n" .
                   "Kienestrasse 1\n" .
                   "80933 Munich\n" .
                   "Germany\n" .
                   "\n".
                   "########################################\n" .
                   "\n" .
                   "Ticket created at %CREATED_AT%:\n";
        }
    }

    public static function getDefaults($language)
    {
        $defaults = array();
        if ($language === 'de') {
            $defaults['spamProfile'] = array(
                'title' => 'Spam Profile',
                'text' => "danke für den Hinweis. Ich habe das Spamprofil eben gelöscht."
            );
            $defaults['pictureUpload'] = array(
                'title' => 'Picture Upload',
                'text' => "es hat verschiedene Gründe wenn Fotos nicht hochgeladen werden können. Häufig liegt es " .
                          "entweder an viel zu großen Bildern (nicht größer als 5 MB), einer zu langsamen " .
                          "Internetverbindung oder an ungültigen Datenformaten. (Erlaubt sind JPEG, GIF und PNG)." .
                          "\n\nMeistens liegt es daran, dass die Bilder einfach zu groß sind. Du kannst versuchen " .
                          "die Bilder auf maximal " . \vc\config\Globals::MAX_PICTURE_WIDTH . " Pixel Breite und " .
                          \vc\config\Globals::MAX_PICTURE_HEIGHT . " Pixel Höhe zu verkleinern, was in fast " .
                          "jedem Grafikprogramm gehen sollte.\n\nWenn das nicht hilft versuche es bitte einmal mit " .
                          "nur einem Bild auf einmal und schicke uns die Bilder via Email wenn es dann auch immer " .
                          "noch nicht funktioniert. Dann können wir dir genauer sagen wo das Problem liegt."
            );
            $defaults['loginProblems'] = array(
                'title' => 'Login Probleme',
                'text' => "Das Problem ist für uns ohne genaue Informationen immer sehr schwierig zu analyisieren, " .
                          "da wir standardmäßig sehr wenig Daten speichern. Wir können aber für solche Fälle " .
                          "zeitweise mehr Daten speichern, die wir dann nutzen um dir zu helfen und wenn wieder " .
                          "alles für dich funktioniert wieder löschen.\n\n" .
                          "- Bitte aktiviere zunächst den vollen Debugmodus, indem du den folgenden Link öffnest: https://www.veggiecommunity.org/de/debug/on/\n" .
                          "- Fordere ein neues Kennwort über den \"Passwort vergessen\"-Link an: https://www.veggiecommunity.org/de/account/rememberpassword/\n" .
                          "- Du bekommst von uns eine E-Mail mit dem Link über den du dein Passwort neu setzen kannst. Siehe bitte auch im Spamfilter nach ob die E-Mail eventuell dort gelandet ist.\n" .
                          "- Klicke den Link in der E-Mail an, gib dein neues Kennwort ein und klicke auf \"Ändern\"\n" .
                          "- Du solltest zum Loginbildschirm umgeleitet werden, wo du dich mit deinem neuen Kennwort anmelden kannst\n" .
                          "- Falls du dich ohne Probleme anmelden konntest öffne bitte den folgenden Link um den Debugmodus (und damit das speichern von extra Daten) wieder zu deaktivieren: https://www.veggiecommunity.org/de/debug/off/\n" .
                          "- Falls das Problem immer noch auftritt schicke uns bitte eine kurze Nachricht über das Supportformular, damit wir deinen Loginversuch auch korrekt deinen Daten zuordnen können: https://www.veggiecommunity.org/de/help/support/"
            );
        } else {
            $defaults['spamProfile'] = array(
                'title' => 'Spam Profile',
                'text' => "thanks for the tip. I just deleted the spam profile."
            );
            $defaults['pictureUpload'] = array(
                'title' => 'Picture Upload',
                'text' => "there are different reasons that might explain why someone can't upload a picture. Often " .
                          "the picture is too big (no image bigger than 5 MB is allowed), the internet connection " .
                          "isn't fast enough or you might be using a picture in the wrong file format (JPEG, GIF " .
                          "and PNG are allowed).\n\nIn most cases the pictures are simply too bbig. Please rescale " .
                          "your pictures to up to " . \vc\config\Globals::MAX_PICTURE_WIDTH . " pixel width and " .
                          \vc\config\Globals::MAX_PICTURE_HEIGHT . " pixel height, which will fix the problem " .
                          "in almost every case.\n\nPlease try it again one picture at a time and send us the " .
                          "pictures via e-mail that still don't work. In most cases we then can tell you what is " .
                          "the underlying issue. "
            );
        }

        return $defaults;
    }
}
