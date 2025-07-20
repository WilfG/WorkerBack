@extends('layouts.app')
@section('content')
<h1>Utilisateurs</h1>
<table>
    <tr>
        <th>ID</th><th>Nom</th><th>Email</th><th>Rôle</th><th>Actions</th>
    </tr>
    @foreach($users as $user)
    <tr>
        <td>{{ $user->id }}</td>
        <td>{{ $user->name }}</td>
        <td>{{ $user->email }}</td>
        <td>{{ $user->role }}</td>
        <td>
            <a href="{{ route('users.edit', $user) }}">Modifier</a>
            <!-- Add delete, show, etc. -->
        </td>
    </tr>
    @endforeach
</table>
@endsection