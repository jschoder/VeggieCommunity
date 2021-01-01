<?php
namespace vc\view\mail;

class View
{
    public function render($template, $locale = null, $params = array())
    {
        foreach ($params as $key => $param) {
            $this->$key = $param;
        }

        if ($locale === null) {
            $templateFile = TEMPLATE_DIR . '/mail/'  . $template . '.tpl.php';
        } else {
            $templateFile = TEMPLATE_DIR . '/mail/'  . $template . '_' . $locale . '.tpl.php';
        }
        if (file_exists($templateFile)) {
            return $this->renderTemplate($templateFile);
        } else {
            \vc\lib\ErrorHandler::warning(
                "Email-Template-file can't be found: " . $template . ' / ' . $locale,
                __FILE__,
                __LINE__
            );
            throw new \vc\exception\NotFoundException(
                "Email-Template-file can't be found: " . $template . ' / ' . $locale
            );
        }
    }

    protected function renderTemplate($templateFile)
    {
        ob_start();
        require($templateFile);
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }

    protected function prepareHtml($text) {
        return htmlspecialchars($text);
    }
}
