@extends('layouts.admin', ['section' => 'media'])

@section('title', 'Media library')
@section('heading', 'Media library')
@section('subheading', 'Logos, goal artwork, photographs and documents. Paths from here can be pasted into any image or file field.')

@section('content')
    <div class="card">
        <div class="card-head"><h2>Upload files</h2></div>
        <form method="post" action="{{ route('admin.media.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="card-body">
                <div class="field-row">
                    <div class="field">
                        <label for="files">Choose files</label>
                        <input type="file" id="files" name="files[]" multiple required
                               accept=".jpg,.jpeg,.png,.gif,.webp,.svg,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.csv,.txt,.zip">
                        <p class="hint">Up to 20 files at a time, 20 MB each.</p>
                    </div>
                    <div class="field">
                        <label for="alt_text">Alternative text</label>
                        <input type="text" id="alt_text" name="alt_text" placeholder="Applied to every file in this upload">
                    </div>
                </div>
            </div>
            <div class="card-foot">
                <button type="submit" class="btn btn-primary">Upload</button>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-head">
            <form method="get" action="{{ route('admin.media.index') }}" style="display:flex;gap:.5rem">
                <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search by name" style="width:15rem">
                <button type="submit" class="btn btn-ghost btn-sm">Search</button>
                @if (! empty($filters['q']))
                    <a class="btn btn-ghost btn-sm" href="{{ route('admin.media.index') }}">Clear</a>
                @endif
            </form>
            <div class="spacer">
                <span class="badge badge-neutral">{{ $media->total() }} {{ \Illuminate\Support\Str::plural('file', $media->total()) }}</span>
            </div>
        </div>
        <div class="card-body">
            @if ($media->isEmpty())
                <div class="empty">
                    <strong>Nothing in the library yet</strong>
                    Upload a logo or a report to get started.
                </div>
            @else
                <div class="media-grid">
                    @foreach ($media as $item)
                        <div class="media-item">
                            <div class="shot">
                                @if ($item->isImage())
                                    <img src="{{ $item->url }}" alt="{{ $item->alt_text }}" loading="lazy">
                                @else
                                    <span class="ext">{{ strtoupper(pathinfo($item->file_name, PATHINFO_EXTENSION)) }}</span>
                                @endif
                            </div>
                            <div class="info">
                                <strong>{{ \Illuminate\Support\Str::limit($item->name, 28) }}</strong>
                                <span class="size">{{ $item->readableSize() }}</span>
                            </div>
                            <div class="tools">
                                <input type="text" value="{{ $item->path }}" readonly
                                       id="media-{{ $item->id }}" aria-label="Library path"
                                       onclick="this.select()">
                                <button type="button" class="btn btn-ghost btn-sm" data-copy-field="#media-{{ $item->id }}" title="Copy the path">Copy</button>
                            </div>
                            <div class="tools" style="padding-bottom:.65rem">
                                <a class="btn btn-ghost btn-sm" href="{{ $item->url }}" target="_blank" rel="noopener" style="flex:1">Open</a>
                                <form method="post" action="{{ route('admin.media.destroy', $item) }}"
                                      data-confirm="Delete {{ $item->name }}? Pages still using it will show a gap."
                                      style="flex:1">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" style="width:100%">Delete</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{ $media->links() }}
            @endif
        </div>
    </div>
@endsection
