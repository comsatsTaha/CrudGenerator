<?php

namespace CrudGenerator\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use ReflectionClass;

class RelationshipController
{
   
    
    public function relationshipsIndex()
    {
        $modelsPath = app_path('Models');
        $models = $this->getAllModels($modelsPath);
        $modelsWithRelationships = [];
    
        foreach ($models as $model) {
            $instance = new $model;
    
            if ($instance instanceof Model) {
                $modelsWithRelationships[] = [
                    'model' => $model,
                    'relationships' => $this->getModelRelationshipNames($instance),
                ];
            }
        }
    
        return view('crud-generator::relationships', [
            'models' => $modelsWithRelationships,
        ]);
    }
    
    /**
     * Recursively fetch all model classes from the given path.
     */
    protected function getAllModels($path)
    {
        $models = [];
        $files = glob($path . '/*.php');
    
        foreach ($files as $file) {
            $namespace = 'App\Models';
            $modelName = basename($file, '.php');
            $models[] = $namespace . '\\' . $modelName;
        }
    
        return $models;
    }
    
    /**
     * Extract relationship method names from the given model instance.
     */
    protected function getModelRelationshipNames($model)
    {
        $reflection = new ReflectionClass($model);
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
    
        $relationshipNames = [];
    
        foreach ($methods as $method) {
            if ($method->class === get_class($model)) {
                $returnType = $method->getReturnType();
    
                if ($returnType && class_exists($returnType->getName())) {
                    $relationshipClasses = [
                        \Illuminate\Database\Eloquent\Relations\HasOne::class,
                        \Illuminate\Database\Eloquent\Relations\HasMany::class,
                        \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
                        \Illuminate\Database\Eloquent\Relations\BelongsToMany::class,
                        \Illuminate\Database\Eloquent\Relations\MorphOne::class,
                        \Illuminate\Database\Eloquent\Relations\MorphMany::class,
                        \Illuminate\Database\Eloquent\Relations\MorphTo::class,
                        \Illuminate\Database\Eloquent\Relations\MorphToMany::class,
                    ];
    
                    if (in_array($returnType->getName(), $relationshipClasses)) {
                        $relationshipNames[] = $method->name;
                    }
                }
            }
        }
    
        return $relationshipNames;
    }
    
}