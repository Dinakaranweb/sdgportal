@extends('layouts.admin', ['section' => 'sitepages'])

@section('title', 'Home page')
@section('heading', 'Home page')
@section('subheading', 'Content blocks shown on the home page, underneath the goal grid.')

@section('actions')
    <a class="btn btn-ghost" href="{{ route('home') }}" target="_blank" rel="noopener">
        <x-icon name="external" size="15" /> View home page
    </a>
    <a class="btn btn-ghost" href="{{ route('admin.sitepages.index') }}">All pages</a>
@endsection

@section('content')
    <div class="note-box">
        The hero, introduction and goal grid at the top of the home page come from
        <a href="{{ route('admin.settings.edit', 'home') }}">Theme &amp; settings &rarr; Home page</a>.
        Blocks added here appear below them. Give a block a <strong>Read more link</strong>
        and it gets a button through to another page.
    </div>

    @include('admin.blocks.list', ['owner' => $page, 'ownerType' => 'site-page'])
@endsection
