<?php

namespace App\Http\Controllers;

use App\Models\Portfolio;
use Illuminate\Http\Request;

class PortfolioController extends Controller
{
    /**
     * Menampilkan semua portfolio.
     */
    public function index()
    {
        $portfolios = Portfolio::latest()->get();

        return view('portfolios.index', compact('portfolios'));
    }

    /**
     * Menampilkan form tambah portfolio.
     */
    public function create()
    {
        return view('portfolios.create');
    }

    /**
     * Menyimpan portfolio baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Portfolio::create([
            'title' => $request->title,
            'description' => $request->description,
        ]);

        return redirect()
            ->route('portfolio.index')
            ->with('success', 'Portfolio berhasil ditambahkan.');
    }

    /**
     * Menampilkan detail portfolio.
     */
    public function show(string $id)
    {
        $portfolio = Portfolio::findOrFail($id);

        return view('portfolios.show', compact('portfolio'));
    }

    /**
     * Menampilkan form edit portfolio.
     */
    public function edit(string $id)
    {
        $portfolio = Portfolio::findOrFail($id);

        return view('portfolios.edit', compact('portfolio'));
    }

    /**
     * Memperbarui portfolio.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $portfolio = Portfolio::findOrFail($id);

        $portfolio->update([
            'title' => $request->title,
            'description' => $request->description,
        ]);

        return redirect()
            ->route('portfolio.index')
            ->with('success', 'Portfolio berhasil diperbarui.');
    }

    /**
     * Menghapus portfolio.
     */
    public function destroy(string $id)
    {
        $portfolio = Portfolio::findOrFail($id);
        $portfolio->delete();

        return redirect()
            ->route('portfolio.index')
            ->with('success', 'Portfolio berhasil dihapus.');
    }
}