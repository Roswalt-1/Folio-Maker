<?php

namespace App\Http\Controllers;

use App\Models\Portfolio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PortfolioController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->query('q', ''));
        $sort = $request->query('sort', 'updated');
        $order = match ($sort) {
            'title' => ['title', 'asc'],
            'oldest' => ['updated_at', 'asc'],
            default => ['updated_at', 'desc'],
        };

        $portfolios = $request->user()->portfolios()
            ->withCount('items')
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            }))
            ->orderBy(...$order)
            ->get();

        return view('portfolios.index', compact('portfolios', 'search', 'sort'));
    }

    public function create(Request $request): View
    {
        $portfolio = new Portfolio([
            'title' => '',
            'description' => '',
            'category' => '',
            'tags' => [],
        ]);
        $editorState = $this->editorState($portfolio, $request->user());

        return view('portfolio-generator', compact('portfolio', 'editorState'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data = $this->storeEditorImages($data, $request);
        $portfolio = DB::transaction(fn () => $request->user()->portfolios()->create($this->portfolioAttributes($data)));
        $this->saveItems($portfolio, $data['items'] ?? []);

        return $this->saveResponse($request, $portfolio, 'Portfolio created.');
    }

    public function show(Request $request, int $portfolio): View
    {
        $portfolio = $request->user()->portfolios()->with('items')->findOrFail($portfolio);

        return view('portfolios.show', compact('portfolio'));
    }

    public function edit(Request $request, int $portfolio): View
    {
        $portfolio = $request->user()->portfolios()->with('items')->findOrFail($portfolio);
        $editorState = $this->editorState($portfolio, $request->user());

        return view('portfolio-generator', compact('portfolio', 'editorState'));
    }

    public function update(Request $request, int $portfolio)
    {
        $portfolio = $request->user()->portfolios()->with('items')->findOrFail($portfolio);
        $data = $this->validated($request);
        $oldImages = $portfolio->items->flatMap(fn ($item) => $item->images ?? [])->all();
        $allowedImages = array_flip($oldImages);
        foreach ($data['items'] ?? [] as &$item) {
            $item['existing_images'] = array_values(array_filter(
                $item['existing_images'] ?? [],
                fn ($image) => isset($allowedImages[$image])
            ));
        }
        unset($item);
        $allowedAssets = array_filter([
            $portfolio->cover_image,
            data_get($portfolio->profile_data, 'photo.path'),
            data_get($portfolio->appearance_data, 'background.image_path'),
        ]);
        if (! in_array(data_get($data, 'profile.photo.path'), $allowedAssets, true)) {
            data_set($data, 'profile.photo.path', null);
        }
        if (! in_array(data_get($data, 'appearance.background.image_path'), $allowedAssets, true)) {
            data_set($data, 'appearance.background.image_path', null);
        }
        $oldAssets = [
            $portfolio->cover_image,
            data_get($portfolio->profile_data, 'photo.path'),
            data_get($portfolio->appearance_data, 'background.image_path'),
        ];
        $data = $this->storeEditorImages($data, $request, $portfolio);
        $newAssets = [
            $data['cover_image'],
            data_get($data['profile'], 'photo.path'),
            data_get($data['appearance'], 'background.image_path'),
        ];

        DB::transaction(function () use ($portfolio, $data): void {
            $portfolio->update($this->portfolioAttributes($data));
            $portfolio->items()->delete();
            $this->saveItems($portfolio, $data['items'] ?? []);
        });

        $keptImages = collect($data['items'] ?? [])->flatMap(fn ($item) => $item['existing_images'] ?? [])->all();
        Storage::disk('local')->delete(array_values(array_filter(array_diff($oldImages, $keptImages))));
        Storage::disk('local')->delete(array_values(array_filter(array_diff($oldAssets, $newAssets))));

        return $this->saveResponse($request, $portfolio->fresh(), 'Your changes are saved.');
    }

    public function destroy(Request $request, int $portfolio): RedirectResponse
    {
        $portfolio = $request->user()->portfolios()->with('items')->findOrFail($portfolio);
        $paths = $portfolio->items->flatMap(fn ($item) => $item->images ?? [])->all();
        $paths = array_merge($paths, array_filter([
            $portfolio->cover_image,
            data_get($portfolio->profile_data, 'photo.path'),
            data_get($portfolio->appearance_data, 'background.image_path'),
        ]));
        Storage::disk('local')->delete($paths);
        $portfolio->delete();

        return redirect()->route('portfolios.index')->with('success', 'Portfolio deleted.');
    }

    public function media(Request $request, int $portfolio, string $folder, string $filename)
    {
        $portfolio = $request->user()->portfolios()->with('items')->findOrFail($portfolio);
        abort_unless(in_array($folder, ['portfolio-covers', 'portfolio-items', 'profile-photos', 'portfolio-backgrounds'], true), 404);
        $path = $folder.'/'.$filename;
        $ownedPaths = collect([$portfolio->cover_image])
            ->merge($portfolio->items->flatMap(fn ($item) => $item->images ?? []))
            ->merge([data_get($portfolio->profile_data, 'photo.path'), data_get($portfolio->appearance_data, 'background.image_path')]);

        abort_unless($ownedPaths->contains($path), 404);
        abort_unless(Storage::disk('local')->exists($path), 404);

        return response()->file(Storage::disk('local')->path($path));
    }

    private function validated(Request $request): array
    {
        $payload = $request->has('editor_data')
            ? json_decode($request->input('editor_data', ''), true)
            : $this->legacyEditorPayload($request);
        if (! is_array($payload)) {
            throw ValidationException::withMessages(['editor_data' => 'The portfolio editor data is invalid. Please refresh and try again.']);
        }

        $data = Validator::make($payload, [
            'title' => ['required', 'string', 'max:120'],
            'description' => ['required', 'string', 'max:3000'],
            'category' => ['nullable', 'string', 'max:80'],
            'tags' => ['nullable', 'string', 'max:500'],
            'profile' => ['required', 'array'],
            'profile.name' => ['nullable', 'string', 'max:100'],
            'profile.role' => ['nullable', 'string', 'max:160'],
            'profile.bio' => ['nullable', 'string', 'max:3000'],
            'profile.contact' => ['required', 'array'],
            'profile.contact.email' => ['nullable', 'email', 'max:255'],
            'profile.contact.phone' => ['nullable', 'string', 'max:50'],
            'profile.contact.location' => ['nullable', 'string', 'max:160'],
            'profile.contact.github' => ['nullable', 'url', 'max:2048'],
            'profile.contact.linkedin' => ['nullable', 'url', 'max:2048'],
            'profile.skills' => ['array', 'max:40'],
            'profile.skills.*' => ['string', 'max:80'],
            'profile.education' => ['array', 'max:20'],
            'profile.education.*.title' => ['nullable', 'string', 'max:160'],
            'profile.education.*.organization' => ['nullable', 'string', 'max:160'],
            'profile.education.*.period' => ['nullable', 'string', 'max:80'],
            'profile.experience' => ['array', 'max:20'],
            'profile.experience.*.title' => ['nullable', 'string', 'max:160'],
            'profile.experience.*.organization' => ['nullable', 'string', 'max:160'],
            'profile.experience.*.period' => ['nullable', 'string', 'max:80'],
            'profile.photo' => ['required', 'array'],
            'profile.photo.path' => ['nullable', 'string', 'max:500'],
            'profile.photo.shape' => ['required', 'in:circle,rounded,square'],
            'profile.photo.zoom' => ['required', 'numeric', 'between:1,2.5'],
            'profile.photo.x' => ['required', 'numeric', 'between:0,100'],
            'profile.photo.y' => ['required', 'numeric', 'between:0,100'],
            'appearance' => ['required', 'array'],
            'appearance.style' => ['required', 'array'],
            'appearance.style.headingFont' => ['required', 'in:Poppins,Inter,Playfair Display,Roboto Mono,Montserrat,Lora,Raleway,Space Grotesk,DM Sans'],
            'appearance.style.bodyFont' => ['required', 'in:Poppins,Inter,Playfair Display,Roboto Mono,Montserrat,Lora,Raleway,Space Grotesk,DM Sans'],
            'appearance.style.fontSize' => ['required', 'integer', 'between:12,24'],
            'appearance.style.accent' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'appearance.style.template' => ['required', 'in:minimal,card,sidebar'],
            'appearance.style.radius' => ['required', 'integer', 'between:0,32'],
            'appearance.style.spacing' => ['required', 'numeric', 'between:0.7,1.5'],
            'appearance.background' => ['required', 'array'],
            'appearance.background.mode' => ['required', 'in:solid,gradient,dots,grid,waves,stripes,image'],
            'appearance.background.solid' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'appearance.background.color1' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'appearance.background.color2' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'appearance.background.angle' => ['required', 'integer', 'between:0,360'],
            'appearance.background.overlayOpacity' => ['required', 'numeric', 'between:0,0.9'],
            'appearance.background.image_path' => ['nullable', 'string', 'max:500'],
            'appearance.theme' => ['nullable', 'in:light,dark'],
            'appearance.sections' => ['required', 'array'],
            'appearance.sections.about' => ['required', 'boolean'],
            'appearance.sections.skills' => ['required', 'boolean'],
            'appearance.sections.projects' => ['required', 'boolean'],
            'appearance.sections.contact' => ['required', 'boolean'],
            'items' => ['nullable', 'array', 'max:30'],
            'items.*.title' => ['nullable', 'required_with:items.*.description,items.*.images', 'string', 'max:120'],
            'items.*.description' => ['nullable', 'string', 'max:2000'],
            'items.*.link' => ['nullable', 'url', 'max:2048'],
            'items.*.existing_images' => ['nullable', 'array', 'max:8'],
            'items.*.existing_images.*' => ['string', 'max:500'],
            'items.*.images' => ['nullable', 'array', 'max:8'],
            'items.*.images.*' => ['image', 'max:5120'],
        ])->validate();

        $files = $request->validate([
            'cover_image' => ['nullable', 'image', 'max:5120'],
            'profile_photo' => ['nullable', 'image', 'max:5120'],
            'background_image' => ['nullable', 'image', 'max:10240'],
            'items.*.images.*' => ['image', 'max:5120'],
        ]);
        foreach ($request->file('items', []) as $index => $itemFiles) {
            $data['items'][$index]['images'] = $itemFiles['images'] ?? [];
        }
        foreach ($data['items'] ?? [] as &$item) {
            if (! $request->routeIs('portfolios.update')) {
                $item['existing_images'] = [];
            }
        }
        unset($item);

        return [...$data, ...array_intersect_key($files, array_flip(['cover_image', 'profile_photo', 'background_image']))];
    }

    private function legacyEditorPayload(Request $request): array
    {
        return [
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'category' => $request->input('category'),
            'tags' => $request->input('tags'),
            'profile' => [
                'name' => $request->user()->name,
                'role' => '',
                'bio' => '',
                'contact' => ['email' => $request->user()->email, 'phone' => '', 'location' => '', 'github' => '', 'linkedin' => ''],
                'skills' => [],
                'education' => [],
                'experience' => [],
                'photo' => ['path' => null, 'shape' => 'circle', 'zoom' => 1, 'x' => 50, 'y' => 50],
            ],
            'appearance' => [
                'style' => ['headingFont' => 'Space Grotesk', 'bodyFont' => 'DM Sans', 'fontSize' => 16, 'accent' => '#5b6e58', 'template' => 'minimal', 'radius' => 14, 'spacing' => 1],
                'background' => ['mode' => 'solid', 'solid' => '#f8f7f2', 'color1' => '#eef0e8', 'color2' => '#dfe7dc', 'angle' => 135, 'image_path' => null, 'overlayOpacity' => .22],
                'sections' => ['about' => true, 'skills' => true, 'projects' => true, 'contact' => true],
                'theme' => '',
            ],
            'items' => $request->input('items', []),
        ];
    }

    private function portfolioAttributes(array $data): array
    {
        return [
            'title' => $data['title'],
            'description' => $data['description'],
            'category' => $data['category'] ?? null,
            'tags' => collect(explode(',', $data['tags'] ?? ''))->map(fn ($tag) => trim($tag))->filter()->unique()->values()->all(),
            'profile_data' => $data['profile'],
            'appearance_data' => $data['appearance'],
            'cover_image' => $data['cover_image'] ?? null,
        ];
    }

    private function storeEditorImages(array $data, Request $request, ?Portfolio $portfolio = null): array
    {
        if (! $portfolio) {
            $data['profile']['photo']['path'] = null;
            $data['appearance']['background']['image_path'] = null;
        }
        $data['cover_image'] = $request->boolean('remove_cover') ? null : $portfolio?->cover_image;

        foreach ([
            'cover_image' => ['portfolio-covers', null],
            'profile_photo' => ['profile-photos', ['profile', 'photo', 'path']],
            'background_image' => ['portfolio-backgrounds', ['appearance', 'background', 'image_path']],
        ] as $field => [$directory, $path]) {
            if (! $request->hasFile($field)) {
                continue;
            }
            $storedPath = $request->file($field)->store($directory, 'local');
            if ($field === 'cover_image') {
                $data['cover_image'] = $storedPath;
            } else {
                data_set($data, implode('.', $path), $storedPath);
            }
        }
        if (! $portfolio && ! $request->hasFile('cover_image')) {
            $data['cover_image'] = null;
        }

        return $data;
    }

    private function saveItems(Portfolio $portfolio, array $items): void
    {
        foreach ($items as $item) {
            $images = $item['existing_images'] ?? [];
            foreach ($item['images'] ?? [] as $image) {
                $images[] = $image->store('portfolio-items', 'local');
            }
            $title = trim($item['title'] ?? '');
            if ($title === '' && $images === [] && empty($item['description']) && empty($item['link'])) {
                continue;
            }
            $portfolio->items()->create([
                'title' => $title,
                'description' => $item['description'] ?? null,
                'link' => $item['link'] ?? null,
                'image_url' => $images[0] ?? null,
                'images' => $images,
            ]);
        }
    }

    private function editorState(Portfolio $portfolio, \App\Models\User $user): array
    {
        $profile = $portfolio->profile_data ?? [];
        $appearance = $portfolio->appearance_data ?? [];
        $items = $portfolio->exists ? $portfolio->items : collect();

        if ($photoPath = data_get($profile, 'photo.path')) {
            data_set($profile, 'photo.url', route('portfolios.media', [$portfolio, ...explode('/', $photoPath)]));
        }
        if ($backgroundPath = data_get($appearance, 'background.image_path')) {
            data_set($appearance, 'background.image_url', route('portfolios.media', [$portfolio, ...explode('/', $backgroundPath)]));
        }

        return [
            'id' => $portfolio->id,
            'updatedAt' => $portfolio->updated_at?->toISOString(),
            'coverImage' => $portfolio->cover_image
                ? route('portfolios.media', [$portfolio, ...explode('/', $portfolio->cover_image)])
                : '',
            'portfolio' => [
                'title' => $portfolio->title ?? '',
                'description' => $portfolio->description ?? '',
                'category' => $portfolio->category ?? '',
                'tags' => implode(', ', $portfolio->tags ?? []),
            ],
            'profile' => array_replace_recursive([
                'name' => $user->name,
                'role' => '',
                'bio' => '',
                'contact' => ['email' => $user->email, 'phone' => '', 'location' => '', 'github' => '', 'linkedin' => ''],
                'skills' => [],
                'education' => [],
                'experience' => [],
                'photo' => ['path' => null, 'url' => '', 'shape' => 'circle', 'zoom' => 1, 'x' => 50, 'y' => 50],
            ], $profile),
            'appearance' => array_replace_recursive([
                'style' => ['headingFont' => 'Space Grotesk', 'bodyFont' => 'DM Sans', 'fontSize' => 16, 'accent' => '#5b6e58', 'template' => 'minimal', 'radius' => 14, 'spacing' => 1],
                'background' => ['mode' => 'solid', 'solid' => '#f8f7f2', 'color1' => '#eef0e8', 'color2' => '#dfe7dc', 'angle' => 135, 'image_path' => null, 'image_url' => '', 'overlayOpacity' => .22],
                'sections' => ['about' => true, 'skills' => true, 'projects' => true, 'contact' => true],
                'theme' => '',
            ], $appearance),
            'items' => $items->map(fn ($item) => [
                'title' => $item->title,
                'description' => $item->description ?? '',
                'link' => $item->link ?? '',
                'images' => collect($item->images ?? [])->map(fn ($image) => [
                    'path' => $image,
                    'url' => route('portfolios.media', [$portfolio, ...explode('/', $image)]),
                ])->all(),
            ])->all(),
        ];
    }

    private function saveResponse(Request $request, Portfolio $portfolio, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'redirect' => route('portfolios.edit', $portfolio),
            ]);
        }

        return redirect()->route('portfolios.show', $portfolio)->with('success', $message);
    }
}