@extends('layouts.app')

@section('content')
    <div class=\"content-header\">
        <div class=\"container-fluid\">
            <div class=\"row mb-2\">
                <div class=\"col-sm-6\">
                    <h1 class=\"m-0\">{{ __('{modelNamePlural}') }}</h1>
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