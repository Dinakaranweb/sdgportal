<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesUploads;
use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    use HandlesUploads;

    public const GROUPS = [
        'general' => 'Identity',
        'theme' => 'Theme & colours',
        'home' => 'Home page',
        'footer' => 'Footer & contact',
        'seo' => 'Search & sharing',
    ];

    public function edit(string $group = 'general'): View
    {
        abort_unless(array_key_exists($group, self::GROUPS), 404);

        return view('admin.settings.edit', [
            'group' => $group,
            'groups' => self::GROUPS,
            'settings' => Setting::where('group', $group)->orderBy('sort_order')->orderBy('id')->get(),
            'library' => Media::latest()->limit(60)->get(),
        ]);
    }

    public function update(Request $request, string $group): RedirectResponse
    {
        abort_unless(array_key_exists($group, self::GROUPS), 404);

        $definitions = Setting::where('group', $group)->get()->keyBy('key');

        foreach ($definitions as $key => $definition) {
            // Image fields are handled on their own because they arrive as an
            // upload, a library path or a remove flag rather than one input.
            if ($definition->type === 'image') {
                $definition->update([
                    'value' => $this->resolveUpload($request, 'fields_'.$key, 'settings', $definition->value),
                ]);

                continue;
            }

            // A setting the form did not submit is left as it is, so a partial
            // post can never silently blank out the rest of the group.
            if (! $request->has('fields.'.$key)) {
                continue;
            }

            $value = match ($definition->type) {
                'boolean' => $request->boolean('fields.'.$key) ? '1' : '0',
                'richtext' => clean_html($request->input('fields.'.$key)),
                default => $request->input('fields.'.$key),
            };

            $definition->update(['value' => is_string($value) ? trim($value) : $value]);
        }

        Setting::flush();

        return redirect()
            ->route('admin.settings.edit', $group)
            ->with('status', self::GROUPS[$group].' settings saved.');
    }
}
