<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'logo' => Setting::getValue('logo'),
            'footer_text' => Setting::getValue('footer_text', '© ' . date('Y') . ' WorkshopPro. All rights reserved.'),
            'slider_images' => json_decode(Setting::getValue('slider_images', '[]'), true),
            'success_background' => Setting::getValue('success_background'),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'footer_text' => 'nullable|string|max:255',
            'slider_images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'success_background' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        // Handle Logo
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            $oldLogo = Setting::getValue('logo');
            if ($oldLogo) Storage::disk('public')->delete($oldLogo);

            $path = $request->file('logo')->store('settings', 'public');
            Setting::setValue('logo', $path);
        }

        // Handle Footer Text
        if ($request->has('footer_text')) {
            Setting::setValue('footer_text', $request->footer_text);
        }

        // Handle Slider Images
        if ($request->hasFile('slider_images')) {
            $existingImages = json_decode(Setting::getValue('slider_images', '[]'), true);
            $newImages = [];
            foreach ($request->file('slider_images') as $image) {
                $path = $image->store('settings/slider', 'public');
                $newImages[] = $path;
            }
            $allImages = array_merge($existingImages, $newImages);
            Setting::setValue('slider_images', json_encode($allImages));
        }

        if ($request->hasFile('success_background')) {
            $old = Setting::getValue('success_background');
            if ($old) {
                Storage::disk('public')->delete($old);
            }

            $path = $request->file('success_background')->store('settings/success', 'public');
            Setting::setValue('success_background', $path);
        }

        return back()->with('success', 'Settings updated successfully.');
    }

    public function removeSliderImage(Request $request)
    {
        $path = $request->path;
        $images = json_decode(Setting::getValue('slider_images', '[]'), true);

        if (($key = array_search($path, $images)) !== false) {
            unset($images[$key]);
            Storage::disk('public')->delete($path);
            Setting::setValue('slider_images', json_encode(array_values($images)));
        }

        return back()->with('success', 'Slider image removed.');
    }
}
