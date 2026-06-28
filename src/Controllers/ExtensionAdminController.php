<?php

namespace RoyalPanel\RoyalAtelier\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use RoyalPanel\RoyalAtelier\Libraries\ExtensionLibrary;

class ExtensionAdminController extends Controller
{
    public function __construct(
        private ExtensionLibrary $library
    ) {}

    public function index()
    {
        $extensions = \RoyalPanel\RoyalAtelier\Models\RxExtension::where('installed', true)->get();
        return view('rxadmin::index', [
            'extensions' => $extensions,
            'blueprint' => $this->library,
        ]);
    }

    public function show(string $id)
    {
        $ext = \RoyalPanel\RoyalAtelier\Models\RxExtension::where('extension_id', $id)->firstOrFail();
        return view('rxadmin::show', [
            'extension' => $ext,
            'EXTENSION_ID' => $ext->extension_id,
            'EXTENSION_NAME' => $ext->name,
            'EXTENSION_VERSION' => $ext->version,
            'EXTENSION_DESCRIPTION' => $ext->description,
            'EXTENSION_ICON' => $ext->icon ?? '/rx-assets/default-icon.svg',
            'EXTENSION_WEBSITE' => $ext->website ?? '',
            'EXTENSION_WEBICON' => 'fa fa-globe',
            'blueprint' => $this->library,
        ]);
    }

    public function install(Request $request)
    {
        $request->validate(['package' => 'required|file|mimes:zip,blueprint']);
        $path = $request->file('package')->store('rx-packages');
        $result = $this->library->install(storage_path("app/{$path}"));

        if (!$result['success']) {
            return redirect()->back()->withErrors(['error' => $result['error']]);
        }

        return redirect()->route('rxadmin.extensions.show', $result['id'])
            ->with('success', 'Extension installed successfully');
    }

    public function uninstall(string $id)
    {
        $this->library->uninstall($id);
        return redirect()->route('rxadmin.extensions.index')
            ->with('success', 'Extension uninstalled');
    }

    public function toggle(string $id)
    {
        $ext = \RoyalPanel\RoyalAtelier\Models\RxExtension::where('extension_id', $id)->firstOrFail();
        $ext->update(['enabled' => !$ext->enabled]);
        return redirect()->back()->with('success', 'Extension ' . ($ext->enabled ? 'enabled' : 'disabled'));
    }

    public function settings()
    {
        return view('rxadmin::settings', ['blueprint' => $this->library]);
    }

    public function updateSettings(Request $request)
    {
        foreach ($request->except(['_token', '_method']) as $key => $value) {
            $this->library->dbSet('rx', $key, $value);
        }
        return redirect()->back()->with('success', 'Settings updated');
    }
}
