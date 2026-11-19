<?php

namespace App\Http\Controllers;

use App\Models\BuilderPortfolio;
use App\Models\User;
use App\Models\UserActivity;
use App\Services\PortfolioInsights;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['q' => 'nullable|string|max:150']);
        $search = trim($request->query('q', ''));
        $timezone = config('app.analytics_timezone');
        $now = now($timezone);
        $active = fn ($start, $end) => UserActivity::whereBetween('created_at', [$start->utc(), $end->utc()])
            ->whereHas('user', fn ($q) => $q->where('role', 'user'))->distinct()->count('user_id');
        $stats = [
            'Hari ini' => $active($now->copy()->startOfDay(), $now->copy()->endOfDay()),
            'Minggu ini' => $active($now->copy()->startOfWeek(), $now->copy()->endOfWeek()),
            'Bulan ini' => $active($now->copy()->startOfMonth(), $now->copy()->endOfMonth()),
            'Tahun ini' => $active($now->copy()->startOfYear(), $now->copy()->endOfYear()),
        ];
        $online = $active($now->copy()->subMinutes(5), $now->copy());
        $daily = collect(range(6, 0))->map(function ($days) use ($now, $active) {
            $day = $now->copy()->subDays($days);
            return ['date' => $day->format('d/m'), 'count' => $active($day->copy()->startOfDay(), $day->copy()->endOfDay())];
        });
        $query = User::where('role', 'user')->withCount('portfolios')
            ->withMax('activities', 'created_at')->with(['portfolios' => fn ($q) => $q->latest('updated_at')]);
        if ($search !== '') {
            // Escape LIKE wildcards: a search for '%' must not expose all users.
            $like = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $search).'%';
            $query->where(function ($q) use ($like) {
                $q->whereRaw("name LIKE ? ESCAPE '!'", [$like])
                    ->orWhereRaw("email LIKE ? ESCAPE '!'", [$like])
                    ->orWhereHas('portfolios', function ($p) use ($like) {
                        $p->where(function ($fields) use ($like) {
                            $grammar = $fields->getQuery()->getGrammar();
                            foreach (['content->personal->name', 'content->personal->email'] as $column) {
                                $fields->orWhereRaw($grammar->wrap($column)." LIKE ? ESCAPE '!'", [$like]);
                            }
                        });
                    });
            });
        }
        $users = $query->latest()->paginate(12)->withQueryString();
        $totals = ['users' => User::where('role', 'user')->count(), 'portfolios' => BuilderPortfolio::count(),
            'completed' => BuilderPortfolio::whereNotNull('completed_at')->count()];
        $activities = UserActivity::with('user')->whereHas('user', fn ($q) => $q->where('role', 'user'))
            ->where('action', '!=', 'heartbeat')->latest('id')->limit(12)->get();

        return response()->view('admin.index', compact('users', 'search', 'stats', 'online', 'daily', 'totals', 'activities', 'timezone'))
            ->header('Cache-Control', 'private, no-store');
    }

    public function show(User $user, PortfolioInsights $insights)
    {
        abort_unless($user->role === 'user', 404);
        $portfolios = $user->portfolios()->latest('updated_at')->get();
        $reports = $portfolios->mapWithKeys(fn ($p) => [$p->id => $insights->analyze($p->content)]);
        $activities = $user->activities()->latest('id')->paginate(20)->withQueryString();

        return response()->view('admin.show', compact('user', 'portfolios', 'reports', 'activities'))
            ->header('Cache-Control', 'private, no-store');
    }
}
