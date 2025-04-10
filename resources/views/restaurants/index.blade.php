@extends('layouts.app')

@section('header')
<h2 class="text-3xl font-bold text-gray-900">
    🍽️ Restaurants
</h2>
@endsection

@section('content')
<div class="py-10 bg-gray-100 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="mb-6">
            <h3 class="text-2xl font-semibold text-gray-800">Top Picks For You</h3>
            <p class="text-gray-500 text-sm">Handpicked places to enjoy great food 🍜</p>
        </div>

        @if (!isset($restaurants) || $restaurants->isEmpty())
        <div class="bg-white p-6 rounded-xl shadow text-center text-gray-500">
            😔 No restaurants found.
        </div>
        @else
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach ($restaurants as $restaurant)
            <div class="bg-white rounded-xl shadow hover:shadow-md transition-all duration-200 overflow-hidden">
                <div class="h-36 bg-gray-200 flex items-center justify-center text-5xl">
                    🍔
                </div>
                <div class="p-4">
                    <h4 class="text-lg font-semibold text-gray-900 truncate">
                        {{ $restaurant->name ?? 'Unnamed' }}
                    </h4>
                    <p class="text-sm text-gray-600 mt-1 truncate">
                        📍 {{ $restaurant->address ?? 'No address' }}
                    </p>

                    @if (property_exists($restaurant, 'is_available'))
                    <div class="mt-2 text-xs font-medium">
                        <span class="px-2 py-1 rounded-full {{ $restaurant->is_available ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $restaurant->is_available ? 'Available' : 'Unavailable' }}
                        </span>
                    </div>
                    @endif

                    <a href="{{ route('restaurants.show', $restaurant->id) }}"
                        class="mt-4 inline-block text-sm font-medium text-indigo-600 hover:underline">
                        View Details →
                    </a>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        @if (isset($restaurants) && method_exists($restaurants, 'links'))
        <div class="mt-10 flex justify-center">
            {{ $restaurants->links() }}
        </div>
        @endif
    </div>
</div>
@endsection