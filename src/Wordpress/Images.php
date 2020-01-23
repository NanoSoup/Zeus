<?php

namespace Zeus\Wordpress;

/**
 * Class Images
 * @package Zeus\Wordpress
 */
class Images
{
    /**
     * Images constructor.
     */
    public function __construct()
    {
        add_action('after_setup_theme', [$this, 'customImageSizes']);
    }

    /**
     * Define the custom image sizes used throughout the designs
     *
     * Multiply all sizes by 2 to cater for high-res
     */
    public function customImageSizes()
    {
        //$this->addImageSize('banner', 1192 * 2, 558 * 2, true);
    }

    /**
     * @param      $slug
     * @param      $width
     * @param int  $height
     * @param bool $crop
     */
    public function addImageSize($slug, $width, $height = 9999, $crop = false)
    {
        // $base * 2
        add_image_size($slug . '-jumbo', $width * 2, $height * 2, $crop);
        // $base
        add_image_size($slug . '-desktop', $width, $height, $crop);
        // $base / 2
        add_image_size($slug . '-tablet', $width / 2, $height / 2, $crop);
        // $base / 4
        add_image_size($slug . '-mobile', $width / 4, $height / 3, $crop);
    }
}
