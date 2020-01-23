<?php

namespace Zeus\Wordpress;

/**
 * Class TinyMCE
 * @package Zeus\Wordpress
 */
class TinyMCE
{
    /**
     * TinyMCE constructor.
     */
    public function __construct()
    {
        add_filter('tiny_mce_before_init', [__CLASS__, 'registerFormats']);
        add_action('init', [__CLASS__, 'tinymceButtons']);
    }

    /**
     * Creates custom formatting for the WYSIWYG editor e.g. button styling
     *
     * @param $settings
     * @return mixed
     */
    public static function registerFormats($settings)
    {

        $formats = [
            [
                'title' => 'Button',
                'selector' => 'a',
                'classes' => 'button'
            ]
        ];


        $settings['style_formats'] = json_encode($formats);

        return $settings;

    }

    /**
     * Allows you to add in custom buttons to WYSIWYG editor
     */
    public static function tinymceButtons()
    {
        add_filter('mce_buttons', [__CLASS__, 'registerButtons']);
    }

    /**
     * Registers the different buttons to show on the WYSIWYG editor
     *
     * @param $buttons
     * @return mixed
     */
    public static function registerButtons($buttons)
    {
        array_push($buttons, 'styleselect');

        return $buttons;
    }
}