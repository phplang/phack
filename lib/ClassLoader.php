<?php

namespace PhpLang\Phack;

class ClassLoader extends \Composer\Autoload\ClassLoader {
    private static $instance;

    /**
     * SPL Autoload callback
     */
    public function loadClass($class) {
        if ($filename = $this->findFile($class)) {
            $fp = fopen($filename, 'r');
            if (!$fp) { return false; }
            $line = fgets($fp);
            if (!strncmp($line, '#!', 2)) { $line = fgets($fp); }
            fclose($fp);
            if (!strncmp($line, '<?hh', 4)) {
              // HackLang file
              \PhpLang\Phack\includeFile($filename);
            } else {
              // Normal PHP file
              \Composer\Autoload\includeFile($filename);
            }
            return true;
        }
        return false;
    }

    public static function hijack() {
        if (self::$instance) { return; }

        if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
          $old = require __DIR__ . '/../vendor/autoload.php';
        } elseif (file_exists(__DIR__ . '/../../../autoload.php')) {
          $old = require __DIR__ . '/../../../vendor/autoload.php';
        } else {
          throw new \RuntimeException("Can't find composer root, unable to hijack autoloader");
        }

        $old->unregister();
        $serialized = serialize($old);
        $serialized = 'O:25:"PhpLang\\Phack\\ClassLoader":' . substr($serialized, 37);
        (self::$instance = unserialize($serialized))->register();
    }
}
