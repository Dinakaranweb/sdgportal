@if (session('status'))
    <div class="flash flash-ok" role="status">
        <x-icon name="check" size="17" />
        <span>{{ session('status') }}</span>
    </div>
@endif

@if ($errors->any())
    <div class="flash flash-bad" role="alert">
        <x-icon name="star" size="17" />
        <span>
            <strong>{{ $errors->count() }} {{ \Illuminate\Support\Str::plural('problem', $errors->count()) }} need attention.</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </span>
    </div>
@endif
