<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutJsonApiHeadersAndDocumentFormatting();
    }

    /** @test */
    public function can_register()
    {
        $response = $this->postJson(
            route('api.v1.register'),
            $data = $this->validCredentials()
        );

        $token = $response->json('plain-text-token');

        $this->assertNotNull(
            PersonalAccessToken::findToken($token),
            'The Plain token is invalid'
        );

        $this->assertDatabaseHas('users',[
            "name" => $data["name"],
            "email" => $data["email"]
        ]);

    }

    /** @test */
    public function authenticated_users_cannot_register_again()
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson(route('api.v1.register'))
            ->assertNoContent();
    }

    /** @test */
    public function name_is_required()
    {
        $this->postJson(
            route('api.v1.register'),
            $this->validCredentials([
                'name' => ''
            ])
        )->assertJsonValidationErrorFor('name');
    }

    /** @test */
    public function email_is_required()
    {
        $this->postJson(
            route('api.v1.register'),
            $this->validCredentials([
                'email' => ''
            ])
        )->assertJsonValidationErrorFor('email');
    }

    /** @test */
    public function email_must_be_valid()
    {
        $this->postJson(
            route('api.v1.register'),
            $this->validCredentials([
                'email' => 'invalidemail'
            ])
        )->assertJsonValidationErrorFor('email');
    }

    /** @test */
    public function email_must_be_an_unique()
    {
        $user = User::factory()->create();

        $this->postJson(
            route('api.v1.register'),
            $this->validCredentials([
                'email' => $user->email
            ])
        )->assertJsonValidationErrorFor('email');
    }

    /** @test */
    public function password_is_required()
    {
        $this->postJson(
            route('api.v1.register'),
            $this->validCredentials([
                'password' => ''
            ])
        )->assertJsonValidationErrorFor('password');
    }

    /** @test */
    public function password_must_be_confirmed()
    {
        $this->postJson(
            route('api.v1.register'),
            $this->validCredentials([
                'password' => '1234',
                'password_confirmation' => 'not-confirm',
            ])
        )->assertJsonValidationErrorFor('password');
    }

    /** @test */
    public function device_name_is_required()
    {
        $this->postJson(
            route('api.v1.register'),
            $this->validCredentials([
                'device_name' => ''
            ])
        )->assertJsonValidationErrorFor('device_name');
    }

    protected function validCredentials(mixed $overrides = []): array
    {
        return  array_merge([
            "name" => "User Name",
            "email" => 'gssa@mail.com',
            "password" => "password",
            "password_confirmation" => "password",
            "device_name" => "my device"
        ],$overrides);
    }
}
