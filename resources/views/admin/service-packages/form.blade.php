@php($isEdit = $servicePackage->exists)

<x-layouts.admin :title="$isEdit ? 'Edit service package' : 'Add service package'">
    <form method="POST" action="{{ $isEdit ? route('admin.service-packages.update', $servicePackage) : route('admin.service-packages.store') }}" class="space-y-8">
        @csrf
        @if ($isEdit) @method('PUT') @endif

        <x-ui.card heading="Basics">
            <div class="mt-4 grid grid-cols-2 gap-4">
                <x-ui.select name="category" label="Category">
                    @foreach ($categories as $category)
                        <option value="{{ $category }}" @selected(old('category', $servicePackage->category) === $category)>{{ \Illuminate\Support\Str::headline($category) }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.input name="price" type="number" label="Estimated price" step="500" :value="old('price', $servicePackage->price)" required />
                <x-ui.input name="name" label="Name (Indonesian, default)" :value="old('name', $servicePackage->name)" required class="col-span-2" />
                <x-ui.textarea name="description" label="Description (Indonesian, default)" :value="old('description', $servicePackage->description)" class="col-span-2" />
                <x-ui.input name="translations[en][name]" label="Name (English)" :value="old('translations.en.name', $servicePackage->translations['en']['name'] ?? '')" />
                <x-ui.input name="translations[ja][name]" label="Name (Japanese)" :value="old('translations.ja.name', $servicePackage->translations['ja']['name'] ?? '')" />
                <x-ui.textarea name="translations[en][description]" label="Description (English)" :value="old('translations.en.description', $servicePackage->translations['en']['description'] ?? '')" />
                <x-ui.textarea name="translations[ja][description]" label="Description (Japanese)" :value="old('translations.ja.description', $servicePackage->translations['ja']['description'] ?? '')" />
            </div>
        </x-ui.card>

        <x-ui.card heading="Details">
            <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-4">
                <x-ui.input name="delivery" label="Engagement" :value="old('delivery', $servicePackage->delivery)" placeholder="project, monthly, sprint, retainer, workshop" />
                <x-ui.input name="estimated_days" type="number" label="Estimated working days" min="0" :value="old('estimated_days', $servicePackage->estimated_days)" />
                <x-ui.input name="sort_order" type="number" label="Sort order" :value="old('sort_order', $servicePackage->sort_order)" />
            </div>
            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-ui.input name="tags" label="Tags (comma-separated)" :value="old('tags', implode(', ', $servicePackage->tags ?? []))" placeholder="startup, growth, enterprise, signature" />
                <x-ui.input name="requirements" label="Requirements (comma-separated)" :value="old('requirements', implode(', ', $servicePackage->requirements ?? []))" placeholder="brand_brief, brand_assets, analytics_access" />
                <x-ui.input name="image_url" label="Image path or URL" :value="old('image_url', $servicePackage->image_url)" placeholder="images/agency/brand-foundations.webp or https://…" class="sm:col-span-2" />
            </div>
            <div class="mt-4 flex flex-wrap gap-6">
                <x-ui.checkbox name="is_featured" label="Featured" :checked="old('is_featured', $servicePackage->is_featured)" />
                <x-ui.checkbox name="is_sold_out" label="Intake paused" :checked="old('is_sold_out', $servicePackage->is_sold_out)" />
                <x-ui.checkbox name="is_active" label="Published (visible on the website)" :checked="old('is_active', $servicePackage->is_active)" />
            </div>
        </x-ui.card>

        <div class="flex justify-end gap-3">
            <x-ui.link-button :href="route('admin.service-packages.index')" variant="secondary">Cancel</x-ui.link-button>
            <x-ui.button type="submit">Save service package</x-ui.button>
        </div>
    </form>
</x-layouts.admin>
