<?php declare(strict_types=1);

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Paste\Controller;

use Paste\IntegrationTest;

/**
 * @group integration
 *
 * @internal
 */
final class CreateControllerTest extends IntegrationTest
{
    public function testPostingWithoutABodyShouldReturnA400(): void
    {
        $client = static::createClient();
        $client->request('POST', '/', [], [], ['HTTP_Accept' => 'text/plain'], '');

        static::assertSame(400, $client->getResponse()->getStatusCode());

        $client->request('POST', '/', ['paste' => '']);

        static::assertSame(400, $client->getResponse()->getStatusCode());
    }

    public function testPostingAPasteShouldReturnTheExpectedResponseHeaders(): void
    {
        $client = static::createClient();
        $client->request('POST', '/', [], [], ['HTTP_Accept' => 'text/plain'], 'Lorem ipsum');

        static::assertSame(201, $client->getResponse()->getStatusCode());
        static::assertTrue($client->getResponse()->headers->has('Location'));
        static::assertTrue($client->getResponse()->headers->has('X-Paste-Id'));
        static::assertTrue($client->getResponse()->headers->has('X-Paste-Token'));

        $client->request('POST', '/', ['paste' => 'Lorem ipsum', 'redirect' => 'redirect']);

        static::assertSame(303, $client->getResponse()->getStatusCode());
        static::assertTrue($client->getResponse()->headers->has('Location'));
        static::assertTrue($client->getResponse()->headers->has('X-Paste-Id'));
        static::assertTrue($client->getResponse()->headers->has('X-Paste-Token'));
    }
}
