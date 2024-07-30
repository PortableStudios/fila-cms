<?php

use Portable\FilaCms\Facades\FilaCms;

Route::middleware('web')->group(function () {
    FilaCms::contentRoutes();
    FilaCms::shortUrlRoutes();
    FilaCms::formRoutes();
    FilaCms::ssoRoutes();
    FilaCms::profileRoutes();
});
