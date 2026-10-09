@extends('layouts.admin', ['section' => 'users'])

@php
    $isNew = ! $user->exists;
    $action = $isNew ? route('admin.users.store') : route('admin.users.update', $user);
@endphp

@section('title', $isNew ? 'New account' : 'Edit account')
@section('heading', $isNew ? 'New account' : $user->name)

@section('actions')
    <a class="btn btn-ghost" href="{{ route('admin.users.index') }}">Back</a>
@endsection

@section('content')
    <form method="post" action="{{ $action }}" style="max-width:34rem">
        @csrf
        @unless ($isNew) @method('PUT') @endunless

        <div class="card">
            <div class="card-head"><h2>Account details</h2></div>
            <div class="card-body">
                <div class="field">
                    <label for="name">Name <span class="req">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                </div>

                <div class="field">
                    <label for="email">Email address <span class="req">*</span></label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="off">
                </div>

                <div class="field-row">
                    <div class="field">
                        <label for="password">{{ $isNew ? 'Password' : 'New password' }} @if ($isNew)<span class="req">*</span>@endif</label>
                        <input type="password" id="password" name="password" autocomplete="new-password" @required($isNew)>
                        @unless ($isNew) <p class="hint">Leave blank to keep the current password.</p> @endunless
                    </div>
                    <div class="field">
                        <label for="password_confirmation">Confirm password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" @required($isNew)>
                    </div>
                </div>

                <div class="field">
                    <label for="role">Role</label>
                    <select id="role" name="role">
                        @foreach (\App\Models\User::ROLES as $key => $label)
                            <option value="{{ $key }}" @selected(old('role', $user->role ?: 'editor') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="hint">Only administrators can manage accounts. Editors can do everything else.</p>
                </div>

                <label class="check">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active ?? true))>
                    <span>Allowed to sign in</span>
                </label>
            </div>
            <div class="card-foot">
                <button type="submit" class="btn btn-primary">{{ $isNew ? 'Create account' : 'Save changes' }}</button>
                <a class="btn btn-ghost" href="{{ route('admin.users.index') }}">Cancel</a>
            </div>
        </div>
    </form>
@endsection
