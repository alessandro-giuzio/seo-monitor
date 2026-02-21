<?php

namespace App\Http\Controllers;

use App\Models\SeoTask;
use App\Models\Website;
use App\Services\SeoChecklistService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SeoChecklistController extends Controller
{
    public function index(Request $request, SeoChecklistService $checklistService): View
    {
        $websites = Website::query()->orderBy('name')->get();
        $selectedWebsite = null;
        $checklist = null;

        if ($websites->isNotEmpty()) {
            $selectedId = $request->integer('website_id') ?: $websites->first()->id;
            $selectedWebsite = Website::find($selectedId);
            if ($selectedWebsite) {
                $checklist = $checklistService->buildForWebsite($selectedWebsite);
            }
        }

        return view('checklist.index', [
            'websites' => $websites,
            'selectedWebsite' => $selectedWebsite,
            'checklist' => $checklist,
            'tasks' => $selectedWebsite
                ? $selectedWebsite->seoTasks()->limit(50)->get()
                : collect(),
        ]);
    }

    public function generateTasks(Request $request, SeoChecklistService $checklistService): RedirectResponse
    {
        $validated = $request->validate([
            'website_id' => ['required', 'exists:websites,id'],
        ]);

        $website = Website::findOrFail($validated['website_id']);
        $checklist = $checklistService->buildForWebsite($website);

        $created = 0;
        foreach (['crawlability', 'onpage', 'technical', 'international'] as $section) {
            foreach ($checklist[$section] as $item) {
                if (! in_array($item['status'], ['warn', 'fail'], true)) {
                    continue;
                }

                $exists = SeoTask::query()
                    ->where('website_id', $website->id)
                    ->where('section', $section)
                    ->where('item_label', $item['label'])
                    ->where('status', 'open')
                    ->exists();

                if ($exists) {
                    continue;
                }

                SeoTask::create([
                    'website_id' => $website->id,
                    'section' => $section,
                    'item_label' => $item['label'],
                    'title' => $item['label'],
                    'details' => $item['detail'],
                    'status' => 'open',
                    'priority' => $item['status'] === 'fail' ? 'high' : 'medium',
                    'due_date' => now()->addDays($item['status'] === 'fail' ? 3 : 7)->toDateString(),
                ]);
                $created++;
            }
        }

        return redirect()
            ->route('checklist.index', ['website_id' => $website->id])
            ->with('status', "{$created} task(s) created from checklist.");
    }

    public function completeTask(SeoTask $task): RedirectResponse
    {
        $task->update([
            'status' => 'done',
            'completed_at' => now(),
        ]);

        return redirect()
            ->route('checklist.index', ['website_id' => $task->website_id])
            ->with('status', 'Task marked as done.');
    }
}
