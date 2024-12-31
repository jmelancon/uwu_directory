#!/bin/sh

export BASE_DN=$( \
        echo $REALM | \
        tr "[:upper:]" "[:lower:]" | \
        sed 's:\.:,DC=:g' | \
        sed 's/^/DC=/' \
    );

if [ ! -f /etc/samba/provisioned ]; then
  samba &
  sleep 2;
  cat /entrypoint.d/provision.ldif | sed "s/%DN_HERE%/$BASE_DN/" > /tmp/provision.ldif;
  ldapmodify -x -H ldaps:// -f /tmp/provision.ldif && \
  echo "uwu you're so provisioned swaggy :3" > /etc/samba/provisioned;
  pkill samba;
fi


