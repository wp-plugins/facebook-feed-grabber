<?php 
/**
 * @package Facebook_Feed_Grabber
 * @subpackage MVC View
 * @since 0.9.0
 */

/**
 * 
 */
class MVCview {

    /**
     * The template directory.
     * 
     * @var string Template directory.
     * @since 0.9.0
     */
    public static $template_dir = 'templates/';


    /**
     * Render a Template.
     * 
     * @param $file - Template file name.
     * @param null $viewData - any data to be used within the template.
     * @return string - 
     */
    public static function render( $file, $viewData = null ) {

        // Was any data sent through?
        ( $viewData ) ? extract( $viewData ) : null;
 
        ob_start();
        include ( self::$template_dir . $file );
        $template = ob_get_contents();
        ob_end_clean();
 
        return $template;
    }
}

?>