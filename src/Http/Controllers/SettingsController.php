<?php

namespace Iqonic\FileManager\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Iqonic\FileManager\Models\Setting;
use Aws\S3\S3Client;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'compress_images' => Setting::get('compress_images', false),
            'compression_quality' => Setting::get('compression_quality', 80),
            'convert_to_webp' => Setting::get('convert_to_webp', false),
            's3_enabled' => Setting::get('s3_enabled', false),
            's3_key' => Setting::get('s3_key', ''),
            's3_secret' => Setting::get('s3_secret', ''),
            's3_region' => Setting::get('s3_region', 'us-east-1'),
            's3_bucket' => Setting::get('s3_bucket', ''),
            's3_endpoint' => Setting::get('s3_endpoint', ''),
            // Theme settings
            'theme_primary_color' => Setting::get('theme_primary_color', '#3B82F6'),
            'theme_sidebar_bg' => Setting::get('theme_sidebar_bg', '#1F2937'),
            'theme_sidebar_text' => Setting::get('theme_sidebar_text', '#F3F4F6'),
            'theme_sidebar_active' => Setting::get('theme_sidebar_active', '#3B82F6'),
            'theme_sidebar_hover_bg' => Setting::get('theme_sidebar_hover_bg', '#ffffff1a'), // Default 10% white (hex with opacity) roughly
            'theme_sidebar_hover_text' => Setting::get('theme_sidebar_hover_text', '#ffffff'),
            'theme_active_font_color' => Setting::get('theme_active_font_color', '#ffffff'),
            'theme_border_radius' => Setting::get('theme_border_radius', '0.5rem'),
            'theme_spacing' => Setting::get('theme_spacing', '1rem'),
            'theme_font_family' => Setting::get('theme_font_family', 'Maven Pro, sans-serif'), // Updated default
            'theme_font_size' => Setting::get('theme_font_size', '14px'),
        ];
        
        $targetInput = ''; // Required by layout
        
        return view('file-manager::settings', compact('settings', 'targetInput'));
    }
    
    public function update(Request $request)
    {
        $validated = $request->validate([
            'compress_images' => 'boolean',
            'compression_quality' => 'integer|min:1|max:100',
            'convert_to_webp' => 'boolean',
            's3_enabled' => 'boolean',
            's3_key' => 'nullable|string',
            's3_secret' => 'nullable|string',
            's3_region' => 'nullable|string',
            's3_bucket' => 'nullable|string',
            's3_endpoint' => 'nullable|string',
            // Theme validation
            'theme_primary_color' => 'nullable|string',
            'theme_sidebar_bg' => 'nullable|string',
            'theme_sidebar_text' => 'nullable|string',
            'theme_sidebar_active' => 'nullable|string',
            'theme_sidebar_hover_bg' => 'nullable|string',
            'theme_sidebar_hover_text' => 'nullable|string',
            'theme_active_font_color' => 'nullable|string',
            'theme_border_radius' => 'nullable|string',
            'theme_spacing' => 'nullable|string',
            'theme_font_family' => 'nullable|string',
            'theme_font_size' => 'nullable|string',
        ]);
        
        foreach ($validated as $key => $value) {
            Setting::set($key, $value);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Settings saved successfully'
        ]);
    }
    
    public function testS3Connection(Request $request)
    {
        $validated = $request->validate([
            's3_key' => 'required|string',
            's3_secret' => 'required|string',
            's3_region' => 'required|string',
            's3_bucket' => 'required|string',
        ]);
        
        try {
            $s3Client = new S3Client([
                'version' => 'latest',
                'region' => $validated['s3_region'],
                'credentials' => [
                    'key' => $validated['s3_key'],
                    'secret' => $validated['s3_secret'],
                ],
            ]);
            
            // Test bucket access
            $result = $s3Client->headBucket(['Bucket' => $validated['s3_bucket']]);
            
            return response()->json([
                'success' => true,
                'message' => 'Connected successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage()
            ], 422);
        }
    }
}
