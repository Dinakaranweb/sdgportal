@if (filled($block->body))
    <div class="block-intro">{!! rich_text($block->body) !!}</div>
@endif

@php
    $headers = $block->headers();
    $rows = $block->data['rows'] ?? [];
@endphp

@if ($headers || $rows)
    <div class="table-scroll" tabindex="0" role="region" aria-label="{{ $block->title ?: 'Data table' }}">
        <table class="data-table">
            @if ($headers)
                <thead>
                    <tr>
                        @foreach ($headers as $header)
                            <th scope="col">{{ $header }}</th>
                        @endforeach
                    </tr>
                </thead>
            @endif
            <tbody>
                @foreach ($rows as $row)
                    <tr>
                        @foreach ((array) $row as $cell)
                            <td>{!! nl2br(e($cell)) !!}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
