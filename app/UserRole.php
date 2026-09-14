<?php

namespace App;

enum UserRole: string
{
    case Admin = 'admin';
    case SuperAdmin = 'super_admin';
}
