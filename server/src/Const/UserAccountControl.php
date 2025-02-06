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
    public const int SCRIPT = 1<<0;
    public const int ACCOUNTDISABLE = 1<<1;
    public const int HOMEDIR_REQUIRED = 1<<3;
    public const int LOCKOUT = 1<<4;
    public const int PASSWD_NOTREQD = 1<<5;
    public const int PASSWD_CANT_CHANGE = 1<<6;
    public const int ENCRYPTED_TEXT_PWD_ALLOWED = 1<<7;
    public const int TEMP_DUPLICATE_ACCOUNT = 1<<8;
    public const int NORMAL_ACCOUNT = 1<<9;
    public const int INTERDOMAIN_TRUST_ACCOUNT = 1<<11;
    public const int WORKSTATION_TRUST_ACCOUNT = 1<<12;
    public const int SERVER_TRUST_ACCOUNT = 1<<13;
    public const int DONT_EXPIRE_PASSWORD = 1<<16;
    public const int MNS_LOGON_ACCOUNT = 1<<17;
    public const int SMARTCARD_REQUIRED = 1<<18;
    public const int TRUSTED_FOR_DELEGATION = 1<<19;
    public const int NOT_DELEGATED = 1<<20;
    public const int USE_DES_KEY_ONLY = 1<<21;
    public const int DONT_REQ_PREAUTH = 1<<22;
    public const int PASSWORD_EXPIRED = 1<<23;
    public const int TRUSTED_TO_AUTH_FOR_DELEGATION = 1<<24;
    public const int PARTIAL_SECRETS_ACCOUNT = 1<<26;
}