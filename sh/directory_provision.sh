#!/bin/sh

if [ ! -f /etc/samba/provisioned ]; then
  samba-tool ou create OU=Services;
  samba-tool ou create OU=Groups;
  samba-tool group create "SSO Administrators" --groupou OU=Groups;
  samba-tool group create "Basic Users" --groupou OU=Groups;

  if samba-tool dbcheck; then
    echo "uwu you're so provisioned swaggy :3" > /etc/samba/provisioned;
    echo "directory's provisioned, bosshog"
  else
    echo "uh ohhhhhhhh, shit's broken :(";
    exit 1;
  fi

  # Set a forgiving expiry on accounts. I don't want to change my password!
  samba-tool user setexpiry Administrator --noexpiry;
  samba-tool domain passwordsettings set --max-pwd-age=0;
fi

exit 0;
