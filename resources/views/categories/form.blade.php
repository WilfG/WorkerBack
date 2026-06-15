@extends('layout.new_main')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12 mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-3">{{ isset($category) ? 'Modifier' : 'Créer' }} une catégorie</h1>
                <a href="{{ route('categories.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
            </div>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Informations de la catégorie</h5>
        </div>
        <div class="card-body">
            <form action="{{ isset($category) ? route('categories.update', $category->id) : route('categories.store') }}" method="POST">
                @csrf
                @if(isset($category))
                    @method('PUT')
                @endif

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Nom</label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="{{ old('name', isset($category) ? $category->name : '') }}" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="status" class="form-label">Statut</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="1" {{ (old('status', isset($category) ? $category->status : '') == 1) ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ (old('status', isset($category) ? $category->status : '') == 0) ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="4">{{ old('description', isset($category) ? $category->description : '') }}</textarea>
                </div>

                <div class="text-end mt-3">
                    <button type="submit" class="btn save-button">
                        <i class="fas fa-save"></i> {{ isset($category) ? 'Modifier' : 'Enregistrer' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
