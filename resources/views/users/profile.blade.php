@extends('layout.index')


@section('content')
<!-- Content Header (Page header) -->
<!-- <section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Profile</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">User Profile</li>
                </ol>
            </div>
        </div>
    </div> /.container-fluid
</section> -->

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3">

                <!-- Profile Image -->
                <div class="card card-primary card-outline">
                    <div class="card-body box-profile">
                        <div class="text-center">
                            <img class="profile-user-img img-fluid img-circle" src="{{ asset('storage/'. $user->photo) }}" alt="Photo de profil" id="render_img" style="width: 100px; height: 100px;">
                            <!-- <span class="fa fa-pencil-alt" id="edit-avatar" style="position: absolute; cursor: pointer;"></span> -->

                        </div>

                        <h3 class="profile-username text-center">{{ $user->firstname }} {{ $user->lastname}}</h3>

                        <!-- <p class="text-muted text-center">{{ $user->poste }} à {{ $user->structure_name }}</p> -->

                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->

                <!-- About Me Box -->
                <div class="card card-primary">
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>
            <!-- /.col -->
            <div class="col-md-9">
                <div class="card card-primary">
                    <div class="card-header p-2">
                        Informations Principales
                    </div><!-- /.card-header -->
                    <div class="card-body">
                        <div class="tab-content">
                            <div class="tab-pane active" id="principale">
                                @if (session('errors'))
                                <div class="mb-4 font-medium text-sm text-green-600 alert alert-danger">
                                    {{ session('errors') }}
                                </div>
                                @endif
                                @if (session('status'))
                                <div class="mb-4 font-medium text-sm text-green-600 alert alert-success">
                                    {{ session('status') }}
                                </div>
                                @endif
                                <form class="form-horizontal" action="{{ route('users.update', $user->id) }}" method="POST" id="myform" enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')

                                    <div class="form-group row">
                                        <label for="inputName" class="col-sm-2 col-form-label">Nom</label>
                                        <div class="col-sm-10">
                                            <div class="input-group">
                                                <input type="text" class="form-control" name="lastname" value="{{$user->lastname}}" id="lastname">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label for="inputName" class="col-sm-2 col-form-label">Prénom</label>
                                        <div class="col-sm-10">
                                            <div class="input-group">
                                                <input type="text" class="form-control" name="firstname" value="{{ $user->firstname }}" id="firstname">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label for="email" class="col-sm-2 col-form-label">Email</label>
                                        <div class="col-sm-10">
                                            <input type="email" name="email" class="form-control" id="email" value="{{ $user->email }}">
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label for="photo" class="col-sm-2 col-form-label">Photo de profil</label>
                                        <div class="col-sm-10">
                                            <input type="file" class="form-control" title="Choisir une photo" name="photo" id="photo" accept="jpeg,jpg,png,gif,PNG,JPG,JPEG">

                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="role" class="col-sm-2 col-form-label">Rôle</label>
                                        <div class="col-sm-10 col-md-6 ">
                                            <select class="form-control" name="role" id="role">
                                                <option value="client" {{ $user->role == 'client' ? 'selected' : '' }}>Client</option>
                                                <option value="worker" {{ $user->role == 'worker' ? 'selected' : '' }}>Artisan</option>
                                                <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Administrateur</option>
                                                <option value="super_admin" {{ $user->role == 'super_admin' ? 'selected' : '' }}>Super Administrateur</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label for="inputName" class="col-sm-2 col-form-label">Téléphone</label>
                                        <div class="col-sm-10">
                                            <div class="input-group">
                                                <input type="text" class="form-control" name="phoneNumber" value="{{ $user->phoneNumber }}" id="phoneNumber">
                                            </div>
                                        </div>
                                    </div>

                                    <!--  <div class="form-group row">
                                        <label for="inputName" class="col-sm-2 col-form-label">Siret</label>
                                        <div class="col-sm-10">
                                            {{-- <input type="text" class="form-control" placeholder="Siret" name="siret" value="{{ $user->siret }}"> --}}
                                            <div class="input-group">
                                                <input type="text" class="form-control" name="siret" value="{{ $user->siret }}" id="siret">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label for="inputName" class="col-sm-2 col-form-label">Tax</label>
                                        <div class="col-sm-10">
                                            <div class="input-group">
                                                <input type="text" class="form-control" name="tax" value="{{ $user->tax }}" id="tax">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label for="inputName" class="col-sm-2 col-form-label">Compte bancaire</label>
                                        <div class="col-sm-10">
                                            <div class="input-group" id="address">
                                                <input type="text" class="form-control" name="account_number" value="{{ $user->account_number }}" id="account_number">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label for="inputName" class="col-sm-2 col-form-label">Adresse de facturation</label>
                                        <div class="col-sm-10">
                                            <div class="input-group" id="address">
                                                <textarea name="address" id="address" cols="30" rows="3" class="form-control">{{ $user->address }}</textarea>
                                            </div>
                                        </div>
                                    </div> -->


                                    <div class="form-group row">
                                        <div class="offset-sm-2 col-sm-10">
                                            <button type="submit" class="btn btn-success" id="submit">Enrégistrer</button>
                                        </div>
                                    </div>
                                </form>
                                <hr>

                            </div>
                            <!-- /.tab-pane -->
                            <div class="tab-pane" id="society">


                            </div>
                            <div class="tab-pane" id="security">

                            </div><!-- /.card-body -->
                        </div>
                        <!-- /.card -->
                    </div>
                    <!-- /.col -->
                </div>
                <!-- /.row -->
            </div><!-- /.container-fluid -->
</section>
<!-- /.content -->
@endsection