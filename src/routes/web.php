<?php

use Illuminate\Support\Facades\Route;
use CrudGenerator\Http\Controllers\CrudController;
use CrudGenerator\Http\Controllers\NewRelationshipController;
use CrudGenerator\Http\Controllers\RelationshipController;

Route::get('crud-generator', function () {
    return view('crud-generator::index');
});

Route::post('crud-generator/create', [CrudController::class, 'create'])->name('crud-generator.create');
Route::get('show-relationships', [RelationshipController::class, 'relationshipsIndex'])->name('relationships-generator');
Route::post('relation-generator', [NewRelationshipController::class, 'relationGenerator'])->name('relation-generator.store');

