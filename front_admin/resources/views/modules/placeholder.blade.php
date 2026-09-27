@extends('layouts.admin')

@section('content')

    <main class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <section class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-700">
                    Belum ada data {{ strtolower($module) }}.
                </div>
            </section>
        </div>
    </main>
@endsection