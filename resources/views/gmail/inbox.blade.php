@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-6 text-center p-4">📥 Gmail Inbox</h1>

    <!-- 🔙 Back to Dashboard -->
    <a href="{{ route('dashboard') }}"
        class="inline-block bg-blue-600 text-white text-sm font-semibold px-4 py-2 rounded-lg shadow hover:bg-blue-700 transition mb-3">
        ← Back to Dashboard
    </a>

    @if(count($emails))
    <div class="overflow-x-auto bg-white shadow-md rounded-2xl">
        <table class="min-w-full table-auto border-collapse border border-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 border-b">Sr. No.</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 border-b">From</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 border-b">Subject</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 border-b">Body</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($emails as $index => $email)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $index + 1 }}</td>
                    <td class="px-6 py-4 text-sm text-gray-800">{{ $email['from'] }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900 font-medium">{{ $email['subject'] }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900 font-medium">{{ $email['body'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="text-center text-gray-500 text-lg">No emails found.</div>
    @endif
</div>
@endsection