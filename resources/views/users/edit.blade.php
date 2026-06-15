@extends('layout.new_main')
@section('title', 'Modifier l\'utilisateur')

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
        transition: all 0.3s ease;
    }

    .avatar-edit-icon:hover {
        background: #0a3449;
        transform: scale(1.1);
    }

    .form-section {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 8px;
        margin-bottom: 2rem;
    }

    .form-section-title {
        color: #0E4561;
        font-size: 1.2rem;
        margin-bottom: 1.5rem;
        font-weight: 600;
    }

    .password-section {
        border-top: 1px solid #dee2e6;
        margin-top: 2rem;
        padding-top: 2rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="text-primary">
                    <i class="fas fa-user-edit me-2"></i>
                    Modifier l'utilisateur
                </h1>
                <a href="{{ route('users.index') }}" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left"></i> Retour à la liste
                </a>
            </div>

            @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <div class="form-container">
                <div class="text-center mb-4">
                    <div class="avatar-container">
                        @if($user->avatar)
                        <img src="{{ asset('storage/' . $user->avatar) }}" alt="Avatar" class="avatar-preview" id="avatarPreview">
                        @else
                        <div class="avatar-preview d-flex align-items-center justify-content-center bg-secondary text-white">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                        @endif
                        <label for="avatar" class="avatar-edit-icon" title="Changer la photo">
                            <i class="fas fa-camera"></i>
                        </label>
                    </div>
                </div>

                <form action="{{ route('users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="form-section">
                        <h3 class="form-section-title">
                            <i class="fas fa-user-circle me-2"></i>
                            Informations personnelles
                        </h3>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Nom complet</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                        id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                </div>
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                        id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                </div>
                                @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Téléphone</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                        id="phone" name="phone" value="{{ old('phone', $user->phone) }}" required>
                                </div>
                                @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="role" class="form-label">Rôle</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user-tag"></i></span>
                                    <select class="form-select @error('role') is-invalid @enderror"
                                        id="role" name="role" required>
                                        <option value="">Sélectionner un rôle</option>
                                        <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Administrateur</option>
                                        <option value="worker" {{ old('role', $user->role) == 'worker' ? 'selected' : '' }}>Travailleur</option>
                                        <option value="client" {{ old('role', $user->role) == 'client' ? 'selected' : '' }}>Client</option>
                                    </select>
                                </div>
                                @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="form-section-title">
                            <i class="fas fa-lock me-2"></i>
                            Mot de passe
                        </h3>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Nouveau mot de passe (optionnel)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                        id="password" name="password">
                                </div>
                                <div class="form-text">Laissez vide pour garder le mot de passe actuel</div>
                                @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">Confirmer le nouveau mot de passe</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                                    <input type="password" class="form-control"
                                        id="password_confirmation" name="password_confirmation">
                                </div>
                            </div>
                        </div>
                    </div>

                    <input type="file" class="form-control d-none"
                        id="avatar" name="avatar" accept="image/*">

                    <div class="d-flex justify-content-end mt-4">
                        <a href="{{ route('users.index') }}" class="btn btn-secondary me-2">
                            <i class="fas fa-times"></i> Annuler
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Mettre à jour
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
                if (preview.tagName === 'IMG') {
                    preview.src = e.target.result;
                } else {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.id = 'avatarPreview';
                    img.className = 'avatar-preview';
                    img.alt = 'Avatar';
                    preview.parentNode.replaceChild(img, preview);
                }
            }
            reader.readAsDataURL(file);
        }
    });

    // Confirmation avant de quitter si le formulaire a été modifié
    const form = document.querySelector('form');
    const initialFormState = new FormData(form);

    window.addEventListener('beforeunload', function(e) {
        const currentFormState = new FormData(form);
        let formChanged = false;

        for (let pair of currentFormState.entries()) {
            if (pair[1] !== initialFormState.get(pair[0])) {
                formChanged = true;
                break;
            }
        }

        if (formChanged) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
</script>
@endpush