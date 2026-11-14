<?php

namespace App\Models;

/**
 * Admin proxy/alias model extending User for backward compatibility.
 * Operates on the unified 'users' table.
 */
class Admin extends User
{
    protected $table = 'users';
}
