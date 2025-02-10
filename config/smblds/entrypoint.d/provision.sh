#!/bin/sh

export BASE_DN=$( \
        echo $REALM | \
        tr "[:lower:]" "[:upper:]" | \
        sed 's:\.:,DC=:g' | \
        sed 's/^/DC=/' \
    );

if [ ! -f /etc/samba/provisioned ]; then
  cat /entrypoint.d/provision.ldif | sed "s/%DN_HERE%/$BASE_DN/" > /tmp/provision.ldif;
  ldbmodify -H "/var/lib/samba/private/sam.ldb.d/$BASE_DN.ldb" /tmp/provision.ldif && \
  echo "uwu you're so provisioned swaggy :3" > /etc/samba/provisioned;
  echo "Directory is hopefully provisioned.......";
fi

exit 0;
