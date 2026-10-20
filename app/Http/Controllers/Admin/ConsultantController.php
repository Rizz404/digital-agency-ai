<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ResolvesCurrentAgency;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ConsultantRequest;
use App\Models\Consultant;
use App\Modules\Team\Actions\SaveConsultant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ConsultantController extends Controller
{
    use ResolvesCurrentAgency;

    public function index(Request $request): View
    {
        $agency = $this->currentAgency($request);

        $consultants = $agency->consultants()->withCount('images')->orderBy('sort_order')->get();

        return view('admin.consultants.index', compact('agency', 'consultants'));
    }

    public function create(Request $request): View
    {
        $agency = $this->currentAgency($request);

        return view('admin.consultants.form', [
            'agency' => $agency,
            'consultant' => new Consultant(['specialty' => Consultant::TYPE_BRAND, 'min_companies' => 1, 'max_companies' => 1, 'consultation_fee' => 0, 'is_active' => true]),
        ]);
    }

    public function store(ConsultantRequest $request, SaveConsultant $save): RedirectResponse
    {
        $consultant = $save->handle(
            $this->currentAgency($request),
            $request->consultantAttributes(),
            $request->imageIdsToDelete(),
            $request->newImages(),
        );

        return redirect()->route('admin.consultants.index')->with('status', "Consultant \"{$consultant->name}\" created.");
    }

    public function edit(Request $request, Consultant $consultant): View
    {
        $agency = $this->currentAgency($request);
        Gate::authorize('update', $consultant);

        $consultant->load('images');

        return view('admin.consultants.form', compact('agency', 'consultant'));
    }

    public function update(ConsultantRequest $request, Consultant $consultant, SaveConsultant $save): RedirectResponse
    {
        $save->handle($this->currentAgency($request), $request->consultantAttributes(), $request->imageIdsToDelete(), $request->newImages(), $consultant);

        return redirect()->route('admin.consultants.index')->with('status', "Consultant \"{$consultant->name}\" updated.");
    }

    public function destroy(Request $request, Consultant $consultant): RedirectResponse
    {
        $this->currentAgency($request);
        Gate::authorize('delete', $consultant);

        $consultant->delete();

        return redirect()->route('admin.consultants.index')->with('status', 'Consultant deleted.');
    }
}
