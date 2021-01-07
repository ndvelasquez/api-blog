<?php

namespace Tests\Feature\Http\Controllers\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\Post;
use App\Models\User;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_store()
    {
        // $this->withoutExceptionHandling();
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')->postJson('/api/posts', ['title' => 'Post de prueba']);

        $response->assertJsonStructure(['id', 'title', 'created_at', 'updated_at'])
        ->assertJson(['title' => 'Post de prueba'])
        ->assertStatus(201);

        $this->assertDatabaseHas('posts', ['title' => 'Post de prueba']);
    }

    public function test_title_validation()
    {
        // $this->withoutExceptionHandling();
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')->postJson('/api/posts', ['title' => '']);

        // HTTP ESTATUS 422
        $response->assertStatus(422)
        ->assertJsonValidationErrors('title');
    }

    public function test_show()
    {
        // $this->withoutExceptionHandling();
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $response = $this->actingAs($user, 'api')->json('GET', "/api/posts/$post->id");

        $response->assertJsonStructure(['id', 'title', 'created_at', 'updated_at'])
        ->assertJson(['title' => $post->title])
        ->assertStatus(200);
    }

    public function test_404_response()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')->json('GET', '/api/posts/1000'); // llamo a un post que no exista

        $response->assertStatus(404);
    }

    public function test_update()
    {
        // $this->withoutExceptionHandling();
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $response = $this->actingAs($user, 'api')->json('PUT', "/api/posts/$post->id", ['title' => 'new title']);

        $response->assertJsonStructure(['id', 'title', 'created_at', 'updated_at'])
        ->assertJson(['title' => 'new title'])
        ->assertStatus(200);

        $this->assertDatabaseHas('posts', ['title' => 'new title']);
    }

    public function test_delete()
    {
         // $this->withoutExceptionHandling();
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $response = $this->actingAs($user, 'api')->json('DELETE', "/api/posts/$post->id");

        $response->assertSee(null)
        ->assertStatus(204); //sin contenido

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_index()
    {
        $user = User::factory()->create();
        Post::factory()->count(5)->create();

        $response = $this->actingAs($user, 'api')->json('GET','/api/posts');
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'created_at', 'updated_at']
            ]
        ])
        ->assertStatus(200);
    }

    public function test_guest()
    {
        $this->json('GET', '/api/posts')->assertStatus(401);
        $this->json('POST', '/api/posts')->assertStatus(401);
        $this->json('GET', '/api/posts/1000')->assertStatus(401);
        $this->json('PUT', '/api/posts/1000')->assertStatus(401);
        $this->json('DELETE', '/api/posts/1000')->assertStatus(401);
    }
}
