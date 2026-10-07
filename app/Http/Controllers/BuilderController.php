<?php

namespace App\Http\Controllers;

use App\Models\PublishedPortfolio;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BuilderController extends Controller
{
    public const STEPS = [
        'template' => 'Pilih template',
        'personal' => 'Info pribadi',
        'experience' => 'Pendidikan & pengalaman',
        'projects' => 'Keahlian & proyek',
        'preview' => 'Pratinjau & selesai',
    ];

    public function index()
    {
        return redirect()->route('builder.step', 'template');
    }

    public function step(Request $request, string $step)
    {
        abort_unless(array_key_exists($step, self::STEPS), 404);
        $draft = $request->session()->get('builder.draft', ['template' => 'minimalist']);
        if ($step === 'preview' && empty($draft['personal']['name'])) {
            return redirect()->route('builder.step', 'personal')->with('notice', 'Isi nama dan profesi Anda terlebih dahulu.');
        }

        return view('builder', [
            'step' => $step,
            'steps' => self::STEPS,
            'draft' => $draft,
            'shareUrl' => $request->session()->get('builder.share_url'),
        ]);
    }

    public function save(Request $request, string $step)
    {
        abort_unless(in_array($step, ['template', 'personal', 'experience', 'projects'], true), 404);
        $url = ['nullable', 'url:http,https', 'max:2048'];
        $rules = match ($step) {
            'template' => ['template' => ['required', Rule::in(['minimalist', 'modern', 'tech'])]],
            'personal' => [
                'personal' => 'required|array:name,role,bio,email,phone,location,website,linkedin',
                'personal.name' => 'required|string|max:100',
                'personal.role' => 'required|string|max:120',
                'personal.bio' => 'nullable|string|max:2000',
                'personal.email' => 'nullable|email|max:255',
                'personal.phone' => 'nullable|string|max:40',
                'personal.location' => 'nullable|string|max:120',
                'personal.website' => $url,
                'personal.linkedin' => $url,
            ],
            'experience' => [
                'education' => 'sometimes|array|max:20',
                'education.*' => 'array:school,degree,period',
                'education.*.school' => 'nullable|string|max:180',
                'education.*.degree' => 'nullable|string|max:180',
                'education.*.period' => 'nullable|string|max:80',
                'experience' => 'sometimes|array|max:20',
                'experience.*' => 'array:company,role,period,description',
                'experience.*.company' => 'nullable|string|max:180',
                'experience.*.role' => 'nullable|string|max:180',
                'experience.*.period' => 'nullable|string|max:80',
                'experience.*.description' => 'nullable|string|max:2000',
            ],
            'projects' => [
                'skills' => 'nullable|string|max:1000',
                'projects' => 'sometimes|array|max:20',
                'projects.*' => 'array:title,description,url',
                'projects.*.title' => 'nullable|string|max:180',
                'projects.*.description' => 'nullable|string|max:2000',
                'projects.*.url' => $url,
            ],
        };
        $data = $request->validate($rules);
        if ($step === 'experience') {
            $data['education'] = $this->rows($data['education'] ?? [], 'school', 'education');
            $data['experience'] = $this->rows($data['experience'] ?? [], 'company', 'experience');
        }
        if ($step === 'projects') {
            $data['projects'] = $this->rows($data['projects'] ?? [], 'title', 'projects');
            $data['skills'] = array_values(array_unique(array_filter(array_map('trim', explode(',', $data['skills'] ?? '')))));
        }
        $draft = array_replace($request->session()->get('builder.draft', ['template' => 'minimalist']), $data);
        $request->session()->put('builder.draft', $draft);
        // A public link remains a snapshot; editing a draft never changes it implicitly.
        $request->session()->forget('builder.share_url');
        $order = array_keys(self::STEPS);
        $next = $order[array_search($step, $order, true) + 1];

        return redirect()->route('builder.step', $next)->with('notice', 'Draft berhasil disimpan.');
    }

    private function rows(array $rows, string $required, string $group): array
    {
        $result = [];
        foreach ($rows as $index => $row) {
            if (!array_filter($row, fn ($value) => $value !== null && trim($value) !== '')) {
                continue;
            }
            if (empty(trim($row[$required] ?? ''))) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    "$group.$index.$required" => 'Lengkapi nama pada entri ini atau hapus entri yang tidak digunakan.',
                ]);
            }
            $result[] = $row;
        }

        return $result;
    }

    private function draft(Request $request): array
    {
        $draft = $request->session()->get('builder.draft', []);
        abort_if(empty($draft['personal']['name']), 422, 'Lengkapi info pribadi terlebih dahulu.');

        return $draft;
    }

    public function download(Request $request)
    {
        $draft = $this->draft($request);
        $html = view('builder.portfolio', ['draft' => $draft])->render();
        $filename = (Str::slug($draft['personal']['name']) ?: 'portofolio').'-portofolio.html';

        return response($html)->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
    }

    public function publish(Request $request)
    {
        $draft = $this->draft($request);
        $token = $request->session()->get('builder.published_token', (string) Str::uuid());
        PublishedPortfolio::updateOrCreate(['token' => $token], ['content' => $draft]);
        $request->session()->put('builder.published_token', $token);
        $request->session()->put('builder.share_url', route('builder.public', $token));

        return redirect()->route('builder.step', 'preview')->with('notice', 'Portofolio dipublikasikan. Tautan publik siap dibagikan.');
    }

    public function show(string $token)
    {
        $portfolio = PublishedPortfolio::where('token', $token)->firstOrFail();

        return response()->view('builder.portfolio', ['draft' => $portfolio->content])
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
