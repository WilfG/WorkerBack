@extends('layout.new_main')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12 mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-3">{{ isset($profession) ? 'Modifier' : 'Créer' }} une profession</h1>
                <a href="{{ route('professions.index') }}" class="btn btn-secondary">
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
            <h5 class="card-title mb-0">Informations de la profession</h5>
        </div>
        <div class="card-body">
            <form action="{{ isset($profession) ? route('professions.update', $profession->id) : route('professions.store') }}" method="POST">
                @csrf
                @if(isset($profession))
                    @method('PUT')
                @endif

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Nom</label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="{{ old('name', isset($profession) ? $profession->name : '') }}" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="category_id" class="form-label">Catégorie</label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="">Sélectionner une catégorie</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" 
                                    {{ (old('category_id', isset($profession) ? $profession->category_id : '') == $category->id) ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4">{{ old('description', isset($profession) ? $profession->description : '') }}</textarea>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="status" class="form-label">Statut</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="1" {{ (old('status', isset($profession) ? $profession->status : '') == 1) ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ (old('status', isset($profession) ? $profession->status : '') == 0) ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="text-end mt-3">
                    <button type="submit" class="btn save-button">
                        <i class="fas fa-save"></i> {{ isset($profession) ? 'Modifier' : 'Enregistrer' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
