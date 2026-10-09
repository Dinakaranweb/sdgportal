@php use App\Models\MenuItem; @endphp

<div class="field">
    <label>Label <span class="req">*</span></label>
    <input type="text" name="label" value="{{ old('label', $item->label) }}" required>
</div>

<div class="field">
    <label>Links to</label>
    <select name="link_type">
        @foreach (MenuItem::LINK_TYPES as $key => $text)
            <option value="{{ $key }}" @selected(old('link_type', $item->link_type ?: 'url') === $key)>{{ $text }}</option>
        @endforeach
    </select>
</div>

<div class="field">
    <label>Custom URL</label>
    <input type="text" name="url" value="{{ old('url', $item->url) }}" placeholder="/about  or  https://">
    <p class="hint">Used when the link type above is "Custom URL".</p>
</div>

<div class="field">
    <label>Goal</label>
    <select name="goal_id">
        <option value="">Not a goal link</option>
        @foreach ($goals as $goalOption)
            <option value="{{ $goalOption->id }}" @selected((int) old('goal_id', $item->goal_id) === $goalOption->id)>
                {{ $goalOption->code }}. {{ \Illuminate\Support\Str::limit($goalOption->title, 36) }}
            </option>
        @endforeach
    </select>
</div>

<div class="field">
    <label>Sub menu or question page</label>
    <select name="goal_page_id">
        <option value="">Not a page link</option>
        @foreach ($pages as $pageOption)
            <option value="{{ $pageOption->id }}" @selected((int) old('goal_page_id', $item->goal_page_id) === $pageOption->id)>
                {{ $pageOption->goal?->code }} &middot; {{ \Illuminate\Support\Str::limit($pageOption->title, 40) }}
            </option>
        @endforeach
    </select>
</div>

<div class="field">
    <label>Nest under</label>
    <select name="parent_id">
        <option value="">Top level</option>
        @foreach ($items as $parentOption)
            @continue($parentOption->id === $item->id)
            <option value="{{ $parentOption->id }}" @selected((int) old('parent_id', $item->parent_id) === $parentOption->id)>
                {{ $parentOption->label }}
            </option>
        @endforeach
    </select>
</div>

<div class="check-list">
    <label class="check">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $item->is_active ?? true))>
        <span>Visible</span>
    </label>
    <label class="check">
        <input type="checkbox" name="open_in_new_tab" value="1" @checked(old('open_in_new_tab', $item->open_in_new_tab ?? false))>
        <span>Open in a new tab</span>
    </label>
</div>
