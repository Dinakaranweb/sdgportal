@php
    $style = $block->setting('image_style', 'cards');
    $pictures = collect($block->rows())->filter(fn ($row) => filled($row['image'] ?? null));
@endphp

@if ($pictures->isNotEmpty())
    <div class="image-set image-set--{{ $style }} cols-{{ $block->setting('columns', '3') }} fit-{{ $block->setting('image_fit', \App\Support\BlockType::defaultImageFit($block->type)) }}">
        @foreach ($pictures as $row)
            <figure class="image-set-item">
                <img src="{{ media_url($row['image']) }}" alt="{{ $row['alt'] ?? '' }}" loading="lazy">
            </figure>
        @endforeach
    </div>
@endif

{{-- One description for the whole set, underneath the pictures. --}}
@if (filled($block->body))
    <div class="image-set-note prose">{!! rich_text($block->body) !!}</div>
@endif
