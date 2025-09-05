<?php

namespace App\Enums;

enum AccountType: string
{
    case CounsellorFree = 'counsellor_free';
    case CounsellorPaid = 'counsellor_paid';
    case Researcher = 'researcher';
    case SuperAdmin = 'super_admin';
    case StudentRc = 'student_rc';
}