<?php

namespace Tests\Feature\SiteContent;

use Database\Seeders\SiteContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class SiteBootstrapTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SiteContentSeeder::class);
    }

    public function test_bootstrap_returns_vue_contract(): void
    {
        $response = $this->getJson('/api/bootstrap');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'company' => [
                        'owner_name',
                        'inn',
                        'ogrn',
                        'legal_address',
                        'actual_address',
                    ],
                    'contacts' => [
                        'contact_person',
                        'phone',
                        'email',
                        'address' => ['main', 'directions'],
                        'social' => ['email', 'links'],
                    ],
                    'schedule' => [
                        'days' => [
                            ['id', 'name', 'is_day_off'],
                        ],
                    ],
                    'prices' => [
                        ['category', 'name', 'price', 'prefix'],
                    ],
                    'delivery_info' => [
                        'free_conditions',
                        'advantages',
                    ],
                    'faq' => [
                        'items' => [
                            ['id', 'question', 'answer_lines'],
                        ],
                    ],
                ],
            ]);

        $data = $response->json('data');

        $this->assertSame('ИП Митькин Максим Игоревич', $data['company']['owner_name']);
        $this->assertSame('701744164429', $data['company']['inn']);
        $this->assertSame('zatochka.tsk@yandex.ru', $data['contacts']['email']);
        $this->assertSame('+7 (983) 233-59-07', $data['contacts']['phone']);
        $this->assertArrayNotHasKey('phone_tel', $data['contacts']);
        $this->assertNotEmpty($data['contacts']['phone']);
        $this->assertIsArray($data['schedule']['days']);
        $this->assertNotEmpty($data['schedule']['days']);
        $this->assertContains($data['prices'][0]['category'], ['sharpening', 'repair']);
        $this->assertArrayHasKey('name', $data['prices'][0]);
        $this->assertArrayHasKey('price', $data['prices'][0]);
        $this->assertArrayHasKey('prefix', $data['prices'][0]);
        $this->assertNotEmpty($data['prices']);
        $this->assertIsArray($data['delivery_info']['free_conditions']);
        $this->assertIsArray($data['faq']['items'][0]['answer_lines']);
        $this->assertCount(4, $data['contacts']['social']['links']);
    }

    public function test_bootstrap_fails_when_not_seeded(): void
    {
        DB::table('site_company_profiles')->delete();
        DB::table('site_contacts')->delete();
        DB::table('site_delivery_infos')->delete();

        $response = $this->getJson('/api/bootstrap');

        $response->assertStatus(422)
            ->assertJsonStructure(['message']);
    }
}
