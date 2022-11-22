<?php

namespace App\Tests\Controller;

use App\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProductControllerTest extends WebTestCase
{
    public function test_products_page(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/en/products/page/1');
        $this->assertResponseIsSuccessful();
        $this->assertCount(
            Paginator::PAGE_SIZE,
            $crawler->filter('tr.product'),
            'The homepage displays the right number of posts.'
        );
    }

    /**
     * This test changes the database contents by creating a new product. However,
     * thanks to the DAMADoctrineTestBundle and its PHPUnit listener, all changes
     * to the database are rolled back when this test completes. This means that
     * all the application tests begin with the same database contents.
     */
    public function testEditProduct(): void
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'john_user',
            'PHP_AUTH_PW' => 'kitten',
        ]);
        $client->followRedirects();

        // Find first blog post
        $crawler = $client->request('GET', '/en/products/page/1');
        $postLink = $crawler->filter('tr.product > td.field a')->link();

        $client->click($postLink);
        $crawler = $client->submitForm('product_save', [
            'product[name]' => 'Acme',
        ]);

        $newName = $crawler->filter('.product')->first()->filter('td.field')->text();

        $this->assertSame('Acme', $newName);
    }

    public function testNewProduct(): void
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'john_user',
            'PHP_AUTH_PW' => 'kitten',
        ]);
        $client->followRedirects();

        // Find first blog post
        $crawler = $client->request('GET', '/en/products/page/1');
        $newProductLink = $crawler->filter('ul.pure-menu-list > li.pure-menu-item a')->link();

        $client->click($newProductLink);
        $crawler = $client->submitForm('product_save', [
            'product[name]' => 'Acme',
        ]);

        $newName = $crawler->filter('.product')->first()->filter('td.field')->text();

        $this->assertSame('Acme', $newName);
    }
}
