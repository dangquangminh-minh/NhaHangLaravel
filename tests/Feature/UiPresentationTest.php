<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Tests\TestCase;

class UiPresentationTest extends TestCase
{
    public function test_invalid_server_form_links_errors_and_preserves_entered_description(): void
    {
        Category::factory()->create(['name' => 'Món trùng']);
        $this->actingAs(User::factory()->admin()->create());
        $this->from(route('admin.categories.create'))->post(route('admin.categories.store'), ['name' => 'Món trùng', 'description' => 'Giữ mô tả'])->assertSessionHasErrors('name');
        $this->app['view']->share('errors', session('errors'));
        $response = $this->view('admin.categories.form', ['record' => new Category])->assertSee('Giữ mô tả')->assertSee('Họ tên / tên đã tồn tại.');
        $document = new \DOMDocument;
        @$document->loadHTML('<?xml encoding="utf-8" ?>'.(string) $response);
        $field = $document->getElementById('name');
        $this->assertSame('true', $field->getAttribute('aria-invalid'), $document->saveHTML($field));
        $error = $document->getElementById($field->getAttribute('aria-describedby'));
        $this->assertNotNull($error);
        $this->assertStringContainsString('đã tồn tại', $error->textContent);
        $this->assertFalse($document->getElementById('description')->hasAttribute('aria-invalid'));
    }

    public function test_gmail_error_remains_associated_with_fixed_suffix_and_preserves_local_part(): void
    {
        User::factory()->create(['email' => 'taken@gmail.com']);
        $this->from(route('register'))->post(route('register.store'), ['name' => 'Khách mới', 'email_local' => 'taken', 'phone' => '0901234567', 'password' => 'New@12345', 'password_confirmation' => 'New@12345'])->assertSessionHasErrors('email');
        $this->app['view']->share('errors', session('errors'));
        $response = $this->view('auth.register')->assertSee('Email đã tồn tại.');
        $document = new \DOMDocument;
        @$document->loadHTML('<?xml encoding="utf-8" ?>'.(string) $response);
        $field = $document->getElementById('email_local');
        $this->assertSame('taken', $field->getAttribute('value'));
        $this->assertSame('true', $field->getAttribute('aria-invalid'), $document->saveHTML($field));
        foreach (explode(' ', $field->getAttribute('aria-describedby')) as $id) {
            $this->assertNotNull($document->getElementById($id));
        }
        $this->assertStringContainsString('@gmail.com', $document->getElementById('gmail-suffix')->textContent);
    }
}
