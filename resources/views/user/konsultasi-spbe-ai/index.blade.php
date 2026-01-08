@extends('layouts.authenticated')
@section('title', '- Konsultasi SPBE Berbasis AI')
@section('header-title', 'Konsultasi SPBE Berbasis AI')

@section('content')
<div class="container mx-auto px-4 max-w-6xl">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-purple-700">Konsultasi SPBE Berbasis AI</h1>
        <p class="text-gray-600 mt-2">Asisten SPBE Kalimantan Utara - Powered by Artificial Intelligence</p>
    </div>

    <div class="bg-gradient-to-br from-purple-50 via-blue-50 to-indigo-50 rounded-2xl shadow-xl p-8 mb-6">
        <div class="grid md:grid-cols-2 gap-8 items-center">
            <!-- Left Column: Animation -->
            <div class="relative">
                <div class="flex justify-center items-center">
                    <!-- AI Robot Animation -->
                    <div class="relative ai-robot-container">
                        <!-- Robot Body -->
                        <div class="robot-body">
                            <!-- Head -->
                            <div class="robot-head">
                                <div class="robot-antenna"></div>
                                <div class="robot-eyes">
                                    <div class="robot-eye left"></div>
                                    <div class="robot-eye right"></div>
                                </div>
                                <div class="robot-mouth"></div>
                            </div>

                            <!-- Body -->
                            <div class="robot-torso">
                                <div class="robot-screen">
                                    <div class="robot-screen-line"></div>
                                    <div class="robot-screen-line"></div>
                                    <div class="robot-screen-line"></div>
                                </div>
                            </div>

                            <!-- Arms -->
                            <div class="robot-arm left"></div>
                            <div class="robot-arm right"></div>
                        </div>

                        <!-- Floating Elements -->
                        <div class="floating-element" style="top: 10%; left: -20%; animation-delay: 0s;">
                            <svg class="w-8 h-8 text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="floating-element" style="top: 60%; right: -15%; animation-delay: 1s;">
                            <svg class="w-10 h-10 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="floating-element" style="top: 30%; right: -25%; animation-delay: 2s;">
                            <svg class="w-6 h-6 text-indigo-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Information -->
            <div>
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Selamat Datang di Asisten SPBE AI</h2>
                <p class="text-gray-700 mb-6 leading-relaxed">
                    Asisten SPBE Kalimantan Utara adalah chatbot berbasis kecerdasan buatan yang siap membantu Anda dalam hal:
                </p>

                <ul class="space-y-3 mb-8">
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-gray-700">Konsultasi implementasi SPBE (Sistem Pemerintahan Berbasis Elektronik)</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-gray-700">Informasi kebijakan dan regulasi terkait digitalisasi pemerintahan</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-gray-700">Panduan teknis dan best practices dalam transformasi digital</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-green-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-gray-700">Rekomendasi solusi teknologi untuk kebutuhan instansi</span>
                    </li>
                </ul>

                <a href="https://chatgpt.com/g/g-68d4e245a8348191b95faca91144169f-asisten-spbe-kalimantan-utara"
                   target="_blank"
                   class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold rounded-lg shadow-lg transform transition duration-200 hover:scale-105">
                    <svg class="w-6 h-6 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd"/>
                    </svg>
                    Akses Disini
                </a>

                <p class="text-sm text-gray-500 mt-4">
                    <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    Layanan ini akan membuka halaman ChatGPT khusus Asisten SPBE Kalimantan Utara
                </p>
            </div>
        </div>
    </div>

    <!-- Additional Info Cards -->
    <div class="grid md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-purple-500">
            <div class="flex items-center mb-4">
                <div class="bg-purple-100 rounded-full p-3 mr-4">
                    <svg class="w-8 h-8 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-800">Berbasis Pengetahuan</h3>
            </div>
            <p class="text-gray-600 text-sm">Dilatih dengan data dan regulasi SPBE terkini untuk memberikan jawaban yang akurat</p>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-blue-500">
            <div class="flex items-center mb-4">
                <div class="bg-blue-100 rounded-full p-3 mr-4">
                    <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-800">Respons Cepat</h3>
            </div>
            <p class="text-gray-600 text-sm">Dapatkan jawaban instan untuk pertanyaan Anda kapan saja, 24/7</p>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-indigo-500">
            <div class="flex items-center mb-4">
                <div class="bg-indigo-100 rounded-full p-3 mr-4">
                    <svg class="w-8 h-8 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-800">Inovatif</h3>
            </div>
            <p class="text-gray-600 text-sm">Menggunakan teknologi AI terdepan untuk mendukung transformasi digital</p>
        </div>
    </div>
</div>

<style>
    /* AI Robot Animation Styles */
    .ai-robot-container {
        width: 250px;
        height: 300px;
        position: relative;
        animation: float 3s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-20px); }
    }

    .robot-body {
        position: relative;
        width: 100%;
        height: 100%;
    }

    /* Robot Head */
    .robot-head {
        position: absolute;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        width: 120px;
        height: 100px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
    }

    .robot-antenna {
        position: absolute;
        top: -25px;
        left: 50%;
        transform: translateX(-50%);
        width: 4px;
        height: 25px;
        background: #667eea;
        animation: antenna-blink 2s ease-in-out infinite;
    }

    .robot-antenna::after {
        content: '';
        position: absolute;
        top: -8px;
        left: 50%;
        transform: translateX(-50%);
        width: 12px;
        height: 12px;
        background: #f59e0b;
        border-radius: 50%;
        animation: light-pulse 1.5s ease-in-out infinite;
    }

    @keyframes antenna-blink {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }

    @keyframes light-pulse {
        0%, 100% {
            box-shadow: 0 0 10px #f59e0b;
            transform: translateX(-50%) scale(1);
        }
        50% {
            box-shadow: 0 0 20px #f59e0b, 0 0 30px #fbbf24;
            transform: translateX(-50%) scale(1.2);
        }
    }

    /* Robot Eyes */
    .robot-eyes {
        position: absolute;
        top: 35px;
        left: 50%;
        transform: translateX(-50%);
        width: 80px;
        display: flex;
        justify-content: space-between;
    }

    .robot-eye {
        width: 20px;
        height: 20px;
        background: white;
        border-radius: 50%;
        position: relative;
        animation: blink 3s infinite;
    }

    .robot-eye::after {
        content: '';
        position: absolute;
        width: 10px;
        height: 10px;
        background: #1e40af;
        border-radius: 50%;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        animation: eye-move 4s ease-in-out infinite;
    }

    @keyframes blink {
        0%, 48%, 52%, 100% { height: 20px; }
        50% { height: 2px; }
    }

    @keyframes eye-move {
        0%, 100% { left: 40%; }
        25% { left: 60%; }
        50% { left: 50%; }
        75% { left: 40%; }
    }

    /* Robot Mouth */
    .robot-mouth {
        position: absolute;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        width: 50px;
        height: 8px;
        background: white;
        border-radius: 10px;
        animation: smile 2s ease-in-out infinite;
    }

    @keyframes smile {
        0%, 100% { width: 50px; }
        50% { width: 60px; border-radius: 0 0 30px 30px; }
    }

    /* Robot Torso */
    .robot-torso {
        position: absolute;
        top: 130px;
        left: 50%;
        transform: translateX(-50%);
        width: 140px;
        height: 120px;
        background: linear-gradient(135deg, #818cf8 0%, #6366f1 100%);
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(99, 102, 241, 0.3);
    }

    .robot-screen {
        position: absolute;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        width: 80px;
        height: 60px;
        background: #1e293b;
        border-radius: 8px;
        padding: 10px;
    }

    .robot-screen-line {
        width: 100%;
        height: 4px;
        background: #10b981;
        margin-bottom: 6px;
        border-radius: 2px;
        animation: screen-scroll 1.5s ease-in-out infinite;
    }

    .robot-screen-line:nth-child(2) { animation-delay: 0.2s; }
    .robot-screen-line:nth-child(3) { animation-delay: 0.4s; }

    @keyframes screen-scroll {
        0%, 100% { opacity: 0.3; width: 40%; }
        50% { opacity: 1; width: 100%; }
    }

    /* Robot Arms */
    .robot-arm {
        position: absolute;
        top: 150px;
        width: 30px;
        height: 80px;
        background: linear-gradient(135deg, #818cf8 0%, #6366f1 100%);
        border-radius: 15px;
    }

    .robot-arm.left {
        left: 25px;
        animation: wave-left 2s ease-in-out infinite;
    }

    .robot-arm.right {
        right: 25px;
        animation: wave-right 2s ease-in-out infinite;
    }

    @keyframes wave-left {
        0%, 100% { transform: rotate(0deg); }
        50% { transform: rotate(-15deg); }
    }

    @keyframes wave-right {
        0%, 100% { transform: rotate(0deg); }
        50% { transform: rotate(15deg); }
    }

    /* Floating Elements */
    .floating-element {
        position: absolute;
        animation: float-around 3s ease-in-out infinite;
    }

    @keyframes float-around {
        0%, 100% {
            transform: translateY(0px) translateX(0px);
            opacity: 0.5;
        }
        50% {
            transform: translateY(-20px) translateX(10px);
            opacity: 1;
        }
    }
</style>
@endsection
