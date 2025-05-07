<?php
/**
 * Basic object, which objects in WordPress extend.
 *
 * This is a simplified version to fix the missing class-basic-object.php error
 * in the WordPress test suite.
 *
 * @package Simple_WP_Optimizer
 */

/**
 * Basic_Object is a simple class that provides basic functionality.
 */
class Basic_Object {
    /**
     * Retrieve a value from an array with support for a default value.
     *
     * @param array  $args    Arguments.
     * @param string $key     Key to retrieve.
     * @param mixed  $default Default value.
     * @return mixed Value if set, default if not.
     */
    protected function get_from_array( $args, $key, $default = null ) {
        if ( isset( $args[ $key ] ) ) {
            return $args[ $key ];
        }
        return $default;
    }
}
