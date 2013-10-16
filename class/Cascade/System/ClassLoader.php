<?php
/**
 *  ClassLoader.php
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade_System
 */

/**
 *  Cascade_System_ClassLoader
 *
 *  @author   Yoshinobu Kinugasa <kinugasa@gree.co.jp>
 *  @package  Cascade_System
 */
final class Cascade_System_ClassLoader
{
    // ----[ Class Constants ]----------------------------------------
    // location variables index
    const LOCATE_SRC_ROOT      = 0x01;
    const LOCATE_IGNORE_PREFIX = 0x02;
    const LOCATE_FILE_EXT      = 0x03;

    // ----[ Properties ]---------------------------------------------
    /**
     *  Infomation map for source files's location
     *  @var array
     */
    protected static $location_map = array(
        array(
            self::LOCATE_SRC_ROOT      => CASCADE_SRC_ROOT,
            self::LOCATE_IGNORE_PREFIX => '',
            self::LOCATE_FILE_EXT      => '.php',
        ),
    );

    // ----[ Methods ]------------------------------------------------
    // {{{ register
    /**
     *  Register a function with the provided load stack
     *
     *  @param   $dir_src        The directory PATH sotred files
     *  @param   $ignore_prefix  (optional) Ignore the prefix name of class
     *  @param   $file_ext       (optional) THe extenstion of PHP's file
     */
    public static /** void */
        function register(/** string */ $dir_src,
                          /** string */ $ignore_prefix = '',
                          /** string */ $file_ext      = '.php')
    {
        // Checks whether a directory exists.
        if (FALSE === is_dir($dir_src)) {
            $ex_msg = 'Not found directory {path} %s';
            $ex_msg = sprintf($ex_msg, $dir_src);
            throw new Cascade_Exception_SystemException($ex_msg);
        }
        // Checks whether file's location is registered in internal
        foreach (self::$location_map as $location) {
            if (   $location[self::LOCATE_SRC_ROOT]      === $dir_src
                && $location[self::LOCATE_IGNORE_PREFIX] === $ignore_prefix
                && $location[self::LOCATE_FILE_EXT]      === $file_ext) {
                return;
            }
        }
        // register to internal
        self::$location_map[] = array(
            self::LOCATE_SRC_ROOT      => $dir_src,
            self::LOCATE_IGNORE_PREFIX => $ignore_prefix,
            self::LOCATE_FILE_EXT      => $file_ext,
        );
    }
    // }}}
    // {{{ load
    /**
     *  This function is intended to be used as a implementation
     *  for spl provided __autoload stack
     *
     *  @param  $class_name  The class name begin searched
     */
    public static /** boolean */
        function load(/** string */ $class_name)
    {
        // find the file containing class definition
        foreach (self::$location_map as $location) {
            $file_path = self::getClassFilePath($class_name, $location);
            if ($file_path === NULL) {
                continue;
            }
            // load class, or interface definition
            include($file_path);
            // check defined class, or interface
            if (class_exists($class_name, $autoload = FALSE)
                || interface_exists($class_name, $autoload = FALSE)) {
                return TRUE;
            }
        }
        return FALSE;
    }
    // }}}
    // {{{ getClassFilePath
    /**
     *  Gets the file contating class, or interface definition.
     *
     *  @param   $class_name  The class name
     *  @param   $location    The file's location infomation
     *  @return               The file PATH, or FALSE not exists
     */
    protected static /** void */
        function getClassFilePath(/** string */ $class_name,
                                  /** array  */ $location)
    {
        $base_name = $class_name;
        if (0 < strlen($location[self::LOCATE_IGNORE_PREFIX])) {
            $rep_ptn   = sprintf('/^%s/', $location[self::LOCATE_IGNORE_PREFIX]);
            $base_name = preg_replace($rep_ptn, '', $class_name);
        }
        $sep_ns = strpos($base_name, "\\") === FALSE ? '_' : "\\";
        $file_path = $location[self::LOCATE_SRC_ROOT]
            . Cascade::SEPARATOR_DIRECTORY
            . str_replace($sep_ns, Cascade::SEPARATOR_DIRECTORY, $base_name)
            . $location[self::LOCATE_FILE_EXT];
        if (is_file($file_path)) {
            return $file_path;
        }
        return NULL;
    }
    // }}}
};