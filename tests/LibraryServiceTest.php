<?php

namespace Antre\Tests;

use Antre\LibraryService;
use PHPUnit\Framework\TestCase;

class LibraryServiceTest extends TestCase
{
    private LibraryService $svc;
    private int $userId = 1;

    protected function setUp(): void
    {
        $pdo = TestDatabase::create();
        $pdo->exec("INSERT INTO users (id, username, password_hash) VALUES (1, 'testuser', 'hash')");
        $this->svc = new LibraryService($pdo);
    }

    private function baseItem(array $overrides = []): array
    {
        return array_merge([
            'external_id' => 'ext_1',
            'title'       => 'Naruto',
            'category'    => 'anime',
            'status'      => 'planifie',
        ], $overrides);
    }

    // --- GET ---

    public function testGetItemsReturnsEmptyArrayWhenNone(): void
    {
        $this->assertSame([], $this->svc->getItems($this->userId, 'anime'));
    }

    public function testGetItemsReturnsOnlyMatchingCategory(): void
    {
        $this->svc->addItem($this->userId, $this->baseItem(['category' => 'anime']));
        $this->svc->addItem($this->userId, $this->baseItem(['external_id' => 'ext_2', 'title' => 'GOT', 'category' => 'serie']));

        $this->assertCount(1, $this->svc->getItems($this->userId, 'anime'));
        $this->assertCount(1, $this->svc->getItems($this->userId, 'serie'));
    }

    public function testGetItemsReturnsOnlyOwnUsersItems(): void
    {
        $this->svc->addItem($this->userId, $this->baseItem());
        $this->assertSame([], $this->svc->getItems(999, 'anime'));
    }

    public function testGetItemsReturnedAlphabetically(): void
    {
        $this->svc->addItem($this->userId, $this->baseItem(['external_id' => 'e1', 'title' => 'Zoro']));
        $this->svc->addItem($this->userId, $this->baseItem(['external_id' => 'e2', 'title' => 'Aria']));
        $items = $this->svc->getItems($this->userId, 'anime');
        $this->assertSame('Aria', $items[0]['title']);
        $this->assertSame('Zoro', $items[1]['title']);
    }

    // --- POST ---

    public function testAddItemSuccess(): void
    {
        $result = $this->svc->addItem($this->userId, $this->baseItem());
        $this->assertTrue($result['success']);
        $this->assertIsInt($result['id']);
        $this->assertGreaterThan(0, $result['id']);
    }

    public function testAddItemAppearsInGetItems(): void
    {
        $this->svc->addItem($this->userId, $this->baseItem());
        $items = $this->svc->getItems($this->userId, 'anime');
        $this->assertCount(1, $items);
        $this->assertSame('Naruto', $items[0]['title']);
    }

    public function testAddItemDefaultStatusIsPlanifie(): void
    {
        $body = $this->baseItem();
        unset($body['status']);
        $this->svc->addItem($this->userId, $body);
        $items = $this->svc->getItems($this->userId, 'anime');
        $this->assertSame('planifie', $items[0]['status']);
    }

    public function testAddItemMissingExternalIdReturnsError(): void
    {
        $body = $this->baseItem();
        unset($body['external_id']);
        $result = $this->svc->addItem($this->userId, $body);
        $this->assertArrayHasKey('error', $result);
    }

    public function testAddItemMissingTitleReturnsError(): void
    {
        $result = $this->svc->addItem($this->userId, array_merge($this->baseItem(), ['title' => '']));
        $this->assertArrayHasKey('error', $result);
    }

    public function testAddItemMissingCategoryReturnsError(): void
    {
        $body = $this->baseItem();
        unset($body['category']);
        $result = $this->svc->addItem($this->userId, $body);
        $this->assertArrayHasKey('error', $result);
    }

    public function testAddItemStoresCoverUrlAndYear(): void
    {
        $this->svc->addItem($this->userId, $this->baseItem([
            'cover_url' => 'https://img.example.com/cover.jpg',
            'year'      => '2002',
        ]));
        $items = $this->svc->getItems($this->userId, 'anime');
        $this->assertSame('https://img.example.com/cover.jpg', $items[0]['cover_url']);
        $this->assertSame('2002', $items[0]['year']);
    }

    // --- PUT ---

    public function testUpdateStatusSuccess(): void
    {
        $add = $this->svc->addItem($this->userId, $this->baseItem());
        $result = $this->svc->updateItem($this->userId, ['id' => $add['id'], 'status' => 'en_cours']);
        $this->assertTrue($result['success']);

        $items = $this->svc->getItems($this->userId, 'anime');
        $this->assertSame('en_cours', $items[0]['status']);
    }

    public function testUpdateMissingIdReturnsError(): void
    {
        $result = $this->svc->updateItem($this->userId, ['status' => 'termine']);
        $this->assertArrayHasKey('error', $result);
    }

    public function testUpdateNonAllowedColumnIsIgnored(): void
    {
        $add = $this->svc->addItem($this->userId, $this->baseItem());
        // 'title' n'est pas dans la whitelist
        $result = $this->svc->updateItem($this->userId, ['id' => $add['id'], 'title' => 'HACKED']);
        $this->assertTrue($result['success']);
        $items = $this->svc->getItems($this->userId, 'anime');
        $this->assertSame('Naruto', $items[0]['title']);
    }

    public function testUpdateDoesNotAffectOtherUsersItem(): void
    {
        $add = $this->svc->addItem($this->userId, $this->baseItem());
        // Un autre user tente de modifier l'item
        $this->svc->updateItem(999, ['id' => $add['id'], 'status' => 'termine']);
        $items = $this->svc->getItems($this->userId, 'anime');
        $this->assertSame('planifie', $items[0]['status']);
    }

    public function testUpdateEmptyStringBecomesNull(): void
    {
        $add = $this->svc->addItem($this->userId, $this->baseItem());
        $this->svc->updateItem($this->userId, ['id' => $add['id'], 'temp_review' => '']);
        $items = $this->svc->getItems($this->userId, 'anime');
        $this->assertNull($items[0]['temp_review']);
    }

    public function testUpdateAllAllowedFields(): void
    {
        $add = $this->svc->addItem($this->userId, $this->baseItem());
        $this->svc->updateItem($this->userId, [
            'id'              => $add['id'],
            'status'          => 'en_cours',
            'current_episode' => 5,
            'current_season'  => 1,
            'airing_season'   => 2,
            'temp_review'     => 'Bien jusqu\'ici',
            'planned_date'    => '2026-07-01',
        ]);
        $items = $this->svc->getItems($this->userId, 'anime');
        $item = $items[0];
        $this->assertSame('en_cours', $item['status']);
        $this->assertSame('5', (string)$item['current_episode']);
        $this->assertSame('Bien jusqu\'ici', $item['temp_review']);
    }

    // --- DELETE ---

    public function testDeleteItemSuccess(): void
    {
        $add = $this->svc->addItem($this->userId, $this->baseItem());
        $result = $this->svc->deleteItem($this->userId, $add['id']);
        $this->assertTrue($result['success']);
        $this->assertSame([], $this->svc->getItems($this->userId, 'anime'));
    }

    public function testDeleteItemMissingIdReturnsError(): void
    {
        $result = $this->svc->deleteItem($this->userId, 0);
        $this->assertArrayHasKey('error', $result);
    }

    public function testDeleteDoesNotAffectOtherUsersItem(): void
    {
        $add = $this->svc->addItem($this->userId, $this->baseItem());
        $this->svc->deleteItem(999, $add['id']);
        $this->assertCount(1, $this->svc->getItems($this->userId, 'anime'));
    }

    public function testDeleteNonExistentItemReturnsSuccess(): void
    {
        $result = $this->svc->deleteItem($this->userId, 9999);
        $this->assertTrue($result['success']);
    }
}
