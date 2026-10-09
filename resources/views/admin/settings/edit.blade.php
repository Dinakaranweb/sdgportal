@extends('layouts.admin', ['section' => 'settings'])

@section('title', $groups[$group])
@section('heading', 'Theme and settings')
@section('subheading', 'Everything here feeds the public portal immediately after saving.')

@section('actions')
    <a class="btn btn-ghost" href="{{ route('home') }}" target="_blank" rel="noopener">
        <x-icon name="external" size="15" /> View portal
    </a>
@endsection

@section('content')
    <nav class="tabs" aria-label="Setting groups">
        @foreach ($groups as $key => $label)
            <a href="{{ route('admin.settings.edit', $key) }}" @class(['is-active' => $key === $group])>{{ $label }}</a>
        @endforeach
    </nav>

    @if ($group === 'theme')
        <div class="note-box">
            Colours below are written straight into the portal stylesheet as custom properties.
            A goal can still override its own colour, and that override wins on its pages and everything nested under them.
        </div>
    @endif

    <form method="post" action="{{ route('admin.settings.update', $group) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="card">
            <div class="card-head">
                <div>
                    <h2>{{ $groups[$group] }}</h2>
                    <p class="sub">{{ $settings->count() }} {{ \Illuminate\Support\Str::plural('setting', $settings->count()) }}</p>
                </div>
            </div>

            <div class="card-body">
                @if ($settings->isEmpty())
                    <div class="empty"><strong>Nothing to configure here</strong></div>
                @else
                    @php
                        $colours = $settings->where('type', 'color');
                        $others = $settings->reject(fn ($s) => $s->type === 'color');
                    @endphp

                    @if ($colours->isNotEmpty())
                        <fieldset>
                            <legend>Colours</legend>
                            <div class="field-row">
                                @foreach ($colours as $setting)
                                    <div class="field">
                                        <label for="f-{{ $setting->key }}">{{ $setting->label ?: $setting->key }}</label>
                                        <span class="colour-field" data-colour-pair>
                                            <input type="color" value="{{ old('fields.'.$setting->key, $setting->value ?: '#ffffff') }}" aria-label="{{ $setting->label }}">
                                            <input type="text" id="f-{{ $setting->key }}" name="fields[{{ $setting->key }}]"
                                                   value="{{ old('fields.'.$setting->key, $setting->value) }}">
                                        </span>
                                        @if ($setting->hint) <p class="hint">{{ $setting->hint }}</p> @endif
                                    </div>
                                @endforeach
                            </div>
                        </fieldset>
                    @endif

                    @foreach ($others as $setting)
                        @php $value = old('fields.'.$setting->key, $setting->value); @endphp

                        @if ($setting->type === 'image')
                            @include('admin.partials.media-field', [
                                'name' => 'fields_'.$setting->key,
                                'value' => $setting->value,
                                'label' => $setting->label ?: $setting->key,
                                'library' => $library,
                            ])
                        @elseif ($setting->type === 'boolean')
                            <div class="field">
                                <label class="check">
                                    <input type="hidden" name="fields[{{ $setting->key }}]" value="0">
                                    <input type="checkbox" name="fields[{{ $setting->key }}]" value="1" @checked($value === '1')>
                                    <span>
                                        {{ $setting->label ?: $setting->key }}
                                        @if ($setting->hint) <small>{{ $setting->hint }}</small> @endif
                                    </span>
                                </label>
                            </div>
                        @elseif ($setting->type === 'select')
                            <div class="field" style="max-width:22rem">
                                <label for="f-{{ $setting->key }}">{{ $setting->label ?: $setting->key }}</label>
                                <select id="f-{{ $setting->key }}" name="fields[{{ $setting->key }}]">
                                    @foreach (($setting->options ?? []) as $optionValue => $optionLabel)
                                        <option value="{{ $optionValue }}" @selected((string) $value === (string) $optionValue)>{{ $optionLabel }}</option>
                                    @endforeach
                                </select>
                                @if ($setting->hint) <p class="hint">{{ $setting->hint }}</p> @endif
                            </div>
                        @elseif ($setting->type === 'number')
                            <div class="field" style="max-width:12rem">
                                <label for="f-{{ $setting->key }}">{{ $setting->label ?: $setting->key }}</label>
                                <input type="number" id="f-{{ $setting->key }}" name="fields[{{ $setting->key }}]" value="{{ $value }}">
                                @if ($setting->hint) <p class="hint">{{ $setting->hint }}</p> @endif
                            </div>
                        @elseif (in_array($setting->type, ['textarea', 'richtext'], true))
                            <div class="field">
                                <label for="f-{{ $setting->key }}">{{ $setting->label ?: $setting->key }}</label>
                                <textarea id="f-{{ $setting->key }}" name="fields[{{ $setting->key }}]"
                                          rows="{{ $setting->type === 'richtext' ? 7 : 3 }}"
                                          @if ($setting->type === 'richtext') data-editor data-editor-height="240" @endif>{{ $value }}</textarea>
                                @if ($setting->hint) <p class="hint">{{ $setting->hint }}</p> @endif
                            </div>
                        @else
                            <div class="field">
                                <label for="f-{{ $setting->key }}">{{ $setting->label ?: $setting->key }}</label>
                                <input type="text" id="f-{{ $setting->key }}" name="fields[{{ $setting->key }}]" value="{{ $value }}">
                                @if ($setting->hint) <p class="hint">{{ $setting->hint }}</p> @endif
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>

            <div class="card-foot">
                <button type="submit" class="btn btn-primary">Save {{ strtolower($groups[$group]) }}</button>
            </div>
        </div>
    </form>
@endsection
