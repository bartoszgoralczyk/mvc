<?php

//spl_autoload_register('autoload');

class Autoloader
{

    public static function autoload($class)
    {
        $paths = explode(PATH_SEPARATOR, 'c:\wamp\www\mvc');
        $flags = PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE;
        $file = strtolower(str_replace("\\", DIRECTORY_SEPARATOR, trim($class, "\\"))) . ".php";

        foreach ($paths as $path)
        {
            $combined = $path . DIRECTORY_SEPARATOR . $file;
            if (file_exists($combined))
            {
                include ($combined);
                return;
            }
        }
    }

}
spl_autoload_register(array('autoloader', 'autoload'));
