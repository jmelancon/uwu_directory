#!/bin/sh

export BASE_DN=$( \
        echo $REALM | \
        tr "[:upper:]" "[:lower:]" | \
        sed 's:\.:,DC=:g' | \
        sed 's/^/DC=/' \
    );

if [ ! -f /etc/samba/provisioned ]; then
  samba &
  for i in $(seq 64);
  do
      if lsof -i -P -n | grep -q ":636 (LISTEN)"; then
        break;
      elif [ "$i" -eq "64" ]; then
        echo "Samba didn't start. Bailing.";
        exit 1;
      else
        sleep 0.25;
      fi
  done
  cat /entrypoint.d/provision.ldif | sed "s/%DN_HERE%/$BASE_DN/" > /tmp/provision.ldif;
  ldapmodify -x -H ldaps:// -f /tmp/provision.ldif && \
  echo "uwu you're so provisioned swaggy :3" > /etc/samba/provisioned;
  pkill samba;
fi


