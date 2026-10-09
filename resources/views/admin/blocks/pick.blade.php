@extends('layouts.admin', ['section' => $ownerType === 'goal' ? 'goals' : 'pages'])

@section('title', 'Add a block')
@section('heading', 'Add a content block')
@section('subheading', 'Choose what this block should hold. You can combine text, links, images, files and data within most types.')

@section('actions')
    <a class="btn btn-ghost" href="{{ $backUrl }}">Cancel</a>
@endsection

@section('content')
    <div class="card">
        <div class="card-head">
            <div>
                <h2>Adding to: {{ $ownerType === 'goal' ? $owner->title : $owner->title }}</h2>
                <p class="sub">
                    @if ($ownerType === 'goal')
                        Goal {{ $owner->code }} landing page
                    @else
                        Goal {{ $goal->code }} &rarr; /{{ $owner->path }}
                    @endif
                </p>
            </div>
        </div>
        <div class="card-body">
            <div class="type-picker">
                @foreach ($grouped as $group => $types)
                    <div class="type-group">
                        <span>{{ $group }}</span>
                        <div class="type-options">
                            @foreach ($types as $key => $definition)
                                <a class="type-option" href="{{ route('admin.blocks.create', [$ownerType, $owner->getKey()]) }}?type={{ $key }}">
                                    <span class="ico" aria-hidden="true"><x-icon :name="$definition['icon'] ?? 'text'" size="18" /></span>
                                    <span>
                                        <strong>{{ $definition['label'] }}</strong>
                                        <small>{{ $definition['hint'] }}</small>
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
