<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_includes_admin_status()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'user' => [
                    'id',
                    'name',
                    'email',
                    'is_admin',
                ],
                'access_token',
                'token_type',
            ])
            ->assertJson([
                'user' => [
                    'is_admin' => false,
                ],
            ]);
    }

    public function test_login_includes_admin_status()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'login@example.com',
            'password' => Hash::make('password123'),
            'is_admin' => false,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'login@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => [
                    'id',
                    'name',
                    'email',
                    'is_admin',
                ],
                'access_token',
                'token_type',
            ])
            ->assertJson([
                'user' => [
                    'is_admin' => false,
                ],
            ]);
    }

    public function test_me_endpoint_includes_admin_status()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'me@example.com',
            'password' => Hash::make('password123'),
            'is_admin' => false,
        ]);

        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/me');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'email',
                'is_admin',
            ])
            ->assertJson([
                'is_admin' => false,
            ]);
    }

        public function test_admin_user_login_shows_admin_status()

        {

            $user = User::create([

                'name' => 'Admin User',

                'email' => 'admin@example.com',

                'password' => Hash::make('password123'),

                'is_admin' => true,

            ]);

    

            $response = $this->postJson('/api/login', [

                'email' => 'admin@example.com',

                'password' => 'password123',

            ]);

    

            $response->assertStatus(200)

                ->assertJson([

                    'user' => [

                        'is_admin' => true,

                    ],

                ]);

        }

    

        public function test_register_sets_is_admin_to_false_by_default()

        {

            $response = $this->postJson('/api/register', [

                'name' => 'New User',

                'email' => 'newuser@example.com',

                'password' => 'password123',

                'password_confirmation' => 'password123',

            ]);

    

            $response->assertStatus(201)

                ->assertJson([

                    'user' => [

                        'is_admin' => false,

                    ],

                ]);

        }

    }

    