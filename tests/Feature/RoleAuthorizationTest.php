<?php

use App\Models\User;

it('redirects guests away from owner routes', function () {
    $this->get(route('owner.dashboard'))
        ->assertRedirect(route('login'));
});

it('forbids clients from accessing owner routes', function () {
    $client = User::factory()->client()->create();

    $this->actingAs($client)
        ->get(route('owner.dashboard'))
        ->assertForbidden();
});

it('allows owners to access owner routes', function () {
    $owner = User::factory()->owner()->create();

    $this->withoutVite()
        ->actingAs($owner)
        ->get(route('owner.dashboard'))
        ->assertOk();
});

it('assigns new registrations the client role', function () {
    $this->post(route('register'), [
        'name' => 'New Client',
        'email' => 'new-client@example.test',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => User::ROLE_OWNER,
    ])->assertRedirect(route('dashboard'));

    expect(User::where('email', 'new-client@example.test')->value('role'))
        ->toBe(User::ROLE_CLIENT);
});
