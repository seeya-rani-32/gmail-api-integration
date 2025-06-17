@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto mt-10 p-6 bg-white shadow rounded-2xl">

    <!-- 🔙 Back to Dashboard -->
    <a href="{{ route('dashboard') }}"
        class="inline-block bg-blue-600 text-white text-sm font-semibold px-4 py-2 rounded-lg shadow hover:bg-blue-700 transition mb-3">
        ← Back to Dashboard
    </a>
    <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">✉️ Compose Email</h2>

    @if(session('success'))
    <div class="text-green-600 font-semibold mb-4">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="text-red-600 font-semibold mb-4">
        {{ session('error') }}
    </div>
    @endif

    <form action="{{ route('gmail.send') }}" method="POST">
        @csrf

        <div class="mb-4">
            <label class="block text-gray-700">To:</label>
            <input type="email" name="to" class="w-full mt-1 p-2 border rounded-md" required>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700">Subject:</label>
            <input type="text" name="subject" class="w-full mt-1 p-2 border rounded-md" required>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700">Message:</label>
            <textarea name="message" rows="6" class="w-full mt-1 p-2 border rounded-md" required></textarea>
        </div>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Send Email</button>
    </form>
</div>
@endsection