<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('user-show');
        $search = trim((string) $request->input('search'));

        $tags = Tag::when($search !== '', function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('id', 'asc')
            ->paginate(50)
            ->withQueryString();

        return view('tags.index', compact('tags'))->with('i', ($request->input('page', 1) - 1) * 5);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('user-create');
        return view('tags.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) : RedirectResponse
    {
         $this->authorize('user-create');

        $request->validate([
            'name' => 'required|unique:tags,name',
            'color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/']
        ]);

        Tag::create([
            'name' => $request->name,
            'color' => $request->color,
        ]);

        return redirect()->route('tags.index')
            ->with('success','Tag created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show($id): View
    {
        $this->authorize('user-show');

        $tag = Tag::findOrFail($id);

        return view('tags.show',compact('tag'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id): View
    {
        $this->authorize('user-edit');
        $tag = Tag::findOrFail($id);

        return view('tags.edit', compact('tag'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $this->authorize('user-edit');

        $request->validate([
            'name' => 'required|unique:tags,name,'.$id,
            'color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/']
        ]);

        $tags = Tag::findOrFail($id);

        $tags->update([
            'name' => $request->name,
            'color' => $request->color,
        ]);

        return redirect()->route('tags.index')
            ->with('success','Tag updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $this->authorize('user-delete');
        $tags = Tag::findOrFail($id);

        $tags->delete();

        return redirect()->route('tags.index')
            ->with('success','Tag deleted successfully');
    }
}
