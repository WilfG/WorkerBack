@extends('layout.new_main')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12 mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-3">{{ isset($user) ? 'Modifier' : 'Créer' }} un utilisateur</h1>
                <a href="{{ route('users.index') }}" class="btn btn-secondary">
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
            <h5 class="card-title mb-0">Informations de l'utilisateur</h5>
        </div>
        <div class="card-body">
            <form action="{{ isset($user) ? route('users.update', $user->id) : route('users.store') }}" method="POST">
                @csrf
                @if(isset($user))
                    @method('PUT')
                @endif

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="firstname" class="form-label">Prénom</label>
                        <input type="text" class="form-control" id="firstname" name="firstname" 
                               value="{{ old('firstname', isset($user) ? $user->firstname : '') }}" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="lastname" class="form-label">Nom</label>
                        <input type="text" class="form-control" id="lastname" name="lastname" 
                               value="{{ old('lastname', isset($user) ? $user->lastname : '') }}" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="{{ old('email', isset($user) ? $user->email : '') }}" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="phone_number" class="form-label">Téléphone</label>
                        <input type="text" class="form-control" id="phone_number" name="phone_number" 
                               value="{{ old('phone_number', isset($user) ? $user->phone_number : '') }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="role" class="form-label">Rôle</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="">Sélectionner un rôle</option>
                            <option value="admin" {{ (old('role', isset($user) ? $user->role : '') == 'admin') ? 'selected' : '' }}>Administrateur</option>
                            <option value="worker" {{ (old('role', isset($user) ? $user->role : '') == 'worker') ? 'selected' : '' }}>Travailleur</option>
                            <option value="client" {{ (old('role', isset($user) ? $user->role : '') == 'client') ? 'selected' : '' }}>Client</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="status" class="form-label">Statut</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="1" {{ (old('status', isset($user) ? $user->status : '') == 1) ? 'selected' : '' }}>Actif</option>
                            <option value="0" {{ (old('status', isset($user) ? $user->status : '') == 0) ? 'selected' : '' }}>Inactif</option>
                        </select>
                    </div>
                </div>

                @if(!isset($user))
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">Mot de passe</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="password_confirmation" class="form-label">Confirmer le mot de passe</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                    </div>
                </div>
                @endif

                <div class="text-end mt-3">
                    <button type="submit" class="btn save-button">
                        <i class="fas fa-save"></i> {{ isset($user) ? 'Modifier' : 'Enregistrer' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
