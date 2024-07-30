<?php

namespace Portable\FilaCms\Models\Traits;

use Laravel\Scout\Searchable as ScoutSearchable;
use Portable\FilaCms\Models\Setting;

trait Searchable
{
    use ScoutSearchable {
        ScoutSearchable::search as scoutSearch;
    }

    /**
     * Override scout's implementation to remove any stop words
     * from even being passed into the query and then
     * perform a search against the model's indexed data.
     *
     * @param  string  $query
     * @param  \Closure  $callback
     * @return \Laravel\Scout\Builder
     */
    public static function search($query = '', $callback = null)
    {
        return static::scoutSearch(static::removeStopWords($query), $callback);
    }

    /**
     * Remove stop words from the given query.
     *
     * @param  string  $query
     * @return string
     */
    protected static function removeStopWords($query)
    {
        $stopWords = json_decode(Setting::get('settings.search.stop_words'));
        if (empty($stopWords) || !is_array($stopWords)) {
            return $query;
        }

        return trim(preg_replace('/\b('.implode('|', $stopWords).')\b/i', '', $query));
    }
}
