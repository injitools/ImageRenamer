<?php
/**
 * INJI
 *
 * @author Alexey Krupskiy <admin@inji.ru>
 * @link http://inji.ru/
 * @copyright 2017 Alexey Krupskiy
 */

/**
 * Filesystem work helper
 */
class FS {
    /**
     * Remove directory recursively
     *
     * @param string $dir Directory path
     */
    public static function removeDir($dir) {
        foreach (array_slice(scandir($dir), 2) as $path) {
            if (is_dir($dir . DIRECTORY_SEPARATOR . $path)) {
                static::removeDir($dir . DIRECTORY_SEPARATOR . $path);
            } else {
                unlink($dir . DIRECTORY_SEPARATOR . $path);
            }

        }
        rmdir($dir);
    }

    /**
     * Start downloading file in browser
     *
     * @param string $filePath
     * @param string $type
     * @param string $name
     */
    public static function forDownload($filePath, $type, $name) {
        header("Cache-control: public");
        header("Accept-Ranges: bytes");
        header("Pragma: public");
        header("Content-Length: " . filesize($filePath));
        header('Content-Description: File Transfer');
        header("Content-Type: {$type}");
        header('Content-Disposition: attachment; filename="' . $name . '"');
        header('Content-Transfer-Encoding: binary');

        readfile($filePath);
    }

    /**
     * Make zip from directory or file
     *
     * @param string $dir
     * @param string $zipPath
     * @return bool
     */
    public static function zipDir($dir, $zipPath) {
        $zip = new ZipArchive();
        if (!$zip->open($zipPath, ZipArchive::CREATE)) {
            return false;
        }
        foreach (scandir($dir) as $item) {
            if (in_array($item, ['.', '..'])) {
                continue;
            }
            static::addToZip($dir, $item, $zip);
        }
        $zip->close();
        return true;
    }

    /**
     * Add file or directory to zip archive
     *
     * Recursively add directories and files to isset zip archive
     *
     * @param string $dir
     * @param string $name
     * @param ZipArchive $zip
     */
    public static function addToZip($dir, $name, $zip) {
        if (is_dir($dir . '/' . $name)) {
            $zip->addEmptyDir($name);
            foreach (scandir($dir . '/' . $name) as $item) {
                if (in_array($item, ['.', '..'])) {
                    continue;
                }
                static::addToZip($dir, $name . '/' . $item, $zip);
            }
        } else {
            $zip->addFile($dir . '/' . $name, $name);
        }
    }

    /**
     * Safely for cyrillic, extract zip to directory
     *
     * @param string $zipFile
     * @param string $extractDir
     * @return bool
     */
    public static function safeUnzip($zipFile, $extractDir) {
        $cp437map = include 'Misc/from.cp437.php';
        $zip = new ZipArchive;
        if ($zip->open($zipFile) !== TRUE) {
            return false;
        }
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $file = $zip->getNameIndex($i);
            $cp866 = str_replace(array_values($cp437map), array_keys($cp437map), $file);
            if (strrpos($cp866, '/') == strlen($cp866) - 1) {
                mkdir($extractDir . '/' . $cp866);
            } else {
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    $filename = iconv('cp866', 'cp1251', $cp866);
                } else {
                    $filename = iconv('cp866', 'utf-8', $cp866);
                }
                copy("zip://" . $zipFile . "#" . $file, $extractDir . '/' . $filename);
            }

        }
        $zip->close();
        return true;
    }

    /**
     * Create temporary dir
     * @return string
     */
    public static function tempDir() {
        $tmpDir = 'tmp' . DIRECTORY_SEPARATOR . microtime(true);
        mkdir($tmpDir);
        return $tmpDir;
    }
}