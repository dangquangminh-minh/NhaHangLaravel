<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Dish;
use App\Models\OrderItem;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    public function test_admin_creates_reads_updates_and_deletes_category(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $this->post(route('admin.categories.store'), ['name' => 'Món hấp', 'description' => 'Món hấp demo'])->assertRedirect(route('admin.categories.index'));
        $record = Category::query()->sole();
        $this->get(route('admin.categories.show', $record))->assertOk()->assertSee('Món hấp');
        $this->put(route('admin.categories.update', $record), ['name' => 'Món hấp', 'description' => 'Mô tả mới'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('categories', ['id' => $record->id, 'description' => 'Mô tả mới']);
        $this->delete(route('admin.categories.destroy', $record))->assertRedirect(route('admin.categories.index'));
        $this->assertModelMissing($record);
    }

    public function test_admin_creates_reads_updates_and_deletes_related_dish(): void
    {
        $category = Category::factory()->create();
        $supplier = Supplier::factory()->create();
        $data = ['name' => 'Cá hấp gừng', 'price' => '85000.50', 'category_id' => $category->id, 'supplier_id' => $supplier->id, 'is_available' => 1, 'description' => 'Hấp cùng gừng tươi'];
        $this->actingAs(User::factory()->admin()->create())->post(route('admin.dishes.store'), $data)->assertSessionHasNoErrors();
        $dish = Dish::query()->sole();
        $this->get(route('admin.dishes.show', $dish))->assertOk()->assertSee($category->name)->assertSee($supplier->name);
        $this->put(route('admin.dishes.update', $dish), array_replace($data, ['price' => '90000.00']))->assertSessionHasNoErrors();
        $this->assertSame('90000.00', $dish->fresh()->price);
        $this->delete(route('admin.dishes.destroy', $dish))->assertRedirect(route('admin.dishes.index'));
        $this->assertModelMissing($dish);
    }

    public function test_category_with_dishes_cannot_be_deleted(): void
    {
        $dish = Dish::factory()->create();
        $this->actingAs(User::factory()->admin()->create())->delete(route('admin.categories.destroy', $dish->category))->assertSessionHas('error');
        $this->assertModelExists($dish->category);
        $this->assertModelExists($dish);
    }

    public function test_bulk_delete_rolls_back_if_one_dish_has_order_history(): void
    {
        $free = Dish::factory()->create();
        $used = Dish::factory()->create();
        OrderItem::factory()->for($used)->create();
        $this->actingAs(User::factory()->admin()->create())->post(route('admin.dishes.bulk-destroy'), ['ids' => [$free->id, $used->id]])->assertSessionHasErrors('ids');
        $this->assertModelExists($free);
        $this->assertModelExists($used);
    }

    public function test_dish_rejects_invalid_price_and_foreign_keys(): void
    {
        $this->actingAs(User::factory()->admin()->create())->post(route('admin.dishes.store'), ['name' => 'Sai dữ liệu', 'price' => '-1', 'category_id' => 999, 'supplier_id' => 999, 'is_available' => 1])->assertSessionHasErrors(['price', 'category_id', 'supplier_id']);
        $this->assertDatabaseCount('dishes', 0);
    }

    public function test_unique_category_error_is_in_vietnamese_and_keeps_input(): void
    {
        Category::factory()->create(['name' => 'Món trùng']);
        $this->actingAs(User::factory()->admin()->create())->post(route('admin.categories.store'), ['name' => 'Món trùng', 'description' => 'Nội dung cần giữ'])->assertSessionHasErrors(['name' => 'Họ tên / tên đã tồn tại.'])->assertSessionHasInput('description', 'Nội dung cần giữ');
        $this->assertDatabaseCount('categories', 1);
    }

    public function test_search_and_pagination_keep_category_and_keyword(): void
    {
        $category = Category::factory()->create();
        Dish::factory()->count(14)->for($category)->sequence(fn ($sequence) => ['name' => 'Bún demo '.$sequence->index])->create();
        Dish::factory()->create(['name' => 'Món ngoài kết quả']);
        $response = $this->get(route('menu', ['q' => 'Bún', 'category_id' => $category->id]));
        $response->assertOk()->assertViewHas('dishes', fn ($rows): bool => $rows->total() === 14 && $rows->count() === 12)->assertDontSee('Món ngoài kết quả');
        $this->assertStringContainsString('q=B%C3%BAn', $response->viewData('dishes')->nextPageUrl());
        $this->get(route('menu', ['q' => 'Bún', 'category_id' => $category->id, 'page' => 2]))->assertOk()->assertViewHas('dishes', fn ($rows): bool => $rows->count() === 2);
    }

    public function test_search_with_no_result_shows_empty_state(): void
    {
        $this->get(route('menu', ['q' => 'không có món nào']))->assertOk()->assertSee('Không có món phù hợp.');
    }

    public function test_rendered_description_escapes_html(): void
    {
        $dish = Dish::factory()->create(['description' => '<script>alert(1)</script>']);
        $this->get(route('dishes.show', $dish))->assertOk()->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_images_are_stored_with_generated_names(): void
    {
        Storage::fake('public');
        $dish = Dish::factory()->create();
        $this->actingAs(User::factory()->admin()->create())->put(route('admin.dishes.update', $dish), ['name' => $dish->name, 'price' => '55000', 'category_id' => $dish->category_id, 'supplier_id' => $dish->supplier_id, 'is_available' => 1, 'image' => UploadedFile::fake()->image('food.jpg')])->assertSessionHasNoErrors();
        Storage::disk('public')->assertExists(substr($dish->fresh()->image, 8));
    }
}
