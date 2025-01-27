<?php declare(strict_types=1);

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Paste\Security;

use Webmozart\Assert\Assert;

final class HashGenerator
{
    /** @var string */
    private $secret;

    /**
     * @throws \InvalidArgumentException
     */
    public function __construct(string $secret)
    {
        Assert::stringNotEmpty($secret, 'Argument "$secret" is required and should be a non-empty string.');

        $this->secret = $secret;
    }

    public function generateHash(string $paste_id): string
    {
        return hash('sha256', sprintf('%s.%s', $paste_id, $this->secret), false);
    }
}
