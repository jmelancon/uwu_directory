#!/bin/sh

export BASE_DN=$( \
        echo $REALM | \
        tr "[:lower:]" "[:upper:]" | \
        sed 's:\.:,DC=:g' | \
        sed 's/^/DC=/' \
    );

UUID_SRC="/proc/sys/kernel/random/uuid"
GROUP_GUID=$(cat $UUID_SRC)
SERVICE_GUID=$(cat $UUID_SRC)
BASIC_USERS_GUID=$(cat $UUID_SRC)
SSO_ADMIN_GUID=$(cat $UUID_SRC)

if [ ! -f /etc/samba/provisioned ]; then
  cat /entrypoint.d/provision.ldif | \
   sed "s/%DN_HERE%/$BASE_DN/" | \
   sed "s/%GROUP_GUID_HERE%/$GROUP_GUID/" | \
   sed "s/%SERVICE_GUID_HERE%/$SERVICE_GUID/" |\
   sed "s/%BASIC_USERS_GUID_HERE%/$BASIC_USERS_GUID/" | \
   sed "s/%SSO_ADMIN_GUID_HERE%/$SSO_ADMIN_GUID/" > /tmp/provision.ldif;
  ldbmodify -H "/var/lib/samba/private/sam.ldb.d/$BASE_DN.ldb" /tmp/provision.ldif && \
  echo "uwu you're so provisioned swaggy :3" > /etc/samba/provisioned;
  echo "Directory is hopefully provisioned.......";
  samba-tool user setexpiry Administrator --noexpiry
fi

exit 0;
