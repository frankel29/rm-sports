<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\ImportacionServiceProvider;

return [
    AppServiceProvider::class,
    ImportacionServiceProvider::class,
    AdminPanelProvider::class,
];
