<?php

namespace Tests\Feature;

use App\Models\Portfolio;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PortfolioWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_guest_is_redirected_from_portfolios(): void
    {
        $this->get(route('portfolios.index'))->assertRedirect(route('login'));
    }

    public function test_the_portfolio_generator_is_available_to_signed_in_users(): void
    {
        $this->get(route('portfolios.generator'))->assertRedirect(route('login'));

        $this->actingAs($this->makeUser())
            ->get(route('portfolios.generator'))
            ->assertRedirect(route('portfolios.create'));

        $this->get(route('portfolios.create'))
            ->assertOk()
            ->assertSee('<title>Create portfolio | Folio Maker</title>', false)
            ->assertSee('<meta property="og:title" content="Create portfolio | Folio Maker">', false)
            ->assertSee('Create your folio')
            ->assertDontSee('YOUR CREATIVE SPACE')
            ->assertDontSee('Shape your story, projects and design in one place.')
            ->assertDontSee('YOUR CANVAS, YOUR RULES')
            ->assertDontSee('Unsaved changes')
            ->assertSee('>Home</a>', false)
            ->assertDontSee('Portfolio generator')
            ->assertSee('data-site-theme-toggle')
            ->assertSee('Overview');

        $this->get(route('portfolios.index'))
            ->assertOk()
            ->assertSee('<title>Home | Folio Maker</title>', false)
            ->assertSee('>Home</a>', false)
            ->assertSee('My')
            ->assertSee('portfolios.')
            ->assertSee('New portfolio')
            ->assertDontSee('Portfolio generator')
            ->assertDontSee('YOUR CREATIVE SPACE')
            ->assertDontSee('Everything you’ve made, all together.');
    }

    public function test_public_pages_use_folio_maker_branding_and_titles(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('<title>Folio Maker</title>', false)
            ->assertSee('>Folio Maker</a>', false);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('<title>Login | Folio Maker</title>', false)
            ->assertSee('class="auth-card-brand"', false);

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('<title>Sign up | Folio Maker</title>', false)
            ->assertSee('class="auth-card-brand"', false);
    }

    public function test_a_user_can_register_with_a_hashed_password(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Avery Stone',
            'email' => 'avery@example.test',
            'password' => 'StrongPass123',
            'password_confirmation' => 'StrongPass123',
        ])->assertRedirect(route('portfolios.index'));

        $user = User::where('email', 'avery@example.test')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertTrue(Hash::check('StrongPass123', $user->password_hash));
        $this->assertNotSame('StrongPass123', $user->password_hash);
    }

    public function test_login_rejects_wrong_password_and_remembered_login_succeeds(): void
    {
        $user = $this->makeUser();

        $this->from(route('login'))->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'WrongPass123',
        ])->assertRedirect(route('login'))->assertSessionHasErrors('email');

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'StrongPass123',
            'remember' => '1',
        ])->assertRedirect(route('portfolios.index'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_a_user_can_request_and_complete_a_password_reset(): void
    {
        Notification::fake();
        $user = $this->makeUser();

        $this->post(route('password.email'), ['email' => $user->email])->assertSessionHas('success');
        Notification::assertSentTo($user, ResetPassword::class);
        $notification = Notification::sent($user, ResetPassword::class)->first();

        $this->post(route('password.update'), [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'ChangedPass123',
            'password_confirmation' => 'ChangedPass123',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('ChangedPass123', $user->fresh()->password_hash));
    }

    public function test_registration_rejects_duplicate_emails_and_weak_passwords(): void
    {
        $this->makeUser('taken@example.test');

        $this->post(route('register.store'), [
            'name' => 'New User',
            'email' => 'taken@example.test',
            'password' => 'weak',
            'password_confirmation' => 'weak',
        ])->assertSessionHasErrors(['email', 'password']);
    }

    public function test_a_user_can_create_and_search_their_portfolio(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user)->get(route('portfolios.create'))
            ->assertOk()
            ->assertSee('Create your folio')
            ->assertDontSee('Unsaved changes')
            ->assertSee('Add a project +');

        $this->actingAs($user)->post(route('portfolios.store'), [
            'title' => 'Quiet Forms',
            'description' => 'A collection of thoughtful identities.',
            'category' => 'Design',
            'tags' => 'identity, print',
            'items' => [
                ['title' => 'North Studio', 'description' => 'A new identity system.', 'link' => 'https://example.test'],
            ],
        ])->assertRedirect();

        $portfolio = $user->portfolios()->firstOrFail();
        $this->assertDatabaseHas('portfolio_items', ['portfolio_id' => $portfolio->id, 'title' => 'North Studio']);
        $this->get(route('portfolios.index', ['q' => 'Quiet']))->assertOk()->assertSee('Quiet Forms');
        $this->get(route('portfolios.show', $portfolio))
            ->assertOk()
            ->assertSee('<title>Quiet Forms | Folio Maker</title>', false)
            ->assertSee('<meta property="og:title" content="Quiet Forms | Folio Maker">', false)
            ->assertSee('North Studio');
        $this->get(route('portfolios.edit', $portfolio))->assertOk()->assertSee('North Studio');
    }

    public function test_the_merged_editor_saves_portfolio_profile_appearance_and_private_images(): void
    {
        Storage::fake('local');
        $user = $this->makeUser();
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAAADUlEQVR4nGP4z8AAAAMBAQDJ/pLvAAAAAElFTkSuQmCC');
        $editorData = [
            'title' => 'My folio',
            'description' => 'A collection of selected work.',
            'category' => 'Design',
            'tags' => 'web, identity',
            'profile' => [
                'name' => 'Test User',
                'role' => 'Product designer',
                'bio' => 'I make useful things.',
                'contact' => ['email' => 'user@example.test', 'phone' => '', 'location' => 'Remote', 'github' => '', 'linkedin' => ''],
                'skills' => ['UI design'],
                'education' => [['title' => 'Design degree', 'organization' => 'School', 'period' => '2020–2024']],
                'experience' => [],
                'photo' => ['path' => null, 'shape' => 'circle', 'zoom' => 1.2, 'x' => 50, 'y' => 50],
            ],
            'appearance' => [
                'style' => ['headingFont' => 'Inter', 'bodyFont' => 'DM Sans', 'fontSize' => 24, 'accent' => '#345678', 'template' => 'card', 'radius' => 16, 'spacing' => 1.1],
                'background' => ['mode' => 'image', 'solid' => '#f8f7f2', 'color1' => '#eef0e8', 'color2' => '#dfe7dc', 'angle' => 135, 'image_path' => null, 'overlayOpacity' => .2],
                'sections' => ['about' => true, 'skills' => true, 'projects' => true, 'contact' => true],
                'theme' => 'light',
            ],
            'items' => [['title' => 'Project One', 'description' => 'First project.', 'link' => '', 'existing_images' => []]],
        ];

        $this->actingAs($user)->post(route('portfolios.store'), [
            'editor_data' => json_encode($editorData),
            'profile_photo' => UploadedFile::fake()->createWithContent('profile.png', $png),
            'background_image' => UploadedFile::fake()->createWithContent('background.png', $png),
            'items' => [['images' => [UploadedFile::fake()->createWithContent('project.png', $png)]]],
        ], ['Accept' => 'application/json'])->assertOk();

        $portfolio = $user->portfolios()->with('items')->firstOrFail();
        $this->assertSame('Test User', $portfolio->profile_data['name']);
        $this->assertSame('card', $portfolio->appearance_data['style']['template']);
        $this->assertSame(24, $portfolio->appearance_data['style']['fontSize']);
        $this->assertSame('#345678', $portfolio->appearance_data['style']['accent']);
        $this->assertSame('Project One', $portfolio->items[0]->title);
        Storage::disk('local')->assertExists($portfolio->profile_data['photo']['path']);
        Storage::disk('local')->assertExists($portfolio->appearance_data['background']['image_path']);
        Storage::disk('local')->assertExists($portfolio->items[0]->images[0]);

        $photoUrl = route('portfolios.media', [$portfolio, ...explode('/', $portfolio->profile_data['photo']['path'])]);
        $this->get($photoUrl)->assertOk();
        $this->get(route('portfolios.edit', $portfolio))
            ->assertOk()
            ->assertSee('Test User')
            ->assertSee('Project One')
            ->assertSee('"fontSize":24', false);

        $updateData = $editorData;
        $updateData['title'] = 'Updated folio';
        $updateData['profile']['photo']['path'] = $portfolio->profile_data['photo']['path'];
        $updateData['appearance']['background']['image_path'] = $portfolio->appearance_data['background']['image_path'];
        $updateData['items'][0]['existing_images'] = $portfolio->items[0]->images;
        $this->actingAs($user)->put(route('portfolios.update', $portfolio), [
            'editor_data' => json_encode($updateData),
        ], ['Accept' => 'application/json'])->assertOk();

        $portfolio->refresh();
        $this->assertSame('Updated folio', $portfolio->title);
        Storage::disk('local')->assertExists($portfolio->profile_data['photo']['path']);
        Storage::disk('local')->assertExists($portfolio->appearance_data['background']['image_path']);
        Storage::disk('local')->assertExists($portfolio->items[0]->images[0]);

        $other = $this->makeUser('other@example.test');
        $this->actingAs($other)->get($photoUrl)->assertNotFound();
    }

    public function test_uploaded_images_are_private_and_only_served_to_the_owner(): void
    {
        Storage::fake('local');
        $user = $this->makeUser();
        $other = $this->makeUser('other@example.test');
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAAADUlEQVR4nGP4z8AAAAMBAQDJ/pLvAAAAAElFTkSuQmCC');

        $this->actingAs($user)->post(route('portfolios.store'), [
            'title' => 'Image collection',
            'description' => 'A portfolio with private images.',
            'cover_image' => UploadedFile::fake()->createWithContent('cover.png', $png),
            'items' => [[
                'title' => 'Project one',
                'images' => [UploadedFile::fake()->createWithContent('project.png', $png)],
            ]],
        ])->assertRedirect();

        $portfolio = $user->portfolios()->with('items')->firstOrFail();
        $coverPath = $portfolio->cover_image;
        Storage::disk('local')->assertExists($coverPath);
        Storage::disk('local')->assertExists($portfolio->items[0]->images[0]);

        $mediaUrl = route('portfolios.media', [$portfolio, ...explode('/', $coverPath)]);
        $this->actingAs($user)->get($mediaUrl)->assertOk();
        $this->actingAs($other)->get($mediaUrl)->assertNotFound();
    }

    public function test_a_user_cannot_view_edit_or_delete_another_users_portfolio(): void
    {
        $owner = $this->makeUser('owner@example.test');
        $other = $this->makeUser('other@example.test');
        $portfolio = $owner->portfolios()->create([
            'title' => 'Private work',
            'description' => 'Only the owner should see this.',
            'cover_image' => 'portfolio-covers/private.jpg',
        ]);

        $this->actingAs($other)->get(route('portfolios.show', $portfolio))->assertNotFound();
        $this->actingAs($other)->get(route('portfolios.edit', $portfolio))->assertNotFound();
        $this->actingAs($other)->get(route('portfolios.media', [$portfolio, 'portfolio-covers', 'private.jpg']))->assertNotFound();
        $this->actingAs($other)->delete(route('portfolios.destroy', $portfolio))->assertNotFound();
        $this->assertDatabaseHas('portfolios', ['id' => $portfolio->id]);
    }

    private function makeUser(string $email = 'user@example.test'): User
    {
        return User::create([
            'name' => 'Test User',
            'email' => $email,
            'password_hash' => Hash::make('StrongPass123'),
        ]);
    }
}