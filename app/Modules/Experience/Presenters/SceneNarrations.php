<?php

namespace App\Modules\Experience\Presenters;

use App\Models\Agency;
use App\Models\AgencyKnowledgeItem;
use App\Models\Consultant;
use App\Models\ServicePackage;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * What the AI Digital Strategist says when the guest steps into a scene. Every sentence
 * is built from stored agency data, never generated.
 */
final class SceneNarrations
{
    /**
     * @param  Collection<int, ServicePackage>  $items
     * @return array{catalog: ?string, item: array<string, string>}
     */
    public static function catalog(Agency $agency, Collection $items, string $locale): array
    {
        $templates = StageCopy::narration($locale);
        $terms = StageCopy::terms($locale);
        $money = self::money($agency);
        $sentence = self::sentence($locale);
        $separator = $locale === 'ja' ? '、' : ', ';

        if ($items->isEmpty()) {
            return ['catalog' => null, 'item' => []];
        }

        $intro = str_replace(
            [':count', ':price'],
            [(string) $items->count(), $money($items->min('price'))],
            $templates[$items->count() === 1 ? 'catalog_one' : 'catalog']
        );

        $narrations = $items->mapWithKeys(function (ServicePackage $item) use ($templates, $terms, $money, $sentence, $separator, $locale) {
            $parts = [$item->translatedName($locale).'.', filled($item->translatedDescription($locale)) ? $sentence(trim($item->translatedDescription($locale))) : null];

            if ($item->delivery) {
                $parts[] = str_replace(':delivery', $terms['delivery'][$item->delivery] ?? Str::headline($item->delivery), $templates['item_delivery']);
            }

            if (! empty($item->requirements)) {
                $list = collect($item->requirements)->map(fn ($requirement) => $terms['requirement'][$requirement] ?? $requirement)->implode($separator);
                $parts[] = str_replace(':list', $list, $templates['item_requirements']);
            }

            $parts[] = str_replace(':price', $money($item->price), $templates['item_price']);

            if ($item->is_sold_out) {
                $parts[] = $templates['item_sold_out'];
            }

            return [$item->slug => implode(' ', array_filter($parts))];
        })->all();

        return ['catalog' => $intro, 'item' => $narrations];
    }

    /**
     * @param  Collection<int, Consultant>  $areas
     * @return array{team: ?string, area: array<string, string>}
     */
    public static function team(Agency $agency, Collection $areas, string $locale): array
    {
        $templates = StageCopy::narration($locale);
        $money = self::money($agency);
        $sentence = self::sentence($locale);

        if ($areas->isEmpty()) {
            return ['team' => null, 'area' => []];
        }

        $first = $areas->first()->translatedName($locale);
        $intro = str_replace(
            [':count', ':first'],
            [(string) $areas->count(), $first],
            $templates[$areas->count() === 1 ? 'team_one' : 'team']
        );

        $narrations = $areas->mapWithKeys(function (Consultant $area) use ($templates, $money, $sentence, $locale) {
            $parts = [$area->translatedName($locale).'.', filled($area->translatedDescription($locale)) ? $sentence(trim($area->translatedDescription($locale))) : null];

            $parts[] = $area->min_companies > 1
                ? str_replace([':min', ':max'], [(string) $area->min_companies, (string) $area->max_companies], $templates['seat_companies'])
                : str_replace(':max', (string) $area->max_companies, $templates['seat_companies_one']);

            $parts[] = (float) $area->consultation_fee > 0
                ? str_replace(':fee', $money($area->consultation_fee), $templates['seat_fee'])
                : $templates['seat_free'];

            if ($area->minimum_budget !== null && (float) $area->minimum_budget > 0) {
                $parts[] = str_replace(':spend', $money($area->minimum_budget), $templates['seat_spend']);
            }

            return [$area->slug => implode(' ', array_filter($parts))];
        })->all();

        return ['team' => $intro, 'area' => $narrations];
    }

    /**
     * Intros for the facilities list and the agency information scene, built
     * only from stored agency data.
     *
     * @param  Collection<int, AgencyKnowledgeItem>  $facilities
     * @return array{facilities: ?string, info: string}
     */
    public static function scenes(Agency $agency, Collection $facilities, string $locale): array
    {
        $templates = StageCopy::narration($locale);
        $separator = $locale === 'ja' ? '、' : '; ';
        $time = fn (?string $value) => $value ? substr($value, 0, 5) : null;

        $intro = null;

        if ($facilities->isNotEmpty()) {
            $examples = $facilities->take(3)->map(fn ($item) => $item->translatedTitle($locale))->implode($separator);
            $intro = str_replace(
                [':count', ':examples'],
                [(string) $facilities->count(), $examples],
                $templates[$facilities->count() === 1 ? 'facilities_one' : 'facilities']
            );
        }

        $location = collect([$agency->city, $agency->country])->filter()->implode($locale === 'ja' ? '、' : ', ');
        $parts = [str_replace(':agency', $agency->name, $templates['info_welcome'])];

        if ($location !== '') {
            $parts[] = str_replace(':location', $location, $templates['info_place']);
        }

        if ($time($agency->opening_time) && $time($agency->closing_time)) {
            $parts[] = str_replace([':open', ':close'], [$time($agency->opening_time), $time($agency->closing_time)], $templates['info_hours']);
        }

        $parts[] = $templates['info_more'];

        return ['facilities' => $intro, 'info' => implode(' ', $parts)];
    }

    /**
     * @return \Closure(mixed): string
     */
    private static function money(Agency $agency): \Closure
    {
        return fn ($value) => $agency->currency.' '.number_format((float) $value, 0, ',', '.');
    }

    /**
     * @return \Closure(string): string
     */
    private static function sentence(string $locale): \Closure
    {
        return fn (string $text) => preg_match('/[.!?。！？]$/u', $text) ? $text : $text.($locale === 'ja' ? '。' : '.');
    }
}
