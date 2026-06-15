@extends('layout.new_main')
<!-- @section('title', 'Ajouter un utilisateur') -->

@push('styles')
<style>
    .form-container {
        background-color: white;
        padding: 2rem;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .btn-primary {
        background-color: #0E4561;
        border-color: #0E4561;
    }

    .btn-primary:hover {
        background-color: #0a3449;
        border-color: #0a3449;
    }

    .text-primary {
        color: #0E4561 !important;
    }

    .avatar-preview {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        object-fit: cover;
        margin-bottom: 1rem;
        border: 3px solid #0E4561;
    }

    .avatar-container {
        position: relative;
        width: fit-content;
        margin: 0 auto;
    }

    .avatar-placeholder {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        background-color: #e9ecef;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        color: #adb5bd;
        margin-bottom: 1rem;
        border: 3px solid #0E4561;
    }

    .avatar-edit-icon {
        position: absolute;
        bottom: 0;
        right: 0;
        background: #0E4561;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        cursor: pointer;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="text-primary">Ajouter un utilisateur</h1>
                <a href="{{ route('users.index') }}" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left"></i> Retour à la liste
                </a>
            </div>

            @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <div class="form-container">
                <div class="text-center mb-4">
                    <div class="avatar-container">
                        <div class="avatar-placeholder" id="avatarPreview">
                            <i class="fas fa-user"></i>
                        </div>
                        <label for="avatar" class="avatar-edit-icon">
                            <i class="fas fa-camera"></i>
                        </label>
                    </div>
                </div>

                <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Nom complet</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                id="email" name="email" value="{{ old('email') }}" required>
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Téléphone</label>
                            <input type="text" class="form-control @error('phone_number') is-invalid @enderror"
                                id="phone" name="phone_number" value="{{ old('phone_number') }}" required>
                            @error('phone_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="role" class="form-label">Rôle</label>
                            <select class="form-select @error('role') is-invalid @enderror"
                                id="role" name="role" required>
                                <option value="" selected disabled>Sélectionner un rôle</option>
                                <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Administrateur</option>
                                <option value="worker" {{ old('role') == 'worker' ? 'selected' : '' }}>Travailleur</option>
                                <option value="client" {{ old('role') == 'client' ? 'selected' : '' }}>Client</option>
                            </select>
                            @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Mot de passe</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                id="password" name="password" required>
                            @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="password_confirmation" class="form-label">Confirmer le mot de passe</label>
                            <input type="password" class="form-control"
                                id="password_confirmation" name="password_confirmation" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="avatar" class="form-label">Photo de profil</label>
                            <input type="file" class="form-control @error('avatar') is-invalid @enderror"
                                id="avatar" name="avatar" accept="image/*">
                            @error('avatar')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        <a href="{{ route('users.index') }}" class="btn btn-secondary me-2">
                            <i class="fas fa-times"></i> Annuler
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Prévisualisation de l'image
    document.getElementById('avatar').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('avatarPreview');
                // Créer une nouvelle image
                const img = document.createElement('img');
                img.src = e.target.result;
                img.id = 'avatarPreview';
                img.className = 'avatar-preview';
                img.alt = 'Avatar';
                // Remplacer le placeholder par l'image
                preview.parentNode.replaceChild(img, preview);
            }
            reader.readAsDataURL(file);
        }
    });
</script>
@endpush