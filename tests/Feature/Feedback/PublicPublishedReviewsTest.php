<?php

namespace Tests\Feature\Feedback;

use App\Domain\Feedback\VO\ReviewStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class PublicPublishedReviewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_only_published_reviews_with_comments(): void
    {
        $now = now();

        DB::table('clients')->insert([
            'id' => 1,
            'phone' => '+79990001122',
            'name' => 'Иван Петров',
            'email' => null,
            'birth_date' => null,
            'delivery_address' => null,
            'password' => null,
            'bonus_account_id' => 1,
            'bonus_balance' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('reviews')->insert([
            [
                'id' => 1,
                'order_id' => 'ord-1',
                'client_id' => 1,
                'rating' => 5,
                'comment' => 'Отличная заточка',
                'manager_reply' => 'Спасибо!',
                'status' => ReviewStatus::Published->value,
                'moderated_by' => null,
                'submitted_at' => $now,
                'moderated_at' => $now,
                'hidden_at' => null,
                'deleted_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'order_id' => 'ord-2',
                'client_id' => 1,
                'rating' => 4,
                'comment' => null,
                'manager_reply' => null,
                'status' => ReviewStatus::Published->value,
                'moderated_by' => null,
                'submitted_at' => $now,
                'moderated_at' => $now,
                'hidden_at' => null,
                'deleted_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'order_id' => 'ord-3',
                'client_id' => 1,
                'rating' => 3,
                'comment' => 'На модерации',
                'manager_reply' => null,
                'status' => ReviewStatus::PendingModeration->value,
                'moderated_by' => null,
                'submitted_at' => $now,
                'moderated_at' => null,
                'hidden_at' => null,
                'deleted_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $response = $this->getJson('/api/reviews');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'average_rating',
                    'items' => [
                        ['id', 'rating', 'comment', 'manager_reply', 'client_name', 'submitted_at'],
                    ],
                ],
            ]);

        $items = $response->json('data.items');
        $this->assertCount(1, $items);
        $this->assertSame(1, $items[0]['id']);
        $this->assertSame('Иван', $items[0]['client_name']);
        $this->assertSame('Отличная заточка', $items[0]['comment']);
        $this->assertSame('Спасибо!', $items[0]['manager_reply']);
        $this->assertSame('4.50', $response->json('data.average_rating'));
    }

    public function test_respects_limit_query(): void
    {
        $now = now();

        DB::table('clients')->insert([
            'id' => 1,
            'phone' => '+79990001122',
            'name' => 'Анна',
            'email' => null,
            'birth_date' => null,
            'delivery_address' => null,
            'password' => null,
            'bonus_account_id' => 1,
            'bonus_balance' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        foreach ([1, 2, 3] as $id) {
            DB::table('reviews')->insert([
                'id' => $id,
                'order_id' => 'ord-'.$id,
                'client_id' => 1,
                'rating' => 5,
                'comment' => 'Комментарий '.$id,
                'manager_reply' => null,
                'status' => ReviewStatus::Published->value,
                'moderated_by' => null,
                'submitted_at' => $now->copy()->subMinutes($id),
                'moderated_at' => $now,
                'hidden_at' => null,
                'deleted_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $response = $this->getJson('/api/reviews?limit=2');

        $response->assertOk();
        $this->assertCount(2, $response->json('data.items'));
    }
}
