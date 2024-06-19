<?php

namespace Portable\FilaCms\Socialite;

use Laravel\Socialite\Two\LinkedInProvider;

class FilaCmsLinkedInProvider extends LinkedInProvider
{
    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected $scopes = ['email'];
}
