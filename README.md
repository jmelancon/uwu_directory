<table role="presentation" border="0" cellspacing="0" width="100%">
    <tr>
        <td>
            <img width=256 height=256 src="docs/assets/logo.svg" alt="uwu_directory logo. It's the flower emoji borrowed from Google's emoji set."/>
        </td>
        <td>
            <h1>uwu_directory</h1>
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

- Finish basic LDAP service integration
- Add configuration options
- Polish registration flow
- Add OAuth endpoints/configuration tabs
- Maybe add unit tests and Github actions?

## Installation

I don't really have any steps for installation at the moment. Best I can offer you
is this:

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
3. Enter the `compose` directory and start the Docker containers.
   ```shell
   cd ../compose
   docker compose up --build -d
   ```
4. ???
5. profit.

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
