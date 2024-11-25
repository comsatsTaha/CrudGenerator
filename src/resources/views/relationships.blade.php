@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Models and Relationships</h1>

    @foreach($models as $data)
        <div class="card mb-4">
            <div class="card-header">
                <h4>{{ class_basename($data['model']) }}</h4>
            </div>
            <div class="card-body">
                <h5>Relationships:</h5>
                <ul>
                    @forelse($data['relationships'] as $relationship)
                        <li>{{ $relationship }}</li>
                    @empty
                        <li>No relationships defined.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    @endforeach

    <div class="container">
        <h1>Create Relationships</h1>
    
        <form action="{{ route('relation-generator.store') }}" method="POST">
            @csrf
    
            <div class="card">
                <div class="card-body">
                    <div id="relationships-container">
                        <div class="relationship-row row mb-3">
                            <div class="col-md-3">
                                <label for="base_model_0">Base Model</label>
                                <select name="base_model[]" id="base_model_0" class="form-control" required>
                                    <option value="" disabled selected>Select a model</option>
                                    @foreach($models as $data)
                                        <option value="{{ class_basename($data['model']) }}">{{ class_basename($data['model']) }}</option>
                                    @endforeach
                                </select>
                            </div>
    
                            <div class="col-md-3">
                                <label for="relationship_type_0">Relationship Type</label>
                                <select name="relationship_type[]" id="relationship_type_0" class="form-control relationship-type" required>
                                    <option value="" disabled selected>Select relationship type</option>
                                    <option value="HasOne">HasOne</option>
                                    <option value="HasMany">HasMany</option>
                                    <option value="BelongsTo">BelongsTo</option>
                                    <option value="BelongsToMany">BelongsToMany</option>
                                    <option value="MorphOne">MorphOne</option>
                                    <option value="MorphMany">MorphMany</option>
                                    <option value="MorphTo">MorphTo</option>
                                    <option value="MorphToMany">MorphToMany</option>
                                </select>
                            </div>
    
                            <div class="col-md-3">
                                <label for="related_model_0">Related Model</label>
                                <select name="related_model[]" id="related_model_0" class="form-control" required>
                                    <option value="" disabled selected>Select a related model</option>
                                    @foreach($models as $data)
                                        <option value="{{ class_basename($data['model']) }}">{{ class_basename($data['model']) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3 d-flex align-items-end">
                                <button type="button" class="btn btn-success btn-sm add-relationship-btn">+</button>
                                <button type="button" class="btn btn-danger btn-sm remove-relationship-btn ml-2">-</button>
                            </div>
    
                            <div class="col-md-12 mt-2 additional-fields"></div>
                        </div>
                        <hr class="m-4" style="height: 5px;color:#333;background-color:#333;border:none">
                    </div>
    
                    <button type="submit" class="btn btn-primary mt-3">Create Relationships</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const relationshipsContainer = document.getElementById('relationships-container');

        // Add a new relationship row
        document.addEventListener('click', function (event) {
            if (event.target && event.target.classList.contains('add-relationship-btn')) {
                const relationshipRow = event.target.closest('.relationship-row');
                const newRow = relationshipRow.cloneNode(true);

                // Reset input values in the cloned row
                newRow.querySelectorAll('select, input').forEach(function (element) {
                    element.value = '';
                    const id = element.id;
                    if (id) {
                        const newId = id.replace(/\d+$/, '') + Date.now(); // Unique ID
                        element.id = newId;
                    }
                });

                newRow.querySelector('.additional-fields').innerHTML = '';
                relationshipsContainer.appendChild(newRow);

                // Add <hr> after the new relationship row
                const hr = document.createElement('hr');
                hr.classList.add('m-4');
                hr.style.height = '5px';
                hr.style.color = '#333';
                hr.style.backgroundColor = '#333';
                hr.style.border = 'none';
                relationshipsContainer.appendChild(hr);
            }
        });

        // Remove a relationship row
        document.addEventListener('click', function (event) {
            if (event.target && event.target.classList.contains('remove-relationship-btn')) {
                const relationshipRow = event.target.closest('.relationship-row');
                if (relationshipsContainer.childElementCount > 1) {
                    relationshipRow.remove();
                    // Remove the corresponding <hr> element if it exists
                    const hr = relationshipRow.nextElementSibling;
                    if (hr && hr.tagName === 'HR') {
                        hr.remove();
                    }
                }
            }
        });

        // Show additional fields based on relationship type
        document.addEventListener('change', function (event) {
            if (event.target && event.target.classList.contains('relationship-type')) {
                const relationshipType = event.target.value;
                const additionalFieldsContainer = event.target.closest('.relationship-row').querySelector('.additional-fields');

                additionalFieldsContainer.innerHTML = ''; // Clear previous fields

                if (['BelongsTo', 'HasOne', 'HasMany'].includes(relationshipType)) {
                    additionalFieldsContainer.innerHTML = `
                        <div class="form-group">
                            <label for="foreign_key">Foreign Key</label>
                            <input type="text" name="foreign_key[]" class="form-control" placeholder="e.g., user_id" required>
                        </div>
                    `;
                }

                if (['BelongsToMany', 'MorphToMany'].includes(relationshipType)) {
                    additionalFieldsContainer.innerHTML = `
                        <div class="form-group">
                            <label for="pivot_table">Pivot Table</label>
                            <input type="text" name="pivot_table[]" class="form-control" placeholder="e.g., role_user" required>
                        </div>
                    `;
                }

                if (relationshipType.startsWith('Morph')) {
                    additionalFieldsContainer.innerHTML = `
                        <div class="form-group">
                            <label for="morph_name">Morph Name</label>
                            <input type="text" name="morph_name[]" class="form-control" placeholder="e.g., imageable" required>
                        </div>
                    `;
                }
            }
        });
    });
</script>
@endsection
