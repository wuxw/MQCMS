<?php
/**
 * 助手函数
 */

/**
 * 随机字符串
 */
if (!function_exists('generateRandomString')) {
    function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }
}

/**
 * Generate random decimals
 */
if (!function_exists('randFloat')) {
    function randFloat($min = 0, $max = 1) {
        return $min + mt_rand() / mt_getrandmax() * ($max - $min);
    }
}

/**
 * 调用文件夹所有的php文件
 */
if (!function_exists('requireDirScript')) {
    function requireDirScript($dir, $filename='') {
        if (is_dir($dir)) {
            $handler = opendir($dir);
            //遍历脚本文件夹下的所有文件
            while (false !== ($file = readdir($handler))) {
                if ($file != "." && $file != "..") {
                    $fullpath = $dir . "/" . $file;
                    if (!is_dir($fullpath) && substr($file,-4) == '.php') {
                        if ($filename !== '' && basename($fullpath, '.php') === $filename) {
                            require_once($fullpath);
                        } else {
                            require_once($fullpath);
                        }
                    } else {
                        requireDirScript($fullpath);
                    }
                }
            }
            //关闭文件夹
            closedir($handler);
        }
    }
}

/**
 * copy
 */
if (!function_exists('recurseCopy')) {
    function recurseCopy($src, $dst) {
        $dir = opendir($src);
        @mkdir($dst);
        while(false !== ($file = readdir($dir))) {
            if ($file != '.' && $file != '..') {
                if (is_dir($src . '/' . $file)) {
                    recurseCopy($src . '/' . $file,$dst . '/' . $file);
                }
                else {
                    copy($src . '/' . $file,$dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }
}