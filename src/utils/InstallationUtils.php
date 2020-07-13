<?php

namespace Ypsolution\YnfinitePhpClient\utils;

class InstallationUtils {
    public static function replaceValue($key, $value, $string)
    {
        $searchString = '/define\(\'' . $key . '\',\s*\'(.)*\'\);/';
        $replacement = 'define(\''.$key.'\', \'' . $value . '\');';
        return preg_replace($searchString, $replacement, $string);
    }       

   public static function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}