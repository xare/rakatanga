<?php

namespace App\Service;

class slugifyHelper
{
    public function slugify($value)
    {
        // Replace empty spaces by dashes
        // 1. remove possible empty spaces before and after
        $value = trim($value);
        // Convierte una cadena con los caracteres codificados ISO-8859-1 con UTF-8 a un sencillo byte ISO-8859-1
        $value = utf8_decode($value);
        // strtr(string $str, string $from, string $to): string
        $value = strtr($value, utf8_decode('ABCDEFGHIJKLMNOPQRSTUVWXYZŠŽšžŸÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÑÒÓÔÕÖØÙÚÛÜÝàáâãäåçèéêëìíîïñòóôõöøùúûüýÿ’'), "abcdefghijklmnopqrstuvwxyzSZszYAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy'");
        // $value = strtr($value,"ABCDEFGHIJKLMNOPQRSTUVWXYZŠŽšžŸÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÑÒÓÔÕÖØÙÚÛÜÝàáâãäåçèéêëìíîïñòóôõöøùúûüýÿ’","abcdefghijklmnopqrstuvwxyzSZszYAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy'");

        $value = strtr($value, ['Þ' => 'th', 'þ' => 'th', 'Ð' => 'dh', 'ð' => 'dh', 'ß' => 'ss', 'Œ' => 'oe', 'œ' => 'oe', 'Æ' => 'ae', 'æ' => 'ae', 'µ' => 'u', "l'" => '-', " d'" => '-']);
        // /\s+/, busca un espacio o más.

        $value = preg_replace(["/\s+/", '/[^a-z0-9]+/i'], ['-', '-'], $value);
        // $value = preg_replace(array("/\s+/","/[^a-z0-9]+/i"), array("-","-"),$value);
        $value = preg_replace('/-+/', '-', $value);
        $value = trim($value, '-');
        $value = strtolower($value);
        $exceptions = [
            'travesi-a' => 'travesia',
            'vi-deo' => 'video',
        ];
        foreach ($exceptions as $key => $exception) {
            if (str_contains($value, $key)) {
                $value = str_replace($key, $exception, $value);
            }
        }

        return $value;
    }
}
