<?php

namespace App\Services;

class ContextService
{
    protected static $bookJournalID = null;

    public static function setBookJournalID($id)
    {
        static::$bookJournalID = $id;
    }

    public static function getBookJournalID()
    {
        // fallback to session jika ada
        return static::$bookJournalID ?? session('book_journal_id');
    }
}
