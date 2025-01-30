<?php /** @noinspection PhpUnused */
declare(strict_types=1);

namespace App\Const;

/**
 * userAccountControl LDAP flags. Good for creating
 * users/services. If multiple flags are needed, use
 * a bitwise OR (<code>|</code>) to join the flags.
 *
 * @see https://learn.microsoft.com/en-us/troubleshoot/windows-server/active-directory/useraccountcontrol-manipulate-account-properties
 */
class UserAccountControl
{
    public const SCRIPT = 1<<0;
    public const ACCOUNTDISABLE = 1<<1;
    public const HOMEDIR_REQUIRED = 1<<3;
    public const LOCKOUT = 1<<4;
    public const PASSWD_NOTREQD = 1<<5;
    public const PASSWD_CANT_CHANGE = 1<<6;
    public const ENCRYPTED_TEXT_PWD_ALLOWED = 1<<7;
    public const TEMP_DUPLICATE_ACCOUNT = 1<<8;
    public const NORMAL_ACCOUNT = 1<<9;
    public const INTERDOMAIN_TRUST_ACCOUNT = 1<<11;
    public const WORKSTATION_TRUST_ACCOUNT = 1<<12;
    public const SERVER_TRUST_ACCOUNT = 1<<13;
    public const DONT_EXPIRE_PASSWORD = 1<<16;
    public const MNS_LOGON_ACCOUNT = 1<<17;
    public const SMARTCARD_REQUIRED = 1<<18;
    public const TRUSTED_FOR_DELEGATION = 1<<19;
    public const NOT_DELEGATED = 1<<20;
    public const USE_DES_KEY_ONLY = 1<<21;
    public const DONT_REQ_PREAUTH = 1<<22;
    public const PASSWORD_EXPIRED = 1<<23;
    public const TRUSTED_TO_AUTH_FOR_DELEGATION = 1<<24;
    public const PARTIAL_SECRETS_ACCOUNT = 1<<26;
}