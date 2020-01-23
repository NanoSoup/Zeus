<?php

namespace Zeus;

use Zeus\Wordpress\ACF;
use Zeus\Wordpress\Dashboard;
use Zeus\Wordpress\Duplicate;
use Zeus\Wordpress\GravityForms;
use Zeus\Wordpress\Images;
use Zeus\Wordpress\Manifest;
use Zeus\Wordpress\OptimiseWP;
use Zeus\Wordpress\TinyMCE;
use Zeus\Wordpress\Twig;
use Zeus\Wordpress\UserPermissions;
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
        $this->registerClasses();
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
            new Images(),
            new Manifest(),
            new OptimiseWP(),
            new TinyMCE(),
            new Twig(),
            new UserPermissions(),
            new Yoast()
        ];
    }
}