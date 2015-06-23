<?php

/*
 * (c) Rob Bast <rob.bast@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Alcohol\PasteBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Index
{
    /**
     * @param Request $request
     * @return Response
     */
    public function __invoke(Request $request)
    {
        $href = $request->getUri();
        $host = $request->getHttpHost();
        $form = <<<FORM
data:text/html,<form action="$href" method="POST" accept-charset="UTF-8">
<textarea name="paste" cols="100" rows="30"></textarea>
<br><button type="submit">paste</button></form>
FORM;
        $body = <<<BODY
<style>body { padding: 2em; }</style>
<pre>
DESCRIPTION
    paste: command line pastebin.

USING
    &lt;command&gt; | curl --data-binary '@-' $host

ALTERNATIVELY
    use <a href='$form'>this form</a> to paste from a browser

SOURCE
    <a href='https://github.com/alcohol/sf-minimal-demo/'>github.com/alcohol</a>
</pre>
BODY;

        $response = new Response($body, 200);
        $response
            ->setPublic()
            ->setETag(md5($response->getContent()))
            ->setTtl(60 * 60)
            ->setClientTtl(60 * 10)
        ;

        if (!$request->isNoCache()) {
            $response->isNotModified($request);
        }

        return $response;
    }
}