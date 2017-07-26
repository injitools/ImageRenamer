<?php
/**
 * INJI
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2017 Alexey Krupskiy
 */

class ImageRenamer {
    private $renamed = [];
    private $htmls = [];
    private $exts = ['.jpg', '.jpeg', '.png', '.bmp', '.gif'];

    public function run() {
        if (empty($_FILES['zipfile']['tmp_name'])) {
            Template::render(__DIR__ . '/content/form.php', [], 'Image Renamer');
        } else {
            $this->processZip($_FILES['zipfile']['tmp_name']);
        }
    }

    private function processZip($path) {
        $tmpDir = FS::tempDir();
        if (!FS::safeUnzip($path, $tmpDir)) {
            exit('Extract error');
        }
        $this->scanFiles($tmpDir);
        $this->replacePaths();
        $newZip = "tmp/zip" . microtime(true) . '.zip';
        FS::zipDir($tmpDir, $newZip);
        FS::forDownload($newZip, 'application/zip', 'converted.zip');
        unlink($newZip);
        FS::removeDir($tmpDir);
    }

    private function scanFiles($path) {
        foreach (array_slice(scandir($path), 2) as $item) {
            if (is_dir($path . DIRECTORY_SEPARATOR . $item)) {
                $this->scanFiles($path . DIRECTORY_SEPARATOR . $item);
            } else {
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    $utf8filename = iconv('cp1251', 'utf-8', $item);
                } else {
                    $utf8filename = $item;
                }
                if (preg_match('!(' . implode('|', $this->exts) . ')!i', $utf8filename)) {
                    $translited = Text::translit($utf8filename);
                    if ($utf8filename !== $translited) {
                        if (file_exists($path . '/' . $translited)) {
                            $k = 1;
                            do {
                                $newName = substr_replace($translited, '_' . $k, strrpos($translited, '.'), 0);
                                $k++;
                            } while (file_exists($path . '/' . $newName));
                            $translited = $newName;
                        }
                        rename($path . DIRECTORY_SEPARATOR . $item, $path . '/' . $translited);
                        $this->renamed['!' . $utf8filename . '!'] = $translited;
                    }
                } elseif (strpos($utf8filename, '.html') !== false) {
                    $this->htmls[] = $path . DIRECTORY_SEPARATOR . $utf8filename;
                }
            }
        }
    }

    private function replacePaths() {
        foreach ($this->htmls as $html) {
            $source = file_get_contents($html);
            file_put_contents($html, preg_replace(array_keys($this->renamed), array_values($this->renamed), $source));

        }
    }
}