<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demo - Examination Platform Showcase</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .feature-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .demo-button {
            background: linear-gradient(45deg, #ff6b6b, #feca57);
            transition: all 0.3s ease;
        }
        .demo-button:hover {
            background: linear-gradient(45deg, #ff5252, #ffc107);
            transform: scale(1.05);
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen" x-data="demoApp()">
    <!-- Header -->
    <header class="gradient-bg text-white">
        <div class="container mx-auto px-4 py-8">
            <div class="text-center">
                <h1 class="text-5xl font-bold mb-4">🎯 Examination Platform Demo</h1>
                <p class="text-xl opacity-90">Experience our comprehensive examination platform with 5 different question types</p>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 py-12">
        <!-- Demo Access Section -->
        <div class="max-w-2xl mx-auto mb-16">
            <div class="bg-white rounded-lg shadow-xl p-8 text-center">
                <h2 class="text-3xl font-bold mb-6">🚀 Start Your Demo Experience</h2>
                <p class="text-gray-600 mb-8">Enter the demo code below to start a fresh 5-minute examination that showcases all our platform capabilities.</p>
                
                <div class="space-y-6">
                    <div>
                        <input 
                            type="text" 
                            x-model="demoCode"
                            @keyup.enter="startDemo"
                            placeholder="Enter Demo Code"
                            class="w-full p-4 text-2xl text-center font-mono border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"
                            :class="{ 'border-red-500': error }"
                        >
                        <p class="text-sm text-gray-500 mt-2">Hint: The demo code is "DEMO"</p>
                    </div>
                    
                    <button 
                        @click="startDemo"
                        :disabled="loading"
                        class="demo-button text-white px-8 py-4 rounded-lg font-bold text-xl w-full"
                        :class="{ 'opacity-50 cursor-not-allowed': loading }"
                    >
                        <span x-show="!loading">🎮 Start Demo Exam</span>
                        <span x-show="loading" class="flex items-center justify-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Initializing Demo...
                        </span>
                    </button>
                    
                    <div x-show="error" class="text-red-600 text-center" x-text="errorMessage"></div>
                    <div x-show="success" class="text-green-600 text-center" x-text="successMessage"></div>
                </div>
            </div>
        </div>

        <!-- Platform Features -->
        <div class="mb-16">
            <h2 class="text-4xl font-bold text-center mb-12">✨ What You'll Experience</h2>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8" x-show="stats">
                <div class="feature-card bg-white rounded-lg shadow-lg p-6">
                    <div class="text-center">
                        <div class="text-4xl mb-4">📝</div>
                        <h3 class="text-xl font-bold mb-3">Multiple Choice</h3>
                        <p class="text-gray-600">Interactive multiple choice questions with automatic scoring and immediate feedback.</p>
                    </div>
                </div>
                
                <div class="feature-card bg-white rounded-lg shadow-lg p-6">
                    <div class="text-center">
                        <div class="text-4xl mb-4">📄</div>
                        <h3 class="text-xl font-bold mb-3">Essay Questions</h3>
                        <p class="text-gray-600">Open-ended essay questions for detailed responses and comprehensive assessment.</p>
                    </div>
                </div>
                
                <div class="feature-card bg-white rounded-lg shadow-lg p-6">
                    <div class="text-center">
                        <div class="text-4xl mb-4">🎤</div>
                        <h3 class="text-xl font-bold mb-3">Interview Assessment</h3>
                        <p class="text-gray-600">Practical interview-style questions for real-world skill evaluation.</p>
                    </div>
                </div>
                
                <div class="feature-card bg-white rounded-lg shadow-lg p-6">
                    <div class="text-center">
                        <div class="text-4xl mb-4">🎨</div>
                        <h3 class="text-xl font-bold mb-3">Rich Media</h3>
                        <p class="text-gray-600">HTML content support with images, styling, and interactive elements.</p>
                    </div>
                </div>
                
                <div class="feature-card bg-white rounded-lg shadow-lg p-6">
                    <div class="text-center">
                        <div class="text-4xl mb-4">⏱️</div>
                        <h3 class="text-xl font-bold mb-3">Timer System</h3>
                        <p class="text-gray-600">Precise timer control with automatic submission and time management.</p>
                    </div>
                </div>
                
                <div class="feature-card bg-white rounded-lg shadow-lg p-6">
                    <div class="text-center">
                        <div class="text-4xl mb-4">🔄</div>
                        <h3 class="text-xl font-bold mb-3">Auto-Restart</h3>
                        <p class="text-gray-600">Each demo session starts completely fresh with clean state.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="max-w-4xl mx-auto" x-show="stats">
            <div class="bg-white rounded-lg shadow-xl p-8">
                <h2 class="text-3xl font-bold text-center mb-8">📊 Demo Exam Details</h2>
                <div class="grid md:grid-cols-3 gap-8">
                    <div class="text-center">
                        <div class="text-4xl font-bold text-blue-600 mb-2" x-text="stats.total_questions"></div>
                        <div class="text-gray-600">Total Questions</div>
                    </div>
                    <div class="text-center">
                        <div class="text-4xl font-bold text-green-600 mb-2" x-text="stats.duration_minutes"></div>
                        <div class="text-gray-600">Minutes Duration</div>
                    </div>
                    <div class="text-center">
                        <div class="text-4xl font-bold text-purple-600 mb-2">3+</div>
                        <div class="text-gray-600">Question Types</div>
                    </div>
                </div>
                
                <div class="mt-8 pt-8 border-t">
                    <h3 class="text-xl font-bold mb-4 text-center">🎯 Featured Capabilities</h3>
                    <div class="grid md:grid-cols-2 gap-4" x-show="stats.features_showcased">
                        <template x-for="feature in stats.features_showcased">
                            <div class="flex items-center space-x-2">
                                <div class="text-green-500">✅</div>
                                <div x-text="feature"></div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="gradient-bg text-white py-8 mt-16">
        <div class="container mx-auto px-4 text-center">
            <p class="opacity-90">Experience the future of online examinations - Powered by Advanced Technology</p>
        </div>
    </footer>

    <script>
        function demoApp() {
            return {
                demoCode: '',
                loading: false,
                error: false,
                success: false,
                errorMessage: '',
                successMessage: '',
                stats: null,

                init() {
                    this.loadStats();
                },

                async loadStats() {
                    try {
                        const response = await fetch('/demo/stats');
                        const data = await response.json();
                        if (data.stats) {
                            this.stats = data.stats;
                        }
                    } catch (e) {
                        console.log('Could not load stats:', e);
                    }
                },

                async startDemo() {
                    if (this.loading) return;
                    
                    this.loading = true;
                    this.error = false;
                    this.success = false;
                    
                    try {
                        const response = await fetch('/demo/start', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                demo_code: this.demoCode
                            })
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            this.success = true;
                            this.successMessage = data.message;
                            
                            // Redirect to exam after short delay
                            setTimeout(() => {
                                window.location.href = data.redirect_url;
                            }, 1500);
                        } else {
                            this.error = true;
                            this.errorMessage = data.message;
                        }
                    } catch (e) {
                        this.error = true;
                        this.errorMessage = 'Connection error. Please try again.';
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }
    </script>
</body>
</html>