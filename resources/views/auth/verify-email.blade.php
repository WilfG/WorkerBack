@extends('layouts.auth')

@section('main')

<div class="register-box">
    <div class="card card-outline card-primary">
        <div class="card-header text-center">
            <a href="#" class="h1"><b>C</b>loseo</a>
        </div>
      <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if (session('status'))
            <div class="alert alert-success text-center">
                {{ __("Lien de vérification envoyé avec succès !")}}
            </div>
        @endif

        <p class="login-box-msg"> Vueillez vérifier votre adresse mail en consultant votre boîte mail</p>
        <form class="js-validation-reminder" action="/email/verification-notification" method="post">
            @csrf
            <div class="form-group">
            {{ __("Vous n'avez pas reçu le mail ou l'avez perdu")}} ?
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                     {{ __('Cliquez ici pour renvoyer le mail de vérification')}}
                </button>
            </div>
        </form>
      </div>
      <!-- /.form-box -->
    </div><!-- /.card -->
</div>

<!-- Page Content -->
@endsection
