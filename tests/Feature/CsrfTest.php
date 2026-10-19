<?php

namespace Tests\Feature;

use Tests\TestCase;

class CsrfTest extends TestCase
{
    public function test_web_post_without_csrf_token_is_rejected(): void
    {
        $this->app['env'] = 'local';
        $this->post(route('contacts.store'), ['name' => 'Khách', 'email' => 'csrf@example.test', 'subject' => 'Thử CSRF', 'message' => 'Không có token'])->assertStatus(419);
        $this->assertDatabaseCount('contacts', 0);
    }

    public function test_web_post_with_matching_csrf_token_is_accepted(): void
    {
        $this->app['env'] = 'local';
        $this->withSession(['_token' => 'csrf-test-token'])->post(route('contacts.store'), ['_token' => 'csrf-test-token', 'name' => 'Khách', 'email' => 'csrf@example.test', 'subject' => 'Thử CSRF', 'message' => 'Token hợp lệ'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('contacts', ['email' => 'csrf@example.test', 'message' => 'Token hợp lệ']);
    }
}
