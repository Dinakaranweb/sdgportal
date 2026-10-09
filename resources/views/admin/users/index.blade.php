@extends('layouts.admin', ['section' => 'users'])

@section('title', 'Accounts')
@section('heading', 'Accounts')
@section('subheading', 'Who can sign in to the control panel.')

@section('actions')
    <a class="btn btn-primary" href="{{ route('admin.users.create') }}">Add an account</a>
@endsection

@section('content')
    <div class="card">
        <div class="card-body card-body--flush">
            <div class="table-wrap">
                <table class="list">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td><strong>{{ $user->name }}</strong></td>
                                <td class="muted">{{ $user->email }}</td>
                                <td>
                                    <span class="badge {{ $user->isAdministrator() ? 'badge-type' : 'badge-neutral' }}">
                                        {{ \App\Models\User::ROLES[$user->role] ?? $user->role }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $user->is_active ? 'badge-live' : 'badge-draft' }}">
                                        {{ $user->is_active ? 'Active' : 'Suspended' }}
                                    </span>
                                </td>
                                <td class="actions">
                                    <a class="btn btn-soft btn-sm" href="{{ route('admin.users.edit', $user) }}">Edit</a>
                                    @if ($user->id !== auth()->id())
                                        <form method="post" action="{{ route('admin.users.destroy', $user) }}"
                                              style="display:inline" data-confirm="Delete the account for {{ $user->name }}?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
