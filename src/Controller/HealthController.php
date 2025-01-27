<?php declare(strict_types=1);

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Paste\Controller;

use Symfony\Component\HttpFoundation\Response;

final class HealthController
{
    public function __invoke(): Response
    {
        $response = new Response('OK', 200);
        $response->setPrivate();

        return $response;
    }
}
