<?php
/**
 * Created by JetBrains PhpStorm.
 * User: marcusamatos
 * Date: 18/09/13
 * Time: 10:25
 * To change this template use File | Settings | File Templates.
 */

class JvsRequire {

    public static $autoload = array();

    public static function load($componentName)
    {
        if(!in_array($componentName, self::$autoload))
        {
            self::$autoload[] = $componentName;
        }
    }
    public static function getAutoLoad()
    {
        return self::$autoload;
    }
}