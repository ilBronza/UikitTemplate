<?php

use IlBronza\UikitTemplate\Http\Controllers\Frontend\ChangeMenuLocaleController;
use Illuminate\Support\Facades\Route;

Route::post('/uikittemplate/frontend/menu-locale', ChangeMenuLocaleController::class)
    ->middleware('web')
    ->name('uikittemplate.frontend.menu-locale');
