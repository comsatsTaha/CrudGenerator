<?php

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
