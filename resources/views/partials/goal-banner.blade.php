@php
    use App\Support\Theme;

    $banner = media_url($bannerImage ?? $goal->banner_image);
    $style = Theme::bannerStyle();
    $variant = $banner ? 'image' : ($style === 'solid' ? 'solid' : 'gradient');
    $logo = media_url($goal->logo);
@endphp

<section @class(['goal-banner', 'goal-banner--'.$variant])
         @if ($banner) style="--banner-image:url('{{ $banner }}')" @endif>
    <div class="shell">
        <div class="goal-banner-inner">
            <div class="goal-emblem">
                @if ($logo)
                    <img src="{{ $logo }}" alt="Goal {{ $goal->code }} emblem">
                @elseif ($goal->icon)
                    <x-icon :name="$goal->icon" size="96" stroke="1.5" />
                @else
                    <span style="font-family:var(--font-heading);font-size:2.6rem;font-weight:800">{{ $goal->code }}</span>
                @endif
            </div>
            <div>
                @if (filled($goal->code))
                    <span class="code-chip">Goal {{ $goal->code }}</span>
                @endif
                <h1>{{ $heading ?? $goal->title }}</h1>
                @if ($statement = ($subheading ?? $goal->statement))
                    <p class="statement">{{ $statement }}</p>
                @endif
            </div>
        </div>
    </div>
</section>
