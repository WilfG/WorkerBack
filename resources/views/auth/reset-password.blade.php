@extends('layout.index')

@section('content')

<div class="register-box">
    <div class="card card-outline card-primary">
       
        <div class="card-body">
            <p class="login-box-msg">Changement de mot de passe</p>
            <form class="form-signin" action="{{route('reset_password')}}" method="post">
                @csrf
                @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
                @if (session('success'))
                    <div class="mb-4 font-medium text-sm text-green-600">
                        {{ session('success') }}
                    </div>
                @endif


                <input type="email" name="email" class="form-control mb-1" placeholder="Votre Adresse Mail" required value="{{ auth()->user()->email }}">
                <input type="password" name="password" class="form-control mb-1" placeholder="Taper un mot de passe" required>
                <input type="password" name="password_confirmation" class="form-control" placeholder="Retaper le même mot de passe" required>

                <button class="btn btn-lg btn-primary btn-block my-2" type="submit">Réinitialiser mon mot de passe</button>
                <!-- <a class="btn btn-lg btn-secondary btn-block" href="/login">Se connecter</a> -->
            </form>
        </div>
    </div>
</div>

@endsection



