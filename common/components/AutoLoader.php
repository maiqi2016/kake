<?php

namespace common\components;

use yii\base\Object;

/**
 * AutoLoader comply with PSR-0
 *
 * @author    Leon <jiangxilee@gmail.com>
 * @copyright 2016-08-16 16:15:15
 */
class AutoLoader extends Object
{

    private $directory;

    /**
     * @param string $baseDirectory Base directory where the source files are located.
     */
    public function __construct($baseDirectory = null)
    {
        parent::__construct();
        $this->directory = $baseDirectory ?: dirname(dirname(__DIR__));
    }

    /**
     * Registers the auto loader class with the PHP SPL auto loader.
     *
     * @param bool $prepend Prepend the auto loader on the stack instead of appending it.
     */
    static public function register($prepend = false)
    {
        if (PHP_VERSION_ID < 50300) {
            spl_autoload_register([
                new self(),
                'autoload'
            ]);
        } else {
            spl_autoload_register([
                new self(),
                'autoload'
            ], true, $prepend);
        }
    }

    /**
     * Loads a class from a file using its fully qualified name.
     *
     * @param string $className Fully qualified name of a class.
     */
    public function autoload($className)
    {
        $className = str_replace('\\', DIRECTORY_SEPARATOR, $className);
        $filePath = $this->directory . DIRECTORY_SEPARATOR . $className . '.php';

        if (is_file($filePath)) {
            require($filePath);
        }
    }
}
