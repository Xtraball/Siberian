<?php
namespace Plesk;

abstract class ObjectStatus
{
    const ACTIVE = 0;
    const UNDER_BACKUP = 4;
    const DISABLED_BY_ADMIN = 16;
    const DISABLED_BY_RESELLER = 32;
    const DISABLED_BY_CUSTOMER = 64;
    const EXPIRED = 256;
}
