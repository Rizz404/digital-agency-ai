<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ResolvesCurrentAgency;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReplyToHandoverRequest;
use App\Models\HandoverRequest;
use App\Modules\Conversation\Actions\ReplyToHandover;
use App\Modules\Conversation\Actions\ResolveHandover;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class HandoverController extends Controller
{
    use ResolvesCurrentAgency;

    public function index(Request $request): View
    {
        $agency = $this->currentAgency($request);

        $status = $request->query('status', HandoverRequest::STATUS_OPEN);

        $handovers = HandoverRequest::whereHas('conversation', fn ($q) => $q->where('agency_id', $agency->id))
            ->where('status', $status)
            ->with('conversation')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.handovers.index', compact('agency', 'handovers', 'status'));
    }

    public function show(Request $request, HandoverRequest $handover): View
    {
        $agency = $this->currentAgency($request);
        Gate::authorize('view', $handover);

        $handover->load('conversation.messages');

        return view('admin.handovers.show', compact('agency', 'handover'));
    }

    public function reply(ReplyToHandoverRequest $request, HandoverRequest $handover, ReplyToHandover $replyToHandover): RedirectResponse
    {
        $this->currentAgency($request);

        $replyToHandover->handle($handover, $request->validated('message'));

        return back()->with('status', 'Reply sent to the guest.');
    }

    public function resolve(Request $request, HandoverRequest $handover, ResolveHandover $resolveHandover): RedirectResponse
    {
        $this->currentAgency($request);
        Gate::authorize('update', $handover);

        $resolveHandover->handle($handover, $request->user());

        return redirect()->route('admin.handovers.index')->with('status', 'Handover resolved — the AI Digital Strategist is back in the conversation.');
    }
}
