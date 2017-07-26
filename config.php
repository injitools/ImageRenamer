<?php
/**
 * INJI
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2017 Alexey Krupskiy
 */
return [
    'defaultUtility' => 'ImageRenamer',
    'utilities' => [
        'ImageRenamer' => 'Utilities/ImageRenamer/ImageRenamer.php'
    ],
    'classMap' => [
        'FS' => 'Tools/FS.php',
        'Text' => 'Tools/Text.php',
        'Template' => 'Tools/Template.php'
    ],
    'defaultTemplate' => 'Templates/main.html'
];