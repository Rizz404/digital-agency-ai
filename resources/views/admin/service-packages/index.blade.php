<x-layouts.admin title="Catalog">
    <x-slot:actions>
        <x-ui.link-button :href="route('admin.service-packages.create')" size="sm">Add service package</x-ui.link-button>
    </x-slot:actions>

    <x-ui.table :columns="['Name', 'Category', 'Price', 'Status', '']" :empty="$servicePackages->isEmpty()" empty-text="No service packages yet.">
        @foreach ($servicePackages as $servicePackage)
            <tr>
                <td class="px-4 py-3 font-medium text-text">{{ $servicePackage->name }}@if ($servicePackage->is_featured)<x-ui.badge tone="warning" class="ml-2">Featured</x-ui.badge>@endif</td>
                <td class="px-4 py-3 text-muted">{{ \Illuminate\Support\Str::headline($servicePackage->category) }}</td>
                <td class="px-4 py-3">{{ $agency->currency }} {{ number_format((float) $servicePackage->price, 0, ',', '.') }}</td>
                <td class="px-4 py-3">
                    <x-ui.badge :tone="$servicePackage->is_active ? 'success' : 'neutral'">{{ $servicePackage->is_active ? 'Active' : 'Hidden' }}</x-ui.badge>
                    @if ($servicePackage->is_sold_out)
                        <x-ui.badge tone="danger" class="ml-1">Sold out</x-ui.badge>
                    @endif
                </td>
                <td class="px-4 py-3 text-right">
                    <a href="{{ route('admin.service-packages.edit', $servicePackage) }}" class="text-muted hover:underline">Edit</a>
                    <x-ui.confirm-form :action="route('admin.service-packages.destroy', $servicePackage)" message="Delete this service package?" confirm-label="Delete" trigger-class="ml-3 text-danger hover:underline">Delete</x-ui.confirm-form>
                </td>
            </tr>
        @endforeach
    </x-ui.table>
</x-layouts.admin>
