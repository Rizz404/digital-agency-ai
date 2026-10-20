<?php

namespace App\Http\Middleware;

use App\Models\Agency;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the published agency named by the {agencySlug} route segment and the
 * guest's language, so controllers never look either up themselves.
 *
 * The language is ?lang= on pages and "locale" in JSON bodies; an unsupported
 * value falls back to the agency's default language.
 */
class ResolveAgency
{
    private const CAFE = 'agency';

    private const LOCALE = 'locale';

    public function handle(Request $request, Closure $next): Response
    {
        $agency = Agency::query()
            ->published()
            ->where('slug', $request->route('agencySlug'))
            ->firstOrFail();

        $requested = $request->query('lang', $request->input('locale'));

        $locale = in_array($requested, config('agency.locales'), true) ? $requested : $agency->default_locale;

        $request->attributes->set(self::CAFE, $agency);
        $request->attributes->set(self::LOCALE, $locale);

        app()->setLocale($locale);

        return $next($request);
    }

    public static function agency(Request $request): Agency
    {
        return $request->attributes->get(self::CAFE);
    }

    public static function locale(Request $request): string
    {
        return $request->attributes->get(self::LOCALE);
    }
}
