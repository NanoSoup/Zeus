<?php

namespace NanoSoup\Zeus;

use NanoSoup\Zeus\Wordpress\ACF;
use NanoSoup\Zeus\Wordpress\Dashboard;
use NanoSoup\Zeus\Wordpress\Duplicate;
use NanoSoup\Zeus\GravityForms\GravityForms;
use NanoSoup\Zeus\Wordpress\Manifest;
use NanoSoup\Zeus\Wordpress\OptimiseWP;
use NanoSoup\Zeus\Wordpress\TinyMCE;
use NanoSoup\Zeus\Wordpress\Twig;
use NanoSoup\Zeus\Wordpress\Yoast;

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