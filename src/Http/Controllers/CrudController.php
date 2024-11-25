<?php

namespace CrudGenerator\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CrudController
{
    public function create(Request $request)
    {
        $model = $request->input('model');
        $fieldNames = $request->input('field_names');
        $fieldTypes = $request->input('field_types');

        // Combine field names and types into one array
        $fields = [];
        $validationRules = [];
        foreach ($fieldNames as $key => $name) {
            $fields[] = [
                'name' => $name,
                'type' => $fieldTypes[$key]
            ];
            $validationRules[$name] = $this->getValidationRule($fieldTypes[$key]);
        }

        $this->generateModel($model);
        $this->generateMigration($model, $fields);
        $this->generateController($model, $validationRules);
        $this->generateViews($model, $fields);
        $this->generateRoutes($model);

        Artisan::call('migrate');

        return back()->with('success', "Model, Migration, Controller, and Views for {$model} created successfully!");
    }

    /**
     * Get validation rule based on the field type.
     */
    protected function getValidationRule($type)
    {
        switch ($type) {
            case 'string':
                return 'required|string';
            case 'integer':
                return 'required|integer';
            case 'text':
                return 'required|string';
            case 'boolean':
                return 'required|boolean';
            case 'date':
                return 'required|date';
            // Add more types as needed
            default:
                return 'required';
        }
    }

    /**
     * Generate the model file.
     */
    protected function generateModel($model)
    {
        $modelTemplate = file_get_contents(resource_path('../CrudGenerator/src/templates/model_template.php'));
        $modelContent = str_replace('{modelName}', $model, $modelTemplate);

        $modelPath = app_path("Models/{$model}.php");
        File::put($modelPath, $modelContent);
    }

    /**
     * Generate the migration file.
     */
    protected function generateMigration($model, $fields)
    {
        // Generate the migration file name
        $migrationName = 'create_' . strtolower($model) . '_table';
        $migrationPath = database_path("migrations/" . date('Y_m_d_His') . "_{$migrationName}.php");

        // Migration template structure
        $migrationTemplate = '<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Create{modelName}Table extends Migration
{
    public function up()
    {
        Schema::create("{tableName}", function (Blueprint $table) {
            $table->id();';

        // Add dynamic fields
        foreach ($fields as $field) {
            $migrationTemplate .= "\n            \$table->{$field['type']}('{$field['name']}');";
        }

        $migrationTemplate .= '
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists("{tableName}");
    }
}
';

        // Replace {modelName} and {tableName} placeholders
        $migrationContent = str_replace(
            ['{modelName}', '{tableName}'],
            [ucwords($model), strtolower($model) . 's'],
            $migrationTemplate
        );

        // Write the migration to the file
        File::put($migrationPath, $migrationContent);
    }

    /**
     * Generate the controller file.
     */
    protected function generateController($model, $validationRules)
    {
        $controllerTemplate = file_get_contents(resource_path('../CrudGenerator/src/templates/controller_template.php'));
        $controllerContent = str_replace('{modelName}', $model, $controllerTemplate);

        // Insert validation rules dynamically
        $validationCode = '';
        foreach ($validationRules as $field => $rule) {
            $validationCode .= "'{$field}' => '{$rule}',\n";
        }
        $controllerContent = str_replace('{validationRules}', $validationCode, $controllerContent);

        // Set dynamic variable names
        $controllerContent = str_replace('{variableName}', strtolower($model), $controllerContent);
        $pluralModelName = Str::plural(strtolower($model));
        $controllerContent = str_replace('{viewPath}', $pluralModelName . '.index', $controllerContent);
        $controllerContent = str_replace('{tableName}', $pluralModelName, $controllerContent);
        $controllerContent = str_replace('{createViewPath}', $pluralModelName . '.create', $controllerContent);
        $controllerContent = str_replace('{editViewPath}', $pluralModelName . '.edit', $controllerContent);
        $controllerContent = str_replace('{routeName}', $pluralModelName, $controllerContent);

        // Write controller to the file
        $controllerPath = app_path("Http/Controllers/{$model}Controller.php");
        File::put($controllerPath, $controllerContent);
    }

    /**
     * Generate the CRUD views (index, create, edit).
     */
   // Adjust the generateViews method
   protected function generateViews($model, $fields)
   {
       // Path to store views
       $viewsPath = resource_path("views/" . Str::plural(strtolower($model)));
   
       // Ensure the directory exists
       if (!File::exists($viewsPath)) {
           File::makeDirectory($viewsPath, 0755, true);
       }
   
       $pluralModelName = Str::plural(strtolower($model));
    //    dd($pluralModelName);
    //    dd($pluralModelName);
       // Generate index view
    //    $indexTemplate = file_get_contents(resource_path('../CrudGenerator/src/templates/index_template.php'));
    $indexTemplate = "
    @extends('layouts.app')

@section('content')
    <div class=\"content-header\">
        <div class=\"container-fluid\">
            <div class=\"row mb-2\">
                <div class=\"col-sm-6\">
                    <h1 class=\"m-0\">{{ __('{\$modelName}') }}</h1>
                </div>
                <div class=\"col-sm-6 text-right\">
                    <a href=\"{{ route('{modelName}.create') }}\" class=\"btn btn-primary\">Create New</a>
                </div>
            </div>
        </div>
    </div>
    <div class=\"content\">
        <div class=\"container-fluid\">
            <div class=\"row\">
                <div class=\"col-lg-12\">
                    <div class=\"card\">
                        <div class=\"card-body p-0\">
                            <table class=\"table\">
                                <thead>
                                    <tr>
                                        @foreach(\$fields as \$field)
                                            <th>{{ ucfirst(\$field) }}</th>
                                        @endforeach
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(\$records as \$record)
                                        <tr>
                                            @foreach(\$fields as \$field)
                                                <td>{{ \$record->{\$field} }}</td>
                                            @endforeach
                                            <td>
                                                <a href=\"{{ route('{modelName}.edit', \$record->id) }}\" class=\"btn btn-sm btn-warning\">Edit</a>
                                                <form action=\"{{ route('{modelName}.destroy', \$record->id) }}\" method=\"POST\" style=\"display:inline;\">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type=\"submit\" class=\"btn btn-sm btn-danger\">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
";

    
    




       $indexContent = str_replace(['{modelName}', '{fields}'], [$pluralModelName, $this->generateFieldsForIndex($fields)], $indexTemplate);
    
    //    dd($indexContent);
       File::put("{$viewsPath}/index.blade.php", $indexContent);
   
       // Generate create view
       $createTemplate1 = file_get_contents(resource_path('../CrudGenerator/src/templates/create_template.php'));













       $createTemplate = <<<HTML
       @extends('layouts.app')
       
       @section('content')
           <div class="content">
               <div class="container-fluid">
                   <div class="row">
                       <div class="col-lg-12">
                           <div class="card">
                               <form action="{{ route('{modelName}.store') }}" method="POST">
                                   @csrf
       
                                   <div class="card-body">
                                           {fields}
                                   </div>
       
                                   <div class="card-footer">
                                       <button type="submit" class="btn btn-primary">Submit</button>
                                   </div>
                               </form>
                           </div>
                       </div>
                   </div>
               </div>
           </div>
       @endsection
       HTML;
       
      
       $createContent = str_replace(['{modelName}', '{fields}'], [$pluralModelName, $this->generateFieldsForView($fields)], $createTemplate);
    //    dd($createContent);

       File::put("{$viewsPath}/create.blade.php", $createContent);
   
       // Generate edit view
    //    $editTemplate = file_get_contents(resource_path('../CrudGenerator/src/templates/edit_template.php'));
    $editTemplate = "
    {{-- resources/CrudGenerator/src/templates/edit_template.php --}}
    @extends('layouts.app')
    
    @section('content')
        <h1>Edit {{ ucfirst(\$modelName) }}</h1>
    
        <form action=\"{{ route('\$modelName.update', \$model->id) }}\" method=\"POST\">
            @csrf
            @method('PUT')
            {!! \$fields !!}
            <button type=\"submit\">Update</button>
        </form>
    @endsection
    ";
    
       $editContent = str_replace(['{modelName}', '{fields}'], [$pluralModelName, $this->generateFieldsForView($fields)], $editTemplate);
       File::put("{$viewsPath}/edit.blade.php", $editContent);
   }
   


    /**
     * Generate fields dynamically for the CRUD views.
     */
    protected function generateFieldsForView($fields, $columns = 2)
    {
        $fieldsHtml = '';
        $fieldsPerRow = $columns; // Number of fields per row
        $fieldCount = 0;

        // Start the first row
        $fieldsHtml .= "<div class='row'>";

        // Loop through each field to generate input
        foreach ($fields as $field) {
            // Add the column wrapper
            $fieldsHtml .= "<div class='col-md-6'>
                                <div class='form-group'>
                                    <label for='{$field['name']}'>" . ucfirst($field['name']) . "</label>";

            // Generate form fields based on the field type
            switch ($field['type']) {
                case 'string':
                case 'text':
                    $fieldsHtml .= "<input type='text' class='form-control' id='{$field['name']}' name='{$field['name']}'>";
                    break;
                case 'boolean':
                    $fieldsHtml .= "<input type='checkbox' class='form-check-input' id='{$field['name']}' name='{$field['name']}'>";
                    break;
                case 'integer':
                    $fieldsHtml .= "<input type='number' class='form-control' id='{$field['name']}' name='{$field['name']}'>";
                    break;
                case 'float':
                    $fieldsHtml .= "<input type='number' step='any' class='form-control' id='{$field['name']}' name='{$field['name']}'>";
                    break;
                case 'decimal':
                    $fieldsHtml .= "<input type='number' step='any' class='form-control' id='{$field['name']}' name='{$field['name']}'>";
                    break;
                case 'date':
                    $fieldsHtml .= "<input type='date' class='form-control' id='{$field['name']}' name='{$field['name']}'>";
                    break;
                case 'time':
                    $fieldsHtml .= "<input type='time' class='form-control' id='{$field['name']}' name='{$field['name']}'>";
                    break;
                case 'timestamp':
                case 'datetime':
                    $fieldsHtml .= "<input type='datetime-local' class='form-control' id='{$field['name']}' name='{$field['name']}'>";
                    break;
                case 'json':
                case 'jsonb':
                    $fieldsHtml .= "<textarea class='form-control' id='{$field['name']}' name='{$field['name']}'></textarea>";
                    break;
                case 'binary':
                    $fieldsHtml .= "<input type='file' class='form-control' id='{$field['name']}' name='{$field['name']}'>";
                    break;
                case 'enum':
                    $fieldsHtml .= "<select class='form-control' id='{$field['name']}' name='{$field['name']}'>
                                        <option value=''>Select {$field['name']}</option>
                                        <!-- Add dynamic options here -->
                                    </select>";
                    break;
                default:
                    // Default case for unknown types, treated as a text field
                    $fieldsHtml .= "<input type='text' class='form-control' id='{$field['name']}' name='{$field['name']}'>";
                    break;
            }

            // Close the column
            $fieldsHtml .= "</div></div>";

            // Increment the field counter
            $fieldCount++;

            // If the row is complete, close it and start a new row
            if ($fieldCount % $fieldsPerRow === 0) {
                $fieldsHtml .= "</div><div class='row'>";
            }
        }

        // Close the last row if necessary
        if ($fieldCount % $fieldsPerRow !== 0) {
            $fieldsHtml .= "</div>";
        }

        return $fieldsHtml;
    }

    



    protected function generateFieldsForIndex($fields)
    {
        $fieldsHtml = '';
        foreach ($fields as $field) {
            $fieldsHtml .= "<th>" . ucfirst($field['name']) . "</th>";
        }
        $fieldsHtml .= "<th>Actions</th>";
        return $fieldsHtml;
    }


    
    protected function generateRoutes($model)
        {
            $pluralModelName = Str::plural(strtolower($model));
            $routesContent = "Route::resource('{$pluralModelName}', \\App\\Http\\Controllers\\{$model}Controller::class);";
            $routesPath = base_path('routes/web.php');
            File::append($routesPath, $routesContent);
        }
   
    
}
