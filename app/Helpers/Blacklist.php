<?php namespace App\Helpers;

class Blacklist {

    /**
     * Check if the name does not contain blacklisted words
     *
     * @param $name
     * @return bool
     */
    public static function checkName($name)
    {
        $blacklist_items = json_decode(\File::get(storage_path('app/blacklist.json')));

        foreach($blacklist_items as $item)
        {
            if (str_contains(strtolower($name), $item))
                return false;
        }

        return true;
    }

}