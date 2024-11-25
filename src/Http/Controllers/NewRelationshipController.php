<?php

namespace CrudGenerator\Http\Controllers;

use Exception;
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


class NewRelationshipController
{
    public function relationGenerator(Request $request)
    {
        // Retrieve form data
        $baseModels = $request->input('base_model'); 
        $relationshipTypes = $request->input('relationship_type'); 
        $relatedModels = $request->input('related_model');
        $foreignKeys = $request->input('foreign_key', []);
        $pivotTables = $request->input('pivot_table', []); 
        $morphNames = $request->input('morph_name', []); 


      
        foreach ($baseModels as $index => $baseModel) {
            $relationshipType = $relationshipTypes[$index];
            $relatedModel = $relatedModels[$index];
            $foreignKey = $foreignKeys[$index] ?? null;
            $pivotTable = $pivotTables[$index] ?? null;
            $morphName = $morphNames[$index] ?? null;

            // Dynamically get the model classes
            $baseModelClass = "App\\Models\\" . $baseModel;
            $relatedModelClass = "App\\Models\\" . $relatedModel;

            // Check if the models exist
            if (!class_exists($baseModelClass) || !class_exists($relatedModelClass)) {
                return response()->json(['error' => 'Invalid models'], 400);
            }

            
            $this->addRelationshipToModel($baseModelClass, $relatedModelClass, $relationshipType, $foreignKey, $pivotTable, $morphName);
        }

        // Return success response
        return response()->json([
            'message' => 'Successfully created relationships and updated models.',
        ]);
    }

    // Function to add relationship methods to the models
    private function addRelationshipToModel($baseModelClass, $relatedModelClass, $relationshipType, $foreignKey, $pivotTable, $morphName)
    {
        // dd($baseModelClass, $relatedModelClass, $relationshipType, $foreignKey, $pivotTable, $morphName);
        // Get the model file path
        $baseModelPath = app_path('Models/' . class_basename($baseModelClass) . '.php');
        // dd($baseModelPath);
        // Check if the model file exists
        if (!File::exists($baseModelPath)) {
           
            return response()->json(['error' => 'Base model file does not exist.'], 400);
        }

     
        $modelContent = File::get($baseModelPath);
        $relationshipMethod = $this->generateRelationshipMethod($baseModelClass, $relatedModelClass, $relationshipType, $foreignKey, $pivotTable, $morphName);
   

        $modelContent = $this->insertMethodIntoModel($modelContent, $relationshipMethod);
        // dd($relationshipMethod);
        // Write the updated content back to the model file
        File::put($baseModelPath, $modelContent);
    }

    // Function to generate the relationship method based on type
    private function generateRelationshipMethod($baseModelClass, $relatedModelClass, $relationshipType, $foreignKey, $pivotTable, $morphName)
    {
        $baseModel = class_basename($baseModelClass);
        $relatedModel = class_basename($relatedModelClass);
        $relationshipCode = '';

        switch ($relationshipType) {
            case 'HasMany':
                $relationshipCode = "public function " . strtolower($relatedModel) . "s()\n{\n\treturn \$this->hasMany($relatedModel::class, '$foreignKey');\n}\n";
                break;

            case 'HasOne':
                $relationshipCode = "public function " . strtolower($relatedModel) . "()\n{\n\treturn \$this->hasOne($relatedModel::class, '$foreignKey');\n}\n";
                break;

            case 'BelongsTo':
                $relationshipCode = "public function " . strtolower($relatedModel) . "()\n{\n\treturn \$this->belongsTo($relatedModel::class, '$foreignKey');\n}\n";
                break;

            case 'BelongsToMany':
                $relationshipCode = "public function " . strtolower($relatedModel) . "s()\n{\n\treturn \$this->belongsToMany($relatedModel::class, '$pivotTable');\n}\n";
                break;

            case 'MorphOne':
                $relationshipCode = "public function " . strtolower($relatedModel) . "()\n{\n\treturn \$this->morphOne($relatedModel::class, '$morphName');\n}\n";
                break;

            case 'MorphMany':
                $relationshipCode = "public function " . strtolower($relatedModel) . "s()\n{\n\treturn \$this->morphMany($relatedModel::class, '$morphName');\n}\n";
                break;

            case 'MorphTo':
                $relationshipCode = "public function " . strtolower($relatedModel) . "()\n{\n\treturn \$this->morphTo();\n}\n";
                break;

            case 'MorphToMany':
                $relationshipCode = "public function " . strtolower($relatedModel) . "s()\n{\n\treturn \$this->morphToMany($relatedModel::class, '$morphName');\n}\n";
                break;

            default:
                throw new \Exception("Invalid relationship type");
        }

        return $relationshipCode;
    }

    // Function to insert the generated method into the model file
    private function insertMethodIntoModel($modelContent, $relationshipMethod)
    {
        if (preg_match('/class\s+(\w+)/', $modelContent, $matches)) {
            $className = $matches[1]; // Extracted class name
            $methodPosition = strpos($modelContent, 'class ' . $className);
    
            if ($methodPosition !== false) {
                // Find the position of the opening curly brace of the class
                $classOpeningBrace = strpos($modelContent, '{', $methodPosition);
    
                if ($classOpeningBrace !== false) {
                    // Format the relationship method
                    $formattedMethod = "\n\n    " . trim($relationshipMethod) . "\n";
    
                    // Insert the relationship method after the opening curly brace
                    $modelContent = substr_replace($modelContent, $formattedMethod, $classOpeningBrace + 1, 0);
    
                }
            }
    
            return $modelContent;
        }
    
        throw new Exception('Class declaration not found in the model content.');
    }
}