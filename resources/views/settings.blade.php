@extends('file-manager::layout')

@section('title', 'Settings')

@section('content')
    <div class="max-w-2xl" x-data="{
        compress_images: {{ $settings['compress_images'] ? 'true' : 'false' }},
        compression_quality: {{ $settings['compression_quality'] }},
        convert_to_webp: {{ $settings['convert_to_webp'] ? 'true' : 'false' }},
        s3_enabled: {{ $settings['s3_enabled'] ? 'true' : 'false' }},
        s3_key: '{{ $settings['s3_key'] }}',
        s3_secret: '{{ $settings['s3_secret'] }}',
        s3_region: '{{ $settings['s3_region'] }}',
        s3_bucket: '{{ $settings['s3_bucket'] }}',
        s3_endpoint: '{{ $settings['s3_endpoint'] }}',
        // Theme settings
        theme_primary_color: '{{ $settings['theme_primary_color'] }}',
        theme_sidebar_bg: '{{ $settings['theme_sidebar_bg'] }}',
        theme_sidebar_text: '{{ $settings['theme_sidebar_text'] }}',
        theme_sidebar_active: '{{ $settings['theme_sidebar_active'] }}',
        theme_sidebar_active: '{{ $settings['theme_sidebar_active'] }}',
        theme_sidebar_hover_bg: '{{ $settings['theme_sidebar_hover_bg'] ?? '#ffffff1a' }}',
        theme_sidebar_hover_text: '{{ $settings['theme_sidebar_hover_text'] ?? '#ffffff' }}',
        theme_active_font_color: '{{ $settings['theme_active_font_color'] ?? '#ffffff' }}',
        theme_border_radius: '{{ $settings['theme_border_radius'] }}',
        theme_spacing: '{{ $settings['theme_spacing'] }}',
        theme_font_family: '{{ $settings['theme_font_family'] }}',
        theme_font_size: '{{ $settings['theme_font_size'] }}',
        saving: false,
        testing: false,
        testResult: null,
        
        // Preset themes
        presets: {
            light: {
                theme_primary_color: '#3B82F6',
                theme_sidebar_bg: '#F3F4F6',
                theme_sidebar_text: '#1F2937',
                theme_sidebar_active: '#3B82F6',
                theme_border_radius: '0.5rem',
                theme_spacing: '1rem',
                theme_font_family: 'Inter, system-ui, sans-serif',
                theme_font_size: '14px'
            },
            dark: {
                theme_primary_color: '#3B82F6',
                theme_sidebar_bg: '#111827',
                theme_sidebar_text: '#F3F4F6',
                theme_sidebar_active: '#3B82F6',
                theme_border_radius: '0.5rem',
                theme_spacing: '1rem',
                theme_font_family: 'Inter, system-ui, sans-serif',
                theme_font_size: '14px'
            },
            blue: {
                theme_primary_color: '#2563EB',
                theme_sidebar_bg: '#1E40AF',
                theme_sidebar_text: '#DBEAFE',
                theme_sidebar_active: '#60A5FA',
                theme_border_radius: '0.75rem',
                theme_spacing: '1rem',
                theme_font_family: 'Inter, system-ui, sans-serif',
                theme_font_size: '14px'
            }
        },
        
        applyPreset(preset) {
            Object.assign(this, this.presets[preset]);
            this.updateCSSVariables();
        },
        
        updateCSSVariables() {
            const hexToRgb = (hex) => {
                let r = 0, g = 0, b = 0;
                // 3 digits
                if (hex.length === 4) {
                    r = parseInt(hex[1] + hex[1], 16);
                    g = parseInt(hex[2] + hex[2], 16);
                    b = parseInt(hex[3] + hex[3], 16);
                } 
                // 6 digits
                else if (hex.length === 7) {
                    r = parseInt(hex.substring(1, 3), 16);
                    g = parseInt(hex.substring(3, 5), 16);
                    b = parseInt(hex.substring(5, 7), 16);
                }
                return `${r}, ${g}, ${b}`;
            };

            document.documentElement.style.setProperty('--primary-color', this.theme_primary_color);
            document.documentElement.style.setProperty('--primary-rgb', hexToRgb(this.theme_primary_color));
            
            document.documentElement.style.setProperty('--sidebar-bg', this.theme_sidebar_bg);
            document.documentElement.style.setProperty('--sidebar-text', this.theme_sidebar_text);
            
            document.documentElement.style.setProperty('--sidebar-active', this.theme_sidebar_active);
            document.documentElement.style.setProperty('--sidebar-active-rgb', hexToRgb(this.theme_sidebar_active));
            
            document.documentElement.style.setProperty('--sidebar-active-rgb', hexToRgb(this.theme_sidebar_active));
            
            // Hover Settings
            document.documentElement.style.setProperty('--sidebar-hover-bg', this.theme_sidebar_hover_bg);
            document.documentElement.style.setProperty('--sidebar-hover-text', this.theme_sidebar_hover_text);
            
            document.documentElement.style.setProperty('--active-font-color', this.theme_active_font_color);
            document.documentElement.style.setProperty('--border-radius', this.theme_border_radius);
            document.documentElement.style.setProperty('--spacing', this.theme_spacing);
            document.documentElement.style.setProperty('--font-family', this.theme_font_family);
            document.documentElement.style.setProperty('--heading-font', this.theme_font_family);
        },
        
        async saveSettings() {
            this.saving = true;
            try {
                const response = await fetch(`${window.apiBaseUrl}/settings`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        compress_images: this.compress_images,
                        compression_quality: this.compression_quality,
                        convert_to_webp: this.convert_to_webp,
                        s3_enabled: this.s3_enabled,
                        s3_key: this.s3_key,
                        s3_secret: this.s3_secret,
                        s3_region: this.s3_region,
                        s3_bucket: this.s3_bucket,
                        s3_endpoint: this.s3_endpoint,
                        // Theme settings
                        theme_primary_color: this.theme_primary_color,
                        theme_sidebar_bg: this.theme_sidebar_bg,
                        theme_sidebar_text: this.theme_sidebar_text,
                        theme_sidebar_active: this.theme_sidebar_active,
                        theme_sidebar_hover_bg: this.theme_sidebar_hover_bg,
                        theme_sidebar_hover_text: this.theme_sidebar_hover_text,
                        theme_active_font_color: this.theme_active_font_color,
                        theme_border_radius: this.theme_border_radius,
                        theme_spacing: this.theme_spacing,
                        theme_font_family: this.theme_font_family,
                        theme_font_size: this.theme_font_size
                    })
                });
                
                const data = await response.json();
                if (response.ok) {
                    alert('Settings saved successfully!');
                } else {
                    alert(data.message || 'Failed to save settings');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred');
            } finally {
                this.saving = false;
            }
        },
        
        async testS3Connection() {
            this.testing = true;
            this.testResult = null;
            try {
                const response = await fetch(`${window.apiBaseUrl}/settings/test-s3`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        s3_key: this.s3_key,
                        s3_secret: this.s3_secret,
                        s3_region: this.s3_region,
                        s3_bucket: this.s3_bucket
                    })
                });
                
                const data = await response.json();
                this.testResult = data;
            } catch (error) {
                this.testResult = { success: false, message: 'Connection failed' };
            } finally {
                this.testing = false;
            }
        }
    }">
        
        <!-- S3 Storage Settings -->
        <div class="glass-panel rounded-2xl p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Amazon S3 Storage</h3>
            <div class="space-y-6">
                
                <!-- Enable S3 Toggle -->
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700">Enable S3 Storage</label>
                        <p class="text-sm text-gray-500">Store all new uploads in Amazon S3</p>
                    </div>
                    <button @click="s3_enabled = !s3_enabled" 
                            type="button" 
                            :class="s3_enabled ? 'theme-bg-primary' : 'bg-gray-200'"
                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 theme-ring-primary focus:ring-offset-2">
                        <span :class="s3_enabled ? 'translate-x-5' : 'translate-x-0'"
                              class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                    </button>
                </div>

                <!-- S3 Configuration (shown when enabled) -->
                <div x-show="s3_enabled" class="pl-4 border-l-2 border-gray-200 space-y-4">
                    
                    <!-- Access Key -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Access Key ID</label>
                        <input type="text" 
                               x-model="s3_key"
                               placeholder="AKIAIOSFODNN7EXAMPLE"
                               class="w-full px-3 py-2 bg-white/50 backdrop-blur-sm border border-gray-300/50 rounded-lg focus:outline-none focus:ring-2 theme-ring-primary theme-border-primary transition-all">
                    </div>

                    <!-- Secret Key -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Secret Access Key</label>
                        <input type="password" 
                               x-model="s3_secret"
                               placeholder="wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY"
                               class="w-full px-3 py-2 bg-white/50 backdrop-blur-sm border border-gray-300/50 rounded-lg focus:outline-none focus:ring-2 theme-ring-primary theme-border-primary transition-all">
                    </div>

                    <!-- Region -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Region</label>
                        <select x-model="s3_region"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 theme-ring-primary theme-border-primary">
                            <option value="us-east-1">US East (N. Virginia)</option>
                            <option value="us-east-2">US East (Ohio)</option>
                            <option value="us-west-1">US West (N. California)</option>
                            <option value="us-west-2">US West (Oregon)</option>
                            <option value="eu-west-3">EU West 3</option>
                            <option value="eu-west-1">EU (Ireland)</option>
                            <option value="eu-central-1">EU (Frankfurt)</option>
                            <option value="ap-southeast-1">Asia Pacific (Singapore)</option>
                            <option value="ap-southeast-2">Asia Pacific (Sydney)</option>
                            <option value="ap-northeast-1">Asia Pacific (Tokyo)</option>
                        </select>
                    </div>

                    <!-- Bucket Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bucket Name</label>
                        <input type="text" 
                               x-model="s3_bucket"
                               placeholder="my-bucket-name"
                               class="w-full px-3 py-2 bg-white/50 backdrop-blur-sm border border-gray-300/50 rounded-lg focus:outline-none focus:ring-2 theme-ring-primary theme-border-primary transition-all">
                    </div>

                    <!-- Custom Endpoint (Optional) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Custom Endpoint (Optional)</label>
                        <input type="text" 
                               x-model="s3_endpoint"
                               placeholder="https://s3-compatible-service.com"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 theme-ring-primary theme-border-primary">
                        <p class="text-xs text-gray-500 mt-1">For S3-compatible services like DigitalOcean Spaces, Wasabi, etc.</p>
                    </div>

                    <!-- Test Connection Button -->
                    <div>
                        <button @click="testS3Connection()" 
                                :disabled="testing"
                                class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg disabled:opacity-50">
                            <span x-text="testing ? 'Testing...' : 'Test Connection'"></span>
                        </button>
                        
                        <!-- Test Result -->
                        <div x-show="testResult" class="mt-2">
                            <div x-show="testResult && testResult.success" class="text-green-600 text-sm flex items-center">
                                <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span x-text="testResult.message"></span>
                            </div>
                            <div x-show="testResult && !testResult.success" class="text-red-600 text-sm flex items-center">
                                <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                                <span x-text="testResult.message"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Theme Customization -->
        <div class="glass-panel rounded-2xl p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Theme Customization</h3>
            
            <!-- Presets -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Theme Presets</label>
                <div class="flex space-x-3">
                    <button @click="applyPreset('light')" class="px-4 py-2 border rounded hover:bg-gray-50 text-sm">Light</button>
                    <button @click="applyPreset('dark')" class="px-4 py-2 border rounded hover:bg-gray-50 text-sm">Dark</button>
                    <button @click="applyPreset('blue')" class="px-4 py-2 border rounded hover:bg-gray-50 text-sm">Blue</button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Colors -->
                <div class="space-y-4">
                    <h4 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">Colors</h4>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Primary Color</label>
                        <div class="flex items-center space-x-2">
                            <input type="color" x-model="theme_primary_color" @input="updateCSSVariables()" class="h-8 w-8 rounded cursor-pointer border-0 p-0">
                            <input type="text" x-model="theme_primary_color" @input="updateCSSVariables()" class="text-sm border-gray-300 rounded-md w-28">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Sidebar Background</label>
                        <div class="flex items-center space-x-2">
                            <input type="color" x-model="theme_sidebar_bg" @input="updateCSSVariables()" class="h-8 w-8 rounded cursor-pointer border-0 p-0">
                            <input type="text" x-model="theme_sidebar_bg" @input="updateCSSVariables()" class="text-sm border-gray-300 rounded-md w-28">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Sidebar Text</label>
                        <div class="flex items-center space-x-2">
                            <input type="color" x-model="theme_sidebar_text" @input="updateCSSVariables()" class="h-8 w-8 rounded cursor-pointer border-0 p-0">
                            <input type="text" x-model="theme_sidebar_text" @input="updateCSSVariables()" class="text-sm border-gray-300 rounded-md w-28">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Sidebar Hover Background</label>
                        <div class="flex items-center space-x-2">
                            <input type="color" x-model="theme_sidebar_hover_bg" @input="updateCSSVariables()" class="h-8 w-8 rounded cursor-pointer border-0 p-0">
                            <!-- Helper for opacity hex codes might be needed, but sticking to solid/alpha-capable picker dependent on browser -->
                            <input type="text" x-model="theme_sidebar_hover_bg" @input="updateCSSVariables()" class="text-sm border-gray-300 rounded-md w-28" placeholder="#ffffff1a">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Sidebar Hover Text</label>
                        <div class="flex items-center space-x-2">
                            <input type="color" x-model="theme_sidebar_hover_text" @input="updateCSSVariables()" class="h-8 w-8 rounded cursor-pointer border-0 p-0">
                            <input type="text" x-model="theme_sidebar_hover_text" @input="updateCSSVariables()" class="text-sm border-gray-300 rounded-md w-28">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Sidebar Active Item Info</label>
                        <div class="flex items-center space-x-2">
                            <input type="color" x-model="theme_sidebar_active" @input="updateCSSVariables()" class="h-8 w-8 rounded cursor-pointer border-0 p-0">
                            <input type="text" x-model="theme_sidebar_active" @input="updateCSSVariables()" class="text-sm border-gray-300 rounded-md w-28">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Sidebar Active Text</label>
                        <div class="flex items-center space-x-2">
                            <input type="color" x-model="theme_active_font_color" @input="updateCSSVariables()" class="h-8 w-8 rounded cursor-pointer border-0 p-0">
                            <input type="text" x-model="theme_active_font_color" @input="updateCSSVariables()" class="text-sm border-gray-300 rounded-md w-28">
                        </div>
                    </div>
                </div>

                <!-- Appearance -->
                <div class="space-y-4">
                    <h4 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">Appearance</h4>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Border Radius</label>
                        <select x-model="theme_border_radius" @change="updateCSSVariables()" class="w-full border-gray-300 rounded-md">
                            <option value="0px">None</option>
                            <option value="0.25rem">Small</option>
                            <option value="0.5rem">Medium</option>
                            <option value="0.75rem">Large</option>
                            <option value="1rem">Extra Large</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Spacing / Padding</label>
                        <select x-model="theme_spacing" @change="updateCSSVariables()" class="w-full border-gray-300 rounded-md">
                            <option value="0.75rem">Compact</option>
                            <option value="1rem">Normal</option>
                            <option value="1.5rem">Spacious</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Font Family</label>
                        <input type="text" x-model="theme_font_family" @input="updateCSSVariables()" class="w-full border-gray-300 rounded-md text-sm" placeholder="Inter, sans-serif">
                    </div>
                </div>
            </div>
            
            <!-- Save Button -->
            <div class="pt-4 border-t border-gray-200 mt-6">
                <button @click="saveSettings()" 
                        :disabled="saving"
                        class="w-full theme-bg-primary hover:opacity-90 text-white px-4 py-2 rounded-lg font-medium disabled:opacity-50">
                    <span x-text="saving ? 'Saving...' : 'Save Settings'"></span>
                </button>
            </div>
        </div>

        <!-- Image Optimization Settings -->
        <div class="glass-panel rounded-2xl p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Image Optimization</h3>
            <div class="space-y-6">
                
                <!-- Compress Images Toggle -->
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700">Compress Images</label>
                        <p class="text-sm text-gray-500">Automatically compress uploaded images to reduce file size</p>
                    </div>
                    <button @click="compress_images = !compress_images" 
                            type="button" 
                            :class="compress_images ? 'theme-bg-primary' : 'bg-gray-200'"
                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 theme-ring-primary focus:ring-offset-2">
                        <span :class="compress_images ? 'translate-x-5' : 'translate-x-0'"
                              class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                    </button>
                </div>

                <!-- Compression Quality Slider -->
                <div x-show="compress_images" class="pl-4 border-l-2 border-gray-200">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Compression Quality: <span x-text="compression_quality + '%'"></span>
                    </label>
                    <input type="range" 
                           x-model="compression_quality" 
                           min="1" 
                           max="100" 
                           class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer">
                    <div class="flex justify-between text-xs text-gray-500 mt-1">
                        <span>Lower size</span>
                        <span>Higher quality</span>
                    </div>
                </div>

                <!-- Convert to WebP Toggle -->
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700">Convert to WebP</label>
                        <p class="text-sm text-gray-500">Automatically convert images to WebP format for better compression</p>
                    </div>
                    <button @click="convert_to_webp = !convert_to_webp" 
                            type="button" 
                            :class="convert_to_webp ? 'theme-bg-primary' : 'bg-gray-200'"
                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 theme-ring-primary focus:ring-offset-2">
                        <span :class="convert_to_webp ? 'translate-x-5' : 'translate-x-0'"
                              class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                    </button>
                </div>

                <!-- Save Button -->
                <div class="pt-4 border-t border-gray-200">
                    <button @click="saveSettings()" 
                            :disabled="saving"
                            class="w-full theme-bg-primary hover:opacity-90 text-white px-4 py-2 rounded-lg font-medium disabled:opacity-50">
                        <span x-text="saving ? 'Saving...' : 'Save Settings'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
