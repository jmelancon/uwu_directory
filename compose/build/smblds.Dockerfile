FROM smblds/smblds

# Get the DN string from existing ENV file
ENV REALM;
RUN export BASE_DN=$( \
        echo $REALM | \
        tr "[:upper:]" "[:lower:]" | \
        sed 's:\.:,DC=:g' | \
        sed 's/^/DC=/' \
    );

# Pull in and update LDIF to add groups directory and add default SSO admin group
COPY ../../config/smblds/entrypoint.d/provision.ldif /root/provision.ldif
RUN sed -i 's/%DN_HERE%/${BASE_DN}/' /root/provision.ldif

# Execute

