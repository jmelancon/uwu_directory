<table role="presentation" border="0" cellspacing="0" width="100%">
    <tr>
        <td>
            <img width=128 height=128 src="docs/assets/logo.svg" alt="uwu_directory logo. It's the flower emoji borrowed from Google's emoji set."/>
        </td>
        <td>
            <h1>Uncomplicated Web User Directory</h1>
            <p><i>Joseph Melancon, 2025</i></p>
        </td>
    </tr>
</table>
<hr/>

## Abstract

This application is a simple Lightweight Directory Access Protocol (LDAP)
management interface build in PHP with the Symfony framework. Included alongside
the interface are support files to deploy a full directory suite using the
`smblds` Docker container.

## Why

I use Linux and don't particularly want to open a Windows VM any time
I want to make simple tweaks to users in my AD instance. Alongside that,
I only really need AD for central auth, so using a full-fat setup is very
overkill for my needs. Thus, my thought process is that I can ditch my OpenLDAP
server and instead use the one preconfigured in `smblds`. Down the line, I'm
hoping to also ditch Keycloak for OIDC and PWM for password resets.

## Project Status

This ain't done, chief.

### Todo

- ~~Finish basic LDAP service integration~~
  - This should be working! Services and groups can be created, and services can be bound to by LDAP
    clients. Next is to test with proper services, i.e. Mac/Windows desktops, server applications, etc.
- ~~Add configuration options~~
   - Done! In my testing, all options implemented persist and enforce rules throughout the application.
- ~~Polish registration flow~~
  - Pretty sure this is good, at least for now. Could use some stylistic tweaks as the padding on
    the field labels ticks me off.
- Add OAuth endpoints/configuration tabs
- Maybe add unit tests and ~~GitHub actions~~?
  - PHPUnit has been configured, but no tests have been written
  - GitHub actions is currently configured to spin up PHPUnit compose,
    build production image, and save the image as an artifact. Should be
    trivial to set this to push to docker.io in the future.

## Installation

For now, the container must be built from source as I haven't pushed off
to docker.io yet. To build and deploy, follow these steps:

1. Clone project to your local computer:
   ```shell
   git clone https://github.com/jmelancon/uwu_directory.git
   ```
2. Edit environment files under the `environment` directory
   ```shell
   cd uwu_directory/environment
   
   cp smblds/.env.example smblds/.env
   nano smblds/.env
   
   cp server/.env.example server/.env
   nano server/.env
   ```
3. Go back to project root and start the Docker containers.
   ```shell
   cd ../
   docker compose --profile prod up --build -d
   ```
4. Wait for containers to start.
5. Access web interface on `0.0.0.0:8018`

These steps are subject to change and may not work over the course
of this program's initial development.

## Dependencies / Thanks

The following frameworks, libraries, etc. are used across the application:

 - [Symfony](https://symfony.com): PHP Framework
 - [Bootstrap](https://getbootstrap.com): CSS Framework
 - [DataTables](https://datatables.net): AJAX Table JS Library
 - [smblds](https://github.com/smblds/smblds-container): LDAP Docker Container
 - [Roboto](https://github.com/googlefonts/roboto-2): Primary Typeface
 - [ParagonIE Paseto](https://paseto.io/): JWT Interface Library
 - [thephpleague/oauth2-server (future)](https://github.com/thephpleague/oauth2-server): OAuth2 Library
