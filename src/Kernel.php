<?php

namespace Zeus;

use Zeus\Wordpress\ACF;
use Zeus\Wordpress\Dashboard;
use Zeus\Wordpress\Duplicate;
use Zeus\GravityForms\GravityForms;
use Zeus\Wordpress\Manifest;
use Zeus\Wordpress\OptimiseWP;
use Zeus\Wordpress\TinyMCE;
use Zeus\Wordpress\Twig;
use Zeus\Wordpress\Yoast;

/**
 * Class Kernel
 */
class Kernel
{
    /**
     * Kernel constructor.
     */
    public function __construct()
    {
        self::registerClasses();
    }

    /**
     * @return array
     */
    public function registerClasses()
    {
        return [
            new GravityForms(),
            new ACF(),
            new Dashboard(),
            new Duplicate(),
            new Manifest(),
            new OptimiseWP(),
            new TinyMCE(),
            new Twig(),
            new Yoast()
        ];
    }
}