@extends('layouts.app')

@section('content')


    <h1>CRUD Generator</h1>

    <form action="{{ route('crud-generator.create') }}" method="POST">
        @csrf
    <div class="card-body">
        <div class="input-group mb-3">
            <input type="text" name="model" class="form-control is-invalid " placeholder="Model Name"  required>
        </div>
        <div id="fields-container">
            <!-- Dynamic Fields will be added here -->
            <div class="row">
                <div class="col-5">
                    <input type="text" name="field_names[]" placeholder="Field Name" class="form-control" required>
                </div>
                <div class="col-5">
                    <select name="field_types[]" class="form-control" required>
                        <option value="foreignId">foreignId</option>
                        <option value="string">String</option>
                        <option value="text">Text</option>
                        <option value="integer">Integer</option>
                        <option value="float">Float</option>
                        <option value="decimal">Decimal</option>
                        <option value="boolean">Boolean</option>
                        <option value="date">Date</option>
                        <option value="time">Time</option>
                        <option value="timestamp">Timestamp</option>
                        <option value="datetime">Datetime</option>
                        <option value="json">JSON</option>
                        <option value="jsonb">JSONB</option>
                        <option value="binary">Binary</option>
                        <option value="enum">Enum</option>
                    </select>
                </div>
                <div class="form-group col-2">
                    <button type="button" id="add-field-btn" class="btn btn-success btn-sm">+</button>
                    <button type="button" id="remove-field-btn" class="btn btn-danger btn-sm">-</button>
                </div>
            </div>
        </div>
        
        
        
        <button type="submit" class="btn btn-primary">Generate Model and Migration</button>
        

        

        

    </div>

    </form>


<script>

document.getElementById('add-field-btn').addEventListener('click', function() {
    // Create a new field row
    const fieldRow = document.createElement('div');
    fieldRow.classList.add('row');

    // Create the field name input element
    const fieldNameInput = document.createElement('input');
    fieldNameInput.type = 'text';
    fieldNameInput.name = 'field_names[]';
    fieldNameInput.placeholder = 'Field Name';
    fieldNameInput.classList.add('form-control');
    fieldNameInput.required = true;

    // Create the field type select element
    const fieldTypeSelect = document.createElement('select');
    fieldTypeSelect.name = 'field_types[]';
    fieldTypeSelect.classList.add('form-control');
    fieldTypeSelect.required = true;

    // Add the field type options
    const fieldTypes = [
        { value: 'string', text: 'String' },
        { value: 'text', text: 'Text' },
        { value: 'integer', text: 'Integer' },
        { value: 'float', text: 'Float' },
        { value: 'decimal', text: 'Decimal' },
        { value: 'boolean', text: 'Boolean' },
        { value: 'date', text: 'Date' },
        { value: 'time', text: 'Time' },
        { value: 'timestamp', text: 'Timestamp' },
        { value: 'datetime', text: 'Datetime' },
        { value: 'json', text: 'JSON' },
        { value: 'jsonb', text: 'JSONB' },
        { value: 'binary', text: 'Binary' },
        { value: 'enum', text: 'Enum' }
    ];

    // Loop through the field types and create option elements
    fieldTypes.forEach(function(type) {
        const option = document.createElement('option');
        option.value = type.value;
        option.textContent = type.text;
        fieldTypeSelect.appendChild(option);
    });

    // Create a col-4 div for each input and select element
    const fieldNameCol = document.createElement('div');
    fieldNameCol.classList.add('col-5');
    fieldNameCol.appendChild(fieldNameInput);

    const fieldTypeCol = document.createElement('div');
    fieldTypeCol.classList.add('col-5');
    fieldTypeCol.appendChild(fieldTypeSelect);

    // Create the buttons column
    const buttonsCol = document.createElement('div');
    buttonsCol.classList.add('col-2', 'd-flex', 'align-items-center');

    const addButton = document.createElement('button');
    addButton.type = 'button';
    addButton.classList.add('btn', 'btn-success', 'btn-sm', 'mr-2');
    addButton.textContent = '+';
    addButton.addEventListener('click', function() {
        // Recursively add more fields if needed
        document.getElementById('add-field-btn').click();
    });

    const removeButton = document.createElement('button');
    removeButton.type = 'button';
    removeButton.classList.add('btn', 'btn-danger', 'btn-sm');
    removeButton.textContent = '-';
    removeButton.addEventListener('click', function() {
        // Remove this row
        fieldRow.remove();
    });

    buttonsCol.appendChild(addButton);
    buttonsCol.appendChild(removeButton);

    // Append all elements to the field row
    fieldRow.appendChild(fieldNameCol);
    fieldRow.appendChild(fieldTypeCol);
    fieldRow.appendChild(buttonsCol);

    // Append the field row to the container
    document.getElementById('fields-container').appendChild(fieldRow);
});

// Remove the last field row
document.getElementById('remove-field-btn').addEventListener('click', function() {
    const fieldContainer = document.getElementById('fields-container');
    const rows = fieldContainer.getElementsByClassName('row');
    if (rows.length > 1) {
        fieldContainer.removeChild(rows[rows.length - 1]);
    }
});



</script>

@endsection