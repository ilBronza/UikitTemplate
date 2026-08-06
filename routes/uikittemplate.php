<?php

use Illuminate\Support\Facades\Route;
use Laravel\Horizon\Contracts\MasterSupervisorRepository;

Route::middleware(['web', 'auth'])->get('/uikittemplate/broadcast-health', function () {
    abort_unless(config('crud.broadcast.enabled'), 404);

    if (! interface_exists(MasterSupervisorRepository::class)) {
        return response()->json(['status' => 'unavailable']);
    }

    $masters = collect(app(MasterSupervisorRepository::class)->all());

    $status = $masters->isEmpty()
        ? 'inactive'
        : ($masters->every(fn ($master) => $master->status === 'paused') ? 'paused' : 'running');

    return response()->json(['status' => $status]);
})->name('uikittemplate.broadcast-health');
