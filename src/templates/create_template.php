{{-- resources/CrudGenerator/src/templates/create_template.php --}}
       @extends('layouts.app')
       
       @section('content')
           <h1>Create {modelName}</h1>
       
           <form action=\"{{ route('{modelName}.store') }}\" method=\"POST\">
               @csrf
               {fields}
               <button type=\"submit\">Save</button>
           </form>
       @endsection