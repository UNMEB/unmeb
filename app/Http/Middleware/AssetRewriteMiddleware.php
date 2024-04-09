<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AssetRewriteMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Handle the request and get the response
        $response = $next($request);

        // Check if the response is valid and is HTML
        if ($response->headers->get('Content-Type') && str_contains($response->headers->get('Content-Type'), 'text/html')) {
            // Modify the content of the response to rewrite asset URLs
            $content = $response->getContent();
            $modifiedContent = $this->rewriteAssetUrls($content);

            // Set the modified content back to the response
            $response->setContent($modifiedContent);
        }

        return $response;
    }

    /**
     * Rewrite asset URLs to start with "unmeb".
     *
     * @param  string  $content
     * @return string
     */
    private function rewriteAssetUrls($content)
    {
        // Rewrite URLs in CSS files
        $content = preg_replace_callback('/url\([\'"]?([^\'"\)]+)[\'"]?\)/i', function ($matches) {
            $url = $matches[1];
            // Modify the URL if it's an asset URL
            if (strpos($url, 'vendor/') === 0) {
                return 'url(\'/unmeb/' . $url . '\')';
            }
            return $matches[0]; // Return the original URL if it doesn't need to be modified
        }, $content);

        // Rewrite URLs in HTML content
        $content = str_replace('src="/vendor/', 'src="/unmeb/vendor/', $content);
        $content = str_replace('href="/vendor/', 'href="/unmeb/vendor/', $content);

        return $content;
    }
}
