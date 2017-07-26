<?php
/**
 * INJI
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2017 Alexey Krupskiy
 * @license https://github.com/injitools/Inji/blob/master/LICENSE
 */

class Template {
    /**
     * Render template with content file
     *
     * @param string $contentPath
     * @param array $contentData
     * @param string $title
     * @param string $templatePath
     */
    public static function render($contentPath, $contentData = [], $title = '', $templatePath = '') {
        if(!$templatePath){
            global $config;
            $templatePath = $config['defaultTemplate'];
        }
        $content = static::renderContent($contentPath, $contentData);
        $template = file_get_contents($templatePath);
        echo preg_replace(['!{CONTENT}!', '!{TITLE}!'], [$content, $title], $template);
    }

    /**
     * Render content file with data
     *
     * @param string $_contentPath
     * @param array $_contentData
     * @return string
     */
    public static function renderContent($_contentPath, $_contentData = []) {
        extract($_contentData);
        ob_start();
        include $_contentPath;
        $_content = ob_get_contents();
        ob_end_clean();
        return $_content;
    }
}