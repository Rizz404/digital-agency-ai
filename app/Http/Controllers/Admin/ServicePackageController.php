<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ResolvesCurrentAgency;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ServicePackageRequest;
use App\Models\ServicePackage;
use App\Modules\Catalog\Actions\SaveServicePackage;
use App\Modules\Catalog\Enums\ServiceCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ServicePackageController extends Controller
{
    use ResolvesCurrentAgency;

    public function index(Request $request): View
    {
        $agency = $this->currentAgency($request);

        $servicePackages = $agency->servicePackages()->orderBy('category')->orderBy('sort_order')->get();

        return view('admin.service-packages.index', compact('agency', 'servicePackages'));
    }

    public function create(Request $request): View
    {
        $agency = $this->currentAgency($request);

        return view('admin.service-packages.form', [
            'agency' => $agency,
            'servicePackage' => new ServicePackage(['category' => ServicePackage::CATEGORY_BRANDING, 'is_active' => true]),
            'categories' => ServiceCategory::values(),
        ]);
    }

    public function store(ServicePackageRequest $request, SaveServicePackage $save): RedirectResponse
    {
        $servicePackage = $save->handle($this->currentAgency($request), $request->servicePackageAttributes());

        return redirect()->route('admin.service-packages.index')->with('status', "Catalog item \"{$servicePackage->name}\" created.");
    }

    public function edit(Request $request, ServicePackage $servicePackage): View
    {
        $agency = $this->currentAgency($request);
        Gate::authorize('update', $servicePackage);

        return view('admin.service-packages.form', [
            'agency' => $agency,
            'servicePackage' => $servicePackage,
            'categories' => ServiceCategory::values(),
        ]);
    }

    public function update(ServicePackageRequest $request, ServicePackage $servicePackage, SaveServicePackage $save): RedirectResponse
    {
        $save->handle($this->currentAgency($request), $request->servicePackageAttributes(), $servicePackage);

        return redirect()->route('admin.service-packages.index')->with('status', "Catalog item \"{$servicePackage->name}\" updated.");
    }

    public function destroy(Request $request, ServicePackage $servicePackage): RedirectResponse
    {
        $this->currentAgency($request);
        Gate::authorize('delete', $servicePackage);

        $servicePackage->delete();

        return redirect()->route('admin.service-packages.index')->with('status', 'Catalog item deleted.');
    }
}
