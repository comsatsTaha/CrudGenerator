{{-- resources/CrudGenerator/src/templates/edit_template.php --}}
@extends('layouts.app')

@section('content')
    <h1>Edit {{ ucfirst($modelName) }}</h1>

    <form action="{{ route("{$modelName}.update", $model->id) }}" method="POST">
        @csrf
        @method('PUT')
        {!! $fields !!}
        <button type="submit">Update</button>
    </form>
@endsection
